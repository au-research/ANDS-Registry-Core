<?php


namespace ANDS\Registry\Providers\RIFCS;


use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use MinhD\SolrClient\SolrClient;
use MinhD\SolrClient\SolrSearchResult;
use ANDS\Util\XMLUtil;


/**
 * Class RelationshipProvider
 * @package ANDS\Registry\Providers
 */
class SubjectProvider implements RIFCSProvider
{


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

        $solrClient = new SolrClient('test.ands.org.au', '8983');
        $solrClient->setCore('concepts');
        $subjectsResolved = array();

        foreach ($subjects AS $subject) {
            //$uri='';
            $type = $subject["type"];
            $value = (string)($subject['value']);
            $uri = $subject['uri'];
            $positive_hit = false;
            $search_extra = '';
            $score = 0;

            if (!array_key_exists((string)$value, $subjectsResolved)) {
                $search_value = self::formatSearchString($value);
                //  $label_search_value = self::suspectAnzsrc($value);
                //  if(!is_numeric($search_value)) $search_extra = ' + label_s:('.$search_value.') ^5';
                  $solrResult = $solrClient->search([
                      'q' => $search_value,
                      'fl' => '* , score',
                      'sort' => 'score desc',
                  ]);


                if ($solrResult->getNumFound() > 0) {
                    $result = $solrResult->getDocs();
                    $top_response = $result[0];
                    $values = $top_response->toArray();
                    $new_value =  array_key_exists('notation_s', $values) ? $top_response->notation_s : $value;
                    $positive_hit = self::checkResult($top_response, $subject, $new_value);
                }

                if ($positive_hit && !array_key_exists($new_value, $subjectsResolved)) {
                    $uri = '';
                    $uri = $top_response->iri[0];
                    $resolved_type = $top_response->type[0];
                    $resolved_value = $top_response->label[0];
                    $score = $top_response->score;

                    $subjectsResolved[$new_value] = array('type' => $resolved_type, 'value' => $new_value, 'resolved' => $resolved_value, 'uri' => $uri);
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
                } else if (!$positive_hit) {
                    $subjectsResolved[$value] = array('type' => $type, 'value' => $value, 'resolved' => $value, 'uri' => $subject['uri']);
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
    public static function formatSearchString($string)
    {

        $search_string = $string;
        $label_string = $string;

        //if (is_numeric($search_string)) return $search_string;

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

        $multi_value = explode("|", $string);
        if (count($multi_value) > 1) {
            return 'label_s:("' . strtoupper(trim(array_pop($multi_value))) . '") ^5 + search_labels_string_s:' . $search_string . ' OR "' . $search_string . '"';
        }

        $multi_value = explode("&gt;",$string);
        if (count($multi_value) > 1) {
            return 'label_s:("' . strtoupper(trim(array_pop($multi_value)))  . '") ^5 + search_labels_string_s:' . $search_string . ' OR "' . $search_string . '"';
        }


        // quote the search string so solr reserved characters don't break the solr query
        return 'label_s:("' . $label_string . '") ^5 + notation_s:"' . $search_string . '" ^5 + "'.$search_string.'"' ;
    }

    /**
     * determine if the found solr result is a correct hit
     *
     * @param $resolved $subject
     * @return boolean
     */
    public static function checkResult($resolved, $subject,$notation_value )
    {

        $value = $subject['value'];

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

        // if this is a concatenated gcmd value strip off the final subject value to check resolved value
        if ($resolved->type[0] == 'gcmd') {
            $multi_value_1 = explode("|", $subject['value']);
            if (count($multi_value_1 > 1)) $value = trim($multi_value_1[count($multi_value_1) - 1]);

            $multi_value_2 = explode("&gt;", $subject['value']);
            if (count($multi_value_2 > 1)) $value = trim($multi_value_2[count($multi_value_2) - 1]);
        }

        // if we have a direct value string check the resolved value
        if (strtoupper($value) == strtoupper($resolved->label[0])) {
            return true;
        }

        // determine threshold of appropriate match for all other cases
        if ($resolved->score < 0.3) {
            return false;
        }

        return true;
    }
}
?>