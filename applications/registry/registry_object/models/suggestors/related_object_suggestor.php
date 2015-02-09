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
        'service' => array(
            'supports',
            'isAvailableThrough',
            'isProducedBy',
            'isPresentedBy',
            'isOperatedOnBy',
            'hasValueAddedBy',
        )
    );

    public function suggest()
    {
        $ci =& get_instance();
        $processed_related_objects = array();
        $score_override = sizeof($this->relationship_types);
        $suggestions = array();
        $ci->load->library('solr');

        foreach($this->relationship_types as $class=>$typeArray)
        {
            $connections = $this->ro->getRelatedObjectsByClassAndRelationshipType(
                array($class),$typeArray);
            // key: relationship type (isPrincipalInvestigatorOf, etc.)
            // value: array of registry_object_ids
            $related_objects_by_relationship_types = array();
            foreach ($connections as $connection) {
                $this_relationship_type = $connection['relation_type'];
                $this_registry_object_id = $connection['registry_object_id'];

                if (!in_array(
                    $this_registry_object_id,
                    $processed_related_objects
                )) {
                    $processed_related_objects[] = $this_registry_object_id;
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
                        $str = $str .
                            'related_party_one_id:' .
                            $related_object_id . ' ' .
                            'related_party_multi_id:' .
                            $related_object_id . ' '
                        ;
                    }
                    // call Solr library
                    $result = $this->runSolrQuery($ci, $str);
                    $this->processSolrResult(
                        $result,
                        $suggestions,
                        $score_override
                    );

                }

            }
            $score_override--;
        }
        return $suggestions;
    }


    private function runSolrQuery($ci, $query)
    {
        $ci->solr
            ->init()
            ->setOpt('q', $query)
            ->setOpt('rows', '10')
            ->setOpt('fl', 'id,key,slug,title,score')
            ->setOpt('fq', '-id:'.$this->ro->id)
            ->setOpt('fq', 'class:collection')
            ->setOpt('defType', 'edismax');
        
        $result = $ci->solr->executeSearch(true);
                
        return $result;
    }

    private function processSolrResult($result, &$suggestions, $score_override)
    {
        if ($result['response']['numFound'] > 0) {
            foreach ($result['response']['docs'] as $doc) {
                if (!in_array_r($doc, $suggestions)) {
                    $doc['score'] = $score_override;
                    $suggestions[] = $doc;
                }
            }
        }
    }

    public function __construct()
    {
        parent::__construct();
        set_exception_handler('json_exception_handler');
    }
}
