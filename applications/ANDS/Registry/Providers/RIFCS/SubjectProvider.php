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
    ) {
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
    function processSubjects(RegistryObject $record)
    {
        $subjects = self::getSubjects($record);

        $solrClient = new SolrClient('devl.ands.org.au', '8983');
        $solrClient->setCore('concepts');
        $subjectsResolved = array();

        foreach ($subjects AS $subject)
        {
            $type = $subject["type"];
            $value = (string)($subject['value']);
            $uri = $subject['uri'];
            $positive_hit = false;
            $score = 0;

            if(!array_key_exists($value, $subjectsResolved))
            {
                $solrResult = $solrClient->search([
                    'q' => '"'.$value.'" ^10',
                    'fl' => '* , score',
                    'sort' => 'score desc',
                ]);

                if ($solrResult->getNumFound() > 0){
                    $result = $solrResult->getDocs();
                    $top_response = $result[0];
                    $resolved_type= $top_response->type[0];
                    $resolved_value =  $top_response->label[0];
                    $uri = $top_response->iri[0];
                    $score = $top_response->score;

                    isset($top_response->notation_s)? $new_value = $top_response->notation_s : $new_value = $value;
                    $positive_hit = self::checkResult($top_response,$subject);
                }

                if($positive_hit) {
                    $subjectsResolved[$value] = array('type' => $resolved_type, 'value' => $new_value, 'resolved' => $resolved_value, 'uri' => $uri, 'score' => $score);
                    if($top_response->broader_labels_ss) {
                        isset($top_response->broader_notations_ss[0]) ? $index = $top_response->broader_notations_ss : $index = $top_response->broader_labels_ss;
                        for($i=0;$i<count($top_response->broader_labels_ss);$i++) {
                             $subjectsResolved[$index[$i]] = array(
                                'type'=>$resolved_type,
                                'value'=>$index[$i],
                                'resolved'=>$top_response->broader_labels_ss[$i],
                                'uri'=>$top_response->broader_iris_ss[$i]
                            );
                        }
                    }
                }else{
                    $subjectsResolved[$value] = array('type' => $type, 'value' => $value, 'resolved' => $value, 'uri' => $uri);
                }

            }
        }
        return $subjectsResolved;
    }

    function checkResult($resolved, $subject){
        if((string)($subject['value']==$resolved->label[0])) return true;
        if($resolved->score<0.8) return false;
        return true;
    }

}