<?php


namespace ANDS\Registry\Providers\RIFCS;


use ANDS\Cache\Cache;
use ANDS\Log\Log;
use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\Util\Config;
use MinhD\SolrClient\SolrClient;
use MinhD\SolrClient\SolrSearchResult;
use ANDS\Util\XMLUtil;


/**
 * Class RelationshipProvider
 * @package ANDS\Registry\Providers
 */
class SubjectProvider implements RIFCSProvider
{
    public static $RESOLVABLE = ['anzsrc', 'anzsrc-for', 'anzsrc-seo','anzsrc-for-2020', 'anzsrc-seo-2020',
        'gcmd', 'iso639-3','local'];
    public static $delimiter = ['|', '&gt;', '>'];

    public static function process(RegistryObject $record)
    {
        return Cache::file()->remember("subjects.{$record->id}", 1440, function () use ($record) {
            return static::processSubjects($record);
        });
    }

    /**
     * get subjects
     *
     * @param $record
     * @return array
     */
    public static function get(RegistryObject $record)
    {
        $data = MetadataProvider::get($record);
        return [
            'subjects' => self::getSubjects($record, $data)
        ];
    }

    /**
     * get subjects
     *
     * @param RegistryObject $record
     * @param null $data
     * @return array
     */
    public static function getSubjects(
        RegistryObject $record,
        $data = null
    )
    {
        if (!$data) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
        }

        $subjects = [];
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:subject') AS $subject) {

            // Release 42 enabled access to the anzsrc 2020 vocabs (for and seo) - code values are provided under the
            // anzsrc-xxx vocab types - check of the code will determine which vocab we resolve the value to
            $notations = explode(" ",$subject);
            $subject_notation = substr((string)$notations[0],0,2);

            if( (string)$subject["type"]=='anzsrc-for' && is_numeric( $subject_notation) && $subject_notation >'29' ){
                $type='anzsrc-for-2020';
            } elseif ((string)$subject["type"]=='anzsrc-seo' && is_numeric( $subject_notation) && $subject_notation < '80'){
                $type='anzsrc-seo-2020';
            } else{
                $type=(string)$subject["type"];
            }

            $subjects[] = array(
                'type' => $type,
                'value' => (string)$subject,
                'uri' => (string)$subject["termIdentifier"]);
        };
        return $subjects;
    }

    /**
     * process subjects
     *
     * @param $record
     * @return array
     */
    public static function processSubjects(RegistryObject $record)
    {
        $subjects = self::getSubjects($record);
        return self::resolveSubjects($subjects);
    }

    /**
     * Resolve the given subjects
     * Hit the SOLR concepts core and determine the list of resolved subjects
     * based on the subjects provided
     * TODO clean up logic and build resolveSubject($value, $type) for even finer unit
     * TODO Cache result at resolveSubject end
     *
     * @param $subjects
     * @return array
     */
    public static function resolveSubjects($subjects)
    {
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore('concepts');

        $subjectsResolved = array();

        foreach ($subjects AS $subject) {

            $type = $subject["type"];
            $value = (string) ($subject['value']);
            $positive_hit = false;
            $uri = array_key_exists('uri', $subject) ? $subject['uri'] : " ";
            if (!array_key_exists((string)$value, $subjectsResolved)) {
                $search_value = self::formatSearchString($value,strtolower($type));
                $solrResult = $solrClient->search([
                    'q' => $search_value,
                    'fl' => '* , score',
                    'sort' => 'score desc',
                    'facet' => 'on',
                    'facet.field' => 'search_label_s'
                ]);
                $subjectFacet = $solrResult->getFacetField('search_label_s');
                $numFound = isset($subjectFacet[strtolower($value)]) ?  $subjectFacet[strtolower($value)] : 0;
                if ($solrResult->getNumFound() > 0) {
                    $result = $solrResult->getDocs();
                    $top_response = $result[0];

                    // RDA-740. Mitigate the unintended side effects of incomplete or faulty concepts
                    if (!isset($top_response->label[0]) || !isset($top_response->iri[0]) || !isset($top_response->type[0])) {
                        Log::warning(__METHOD__. " Failed resolving concept. Incomplete concept", [
                            'search_value' => $search_value,
                            'subject_type' => $type,
                            'subject_value' => $value
                        ]);
                        continue;
                    }

                    $resolved_value = $top_response->label[0];
                    $resolved_uri = $top_response->iri[0];
                    $resolved_type = $top_response->type[0];
                    $values = $top_response->toArray();
                    $new_value =  array_key_exists('notation_s', $values) ? $top_response->notation_s : $value;
                    $returned_notation = array_key_exists('notation_s', $values) ? $top_response->notation_s : "";
                    $positive_hit = self::checkResult($top_response, $subject, $returned_notation, $numFound);
                    if(self::isMultiValue($new_value) && (strtolower($type)=='gcmd'||$type=='local')) $new_value = self::getNarrowestConcept($new_value);
                }

                if (in_array(strtolower($type), self::$RESOLVABLE) && $positive_hit && !array_key_exists($new_value, $subjectsResolved)) {
                    $score = $top_response->score;
                    $subjectsResolved[$new_value] = array('type' => $resolved_type, 'value' => $new_value, 'resolved' => $resolved_value, 'uri' => $resolved_uri);
                    if (array_key_exists('broader_labels_ss', $values)) {
                        array_key_exists('broader_notations_ss', $values) ? $index = $top_response->broader_notations_ss : $index = $top_response->broader_labels_ss;
                        for ($i = 0; $i < count($top_response->broader_labels_ss); $i++) {
                            $subjectsResolved[$index[$i]] = array(
                                'type' => $resolved_type,
                                'value' => $index[$i],
                                'resolved' => $top_response->broader_labels_ss[$i],
                                'uri' => $top_response->broader_iris_ss[$i]
                            );
                        }
                    }
                }else if (!$positive_hit && $numFound > 1 && strtolower($type) =='gcmd') {
                    $subjectsResolved[$value] = array('type' => $type, 'value' => $value, 'resolved' => $value, 'uri' => $uri );
                }else if (!$positive_hit || !in_array(strtolower($type), self::$RESOLVABLE)) {
                    //no match or a very loose match was found so it is not a gcmd vocab
                    if((strtolower($type) =='gcmd' || strtolower($type) == 'anzsrc-for' || strtolower($type) == 'anzsrc-seo' ||
                        strtolower($type) == 'anzsrc-for-2020' || strtolower($type) == 'anzsrc-seo-2020' || strtolower($type) == 'anzsrc')) $type = 'local';
                    $subjectsResolved[$value] = array('type' => $type, 'value' => $value, 'resolved' => $value, 'uri' => $uri );
                }
            }
        }
        return $subjectsResolved;
    }

    /**
     * format the solr search string
     *
     * @param $string
     * @return string
     */
    public static function formatSearchString($string, $type)
    {

        $search_string = $string;
        $label_string = $string;

        // escape special characters
        $match = ['\\', '&', '!', '(', ')', '{', '}', '[', ']', '^', '~', '*', '?', ':', '/', '||'];
        $replace = ['\\\\', '&', '\\!', '\\(', '\\)', '\\{', '\\}', '\\[', '\\]', '\\^', '\\~', '\\*', '\\?', '\\:', '\\/', '\\||'];

        // determine if string has a preceding numeric notation before the prefLabel . If so, then don't quote the search string
        // search string needs to be escaped to ensure that special characters don't break SOLR
        $notation = explode(" ", $string);
        if (is_numeric($notation[0]) && !in_array(strtolower($type), self::$RESOLVABLE)) {
           return str_replace($match, $replace, $string);
        }
        if (is_numeric($notation[0]) && in_array(strtolower($type), self::$RESOLVABLE)) {
            $search_string = $notation[0];
        }
        // determine if the string has &gt; divider and convert to |
        $search_string = str_replace("&gt;", "|", $search_string);

        $search_string = str_replace($match, $replace, $search_string);
        $label_string = str_replace($match, $replace, $label_string);

        //determine the actual final term of a gcmd value
        if(self::isMultiValue($string) && ($type=='gcmd'||$type=='local'))
            return 'search_label_s:("' . mb_strtolower(self::getNarrowestConcept($string)) . '") ^5 + search_labels_string_s:' . $search_string . ' OR "' . $search_string . '"';

        //if the provided type is anzsrc-for or anzsrc-seo then specifiy the type in the query so that duplicate values from the wrong type are not returned
        if($type == "anzsrc-for" || $type == "anzsrc-seo" || $type == "anzsrc-for-2020" || $type == "anzsrc-seo-2020")
            return 'type:' . $type . ' AND (search_label_s:("' . strtolower($label_string) . '") ^5 + notation_s:"' . $search_string . '" ^5 + "' . $search_string . '")';

        // quote the search string so solr reserved characters don't break the solr query
        return 'search_label_s:("' . mb_strtolower($label_string) . '")^5 + notation_s:"' . $search_string . '"^5 + "'.$search_string.'"' ;
    }

    /**
     * determine if the found solr result is a correct hit
     *
     * @param $resolved $subject
     * @return boolean
     */

    public static function checkResult($resolved, $subject,$notation_value, $numFound)
    {
        $value = $subject['value'];
        $type = strtolower($subject['type']);

        //if we have a local type and the provided subject value is a numeric, then do not provide a match

        if(strtolower($subject['type'])=='local' && is_numeric($subject['value'])) return false;

        //if we have resolved a local string type to a language code then do not provide a match

        if(strtolower($subject['type'])=='local' && strtolower($resolved->type[0])=='iso639-3')  return false;

        // if this could be a numeric notation preceding a resolved value - strip off the notation and check the resolved value
        // if the resolved type is the same as the supplied type disregard any discrepancy in the supplied numeric notation
        $notation = explode(" ", $value);
        if (is_numeric($notation[0]) && count($notation) > 1) {
            $notation_match =  $notation[0] == $notation_value ? true : false;
            $type_match = strtoupper($subject["type"]) == strtoupper($resolved->type[0]) ? true : false;
            unset($notation[0]);
            $value = implode(" ", $notation);
            $value_match = strtoupper(trim($value)) == strtoupper($resolved->label[0]) ? true : false;
            return ($type_match OR $notation_match) AND $value_match ? true : false;
        }

        // if we have a direct value string check the resolved value and number of exact hits and defined type - if multiple hits or local type is local then don't match
        if (strtoupper($value) == strtoupper($resolved->label[0]) && ($numFound > 1 || $type == 'local'))  return false;

        // if this is a concatenated gcmd value strip off the final subject value to check resolved value
        if ($resolved->type[0] == 'gcmd' && self::isMultiValue($subject['value'])) {
            $value = self::getNarrowestConcept($subject['value']);
        }


        // if we have a direct value string check the resolved value
        if (strtoupper($value) == strtoupper($resolved->label[0]))  return true;

        //if we have a direct hit on the notation of a vocab
        if (strtoupper($value) == strtoupper($notation_value))  return true;

        return false;
    }

    /**
     * determine if the provided value is a concatenated concepts string of concepts
     *
     * @param $string
     * @return boolean
     */
    public static function isMultiValue($string)
    {
        foreach (self::$delimiter as $delim) {
            $multi_value = explode($delim, $string);
            if (count($multi_value) > 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * determine narrowest concept of a concatenated concepts string of concepts
     *
     * @param $string
     * @return string
     */
    public static function getNarrowestConcept($string)
    {
        foreach (self::$delimiter as $delim) {
            $multi_value = explode($delim, $string);
            if (count($multi_value) > 1) {
                return trim(array_pop($multi_value));
            }
        }

        return $string;
    }

    /**
     * Obtain an associative array for the indexable fields
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getIndexableArray(RegistryObject $record)
    {
        $subjects = static::processSubjects($record);

        $unresolved = [];
        $resolved = [];
        $types = [];
        $uris = [];

        foreach ($subjects as $key => $subject) {
            $unresolved[] = (string) $key;
            $resolved[] =  html_entity_decode((string) $subject['resolved'], ENT_QUOTES);
            $types[] = (string) $subject['type'];
            $uris[] = (string) $subject['uri'];
        }

        // adding tsubject_$type for portal/registry_object/vocab usage
        $typeValuePairs = [];
        foreach ($subjects as $key => $subject) {
            $type = (string) $subject['type'];
            $value = (string) $subject['resolved'];
            $typeValuePairs["tsubject_$type"][] = $key;

            if ($type === "anzsrc-for") {
                $typeValuePairs["subject_anzsrcfor"][] = $value;
            }else if ($type === "anzsrc-seo") {
                $typeValuePairs["subject_anzsrcseo"][] = $value;
            } else if ($type === "gcmd") {
                $typeValuePairs["subject_gcmd"][] = $value;
            } else if ($type === "iso639-3") {
                $typeValuePairs["subject_iso639-3"][] = $value;
            }
        }

        return array_merge([
            'subject_value_unresolved' => $unresolved,
            'subject_value_resolved' => $resolved,
            'subject_type' => $types,
            'subject_vocab_uri' => $uris,
        ], $typeValuePairs);
    }
}