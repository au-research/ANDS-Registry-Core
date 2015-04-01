<?php
require_once(APP_PATH. 'registry_object/models/_GenericSuggestor.php');

/**
 * Class Related object Suggestor
 * @author Leo Monus <Leo.Monus@ands.org.au>
 * @author Richard Walker <Richard.Walker@ands.org.au>
 */
class Related_object_suggestor extends _GenericSuggestor
{

    /**
     * Suggest Records based on related objects.
     * @return array suggested_records
     */
    private $relationship_types = array(
        'collection' =>  array(
            'describes',
            'isLocationFor',
            'isDescribedBy',
            'isLocatedIn'
        ),
        'party' =>  array(
            'isPrincipalInvestigatorOf',
            'hasPrincipalInvestigator',
            'principalInvestigator',
            'author',
            'coInvestigator',
            'isOwnedBy',
            'hasCollector',
            'isManagedBy',
            'enriches',
            'hasAssociationWith',
        ),
        'activity' => array(
            'isPrincipalInvestigatorOf',
            'isPartOf',
            'isOutputOf',
            'hasAssociationWith',
            'isManagerOf',
            'isManagedBy',
            'isOwnedBy',
            'hasAssociatonWith',
            'isOwnerOf',
        ),
        'service' => array()// any relationship type
    );

    public function suggest()
    {
        $ci =& get_instance();
        $score_override = 1;

        $suggestions = array();
        $ci->load->library('solr');
        $maxRows = 50;
        foreach($this->relationship_types as $class=>$typeArray)
        {
            $nextOverRide = 0;
            $connections = $this->ro->getRelatedObjectsByClassAndRelationshipType(
            array($class),$typeArray);
            if($class == 'collection') // get them from DB only
            {

                foreach ($connections as $connection) {

                    $ci->db->select('registry_object_id, key, slug, title')
                        ->from('registry_objects')
                        ->where('registry_object_id',$connection['registry_object_id']);

                    $query = $ci->db->get();
                    foreach($query->result_array() AS $row)
                    {
                        $suggestions[] = array('id'=>$row['registry_object_id'],
                            'key'=>$row['key'],
                            'slug'=>$row['slug'],
                            'title'=>$row['title'],
                            'class'=>$class,
                            'RDAUrl' => portal_url($row['slug'].'/'.$row['registry_object_id']),
                            'relation_type'=>$connection['relation_type'],
                            'score'=>1
                        );
                        $nextOverRide = 0.3;
                    }
                }
            }
            else{

                // key: relationship type (isPrincipalInvestigatorOf, etc.)
                // value: array of registry_object_ids
                $related_objects_by_relationship_types = array();
                foreach ($connections as $connection) {
                    $this_relationship_type = $connection['relation_type'];
                    $this_registry_object_id = $connection['registry_object_id'];

                   if (!isset(
                    $related_objects_by_relationship_types[
                    $this_relationship_type])) {
                        $related_objects_by_relationship_types[
                        $this_relationship_type] = array(
                            $this_registry_object_id);
                    } else {
                        $related_objects_by_relationship_types[
                        $this_relationship_type][] = $this_registry_object_id;
                    }
                }

                // Assign a score based on relationship types.
                // Prioritize according to the ordering of the $relationship_types
                // array (as per spec).

                foreach ($typeArray as $relationship_type) {

                    if (isset(
                    $related_objects_by_relationship_types[
                    $relationship_type])) {

                        $str = '';

                        foreach ($related_objects_by_relationship_types[
                                 $relationship_type] as $related_object_id) {

                            //construct the query string
                            //party hack!!!
                            if($class == 'party'){
                                $str = $str .
                                    'related_party_one_id:' .
                                    $related_object_id . ' ' .
                                    'related_party_multi_id:' .
                                    $related_object_id . ' '
                                ;
                            }
                            else{
                                $str = $str .
                                    'related_'.$class.'_id:' .
                                    $related_object_id . ' ';
                            }
                        }
                        // call Solr library
                        $result = $this->runSolrQuery($ci, $str, $maxRows);
                        if($result['response']['numFound'] > 0){
                            $this->processSolrResult(
                                $result,
                                $suggestions,
                                $score_override,
                                $class,
                                $relationship_type,
                                $maxRows
                            );
                            $nextOverRide = 0.3;
                        }


                    }

                }
            }
            $score_override = $score_override - $nextOverRide;
        }
        return $suggestions;
    }


    private function runSolrQuery($ci, $query, $maxRows)
    {
        $ci->solr
            ->init()
            ->setOpt('q', $query)
            ->setOpt('rows', $maxRows)
            ->setOpt('fl', 'id,key,slug,title,score')
            ->setOpt('fq', '-id:'.$this->ro->id)
            ->setOpt('fq', 'class:collection')
            ->setOpt('defType', 'edismax');
        
        $result = $ci->solr->executeSearch(true);
                
        return $result;
    }

    private function processSolrResult($result, &$suggestions, $score_override, $class, $relation_type, $maxRows)
    {
        $intScore = 0;
        $maxScore = floatval($result['response']['maxScore']);
        foreach ($result['response']['docs'] as $doc) {
            if (!in_array_r($doc, $suggestions)) {
                $doc['score'] = $doc['score'] / $maxScore * (1-($intScore/$maxRows) * $score_override);
                $intScore++;
                $doc['relation_type'] = $relation_type;
                $doc['class'] = $class;
                $doc['RDAUrl'] = portal_url($doc['slug'].'/'.$doc['id']);
                $suggestions[] = $doc;
            }
        }
    }

    public function __construct()
    {
        parent::__construct();
        set_exception_handler('json_exception_handler');
    }
}
