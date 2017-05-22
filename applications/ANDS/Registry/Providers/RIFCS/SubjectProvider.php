<?php


namespace ANDS\Registry\Providers\RIFCS;


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
    public static $RESOLVABLE = ['anzsrc', 'anzsrc-for', 'anzsrc-seo',
        'gcmd', 'iso639-3','local'];

    public static function process(RegistryObject $record)
    {
        return;
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
     * @param $record
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
            $subjects[] = array(
                'type' => (string)$subject["type"],
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

        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore('concepts');
        $subjectsResolved = array();

        foreach ($subjects AS $subject) {

            $type = $subject["type"];
            $value = (string)($subject['value']);
            $positive_hit = false;
            $uri= $subject['uri']? $subject['uri'] : " ";
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
                    if((strtolower($type) =='gcmd' || strtolower($type) == 'anzsrc-for' || strtolower($type) == 'anzsrc-seo' || strtolower($type) == 'anzsrc')) $type = 'local';
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
    public static function formatSearchString($string,$type)
    {

        $search_string = $string;
        $label_string = $string;

        // determine if string has a preceding numeric notation before the prefLabel then don't quote the search string
        $notation = explode(" ", $string);
        if (is_numeric($notation[0])) {
            return $string;
        }

        // determine if the string has &gt; divider and convert to | or strip special characters in the subject query string
        $search_string = str_replace("&gt;", "|", $search_string);
        $search_string = str_replace("&", "", $search_string);
        $search_string = str_replace("(", "", $search_string);
        $search_string = str_replace(")", "", $search_string);
        $search_string = str_replace(":", "", $search_string);
        $search_string = str_replace(";", "", $search_string);


        //determine the actual final term of a gcmd value
        if(self::isMultiValue($string) && ($type=='gcmd'||$type=='local'))
            return 'search_label_s:("' . strtolower(self::getNarrowestConcept($string)) . '") ^5 + search_labels_string_s:' . $search_string . ' OR "' . $search_string . '"';

        //if the provided type is anzsrc-for or anzsrc-seo then specifiy the type in the query so that duplicate values from the wrong type are not returned
        if($type == "anzsrc-for" || $type == "anzsrc-seo")
            return 'type:'.$type.' AND (search_label_s:("' . strtolower($label_string) . '") ^5 + notation_s:"' . $search_string . '" ^5 + "'.$search_string.'")';

        // quote the search string so solr reserved characters don't break the solr query
            return 'search_label_s:("' . strtolower($label_string) . '") ^5 + notation_s:"' . $search_string . '" ^5 + "'.$search_string.'"' ;
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
        if ($resolved->type[0] == 'gcmd' && self::isMultiValue($subject['value']))
            $value = self::getNarrowestConcept($subject['value']);

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

        $multi_value = explode("|", $string);
        if (count($multi_value) > 1)  return true;

        $multi_value = explode("&gt;",$string);
        if (count($multi_value) > 1) return true;

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

        $multi_value = explode("|", $string);
        if (count($multi_value) > 1)  return trim(array_pop($multi_value));

        $multi_value = explode("&gt;",$string);
        if (count($multi_value) > 1) return trim(array_pop($multi_value));

        return $string;

    }
}
?>