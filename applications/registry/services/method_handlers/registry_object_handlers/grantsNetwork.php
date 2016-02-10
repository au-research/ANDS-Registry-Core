<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Class:  grantsNetwork
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class grantsNetwork extends ROHandler {

    /**
     * @return array
     */
    function handle() {

        $ci =& get_instance();
        $ci->load->library('solr');

        $result = array(
            'tree' => $this->ro->getGrantsStructureSOLR(),
            'data_output' => $this->getByRelation('data_output'),
            'programs' => $this->getByRelation('programs'),
            'grants' => $this->getByRelation('grants'),
            'publications' => $this->ro->getDirectPublication()
        );

        return $result;
    }

    /**
     * A wrapper allows consistent data return for each relation
     *
     * @param     $relation
     * @param int $limit
     * @return array|bool
     */
    private function getByRelation($relation, $limit = 5){
        $ci =& get_instance();
        $ci->load->library('solr');

        $ci->solr->init()
            ->setOpt('fl', 'id, key, title, class, type, slug')
            ->setOpt('rows', $limit);

        switch ($relation) {
            case "data_output":
                $ci->solr->setOpt('fq', '+class:collection');
                if ($this->ro->class == 'party') {
                    $ci->solr->setOpt('fq', '+relation_grants_isFundedBy:'.$this->ro->id);
                } else {
                    $ci->solr->setOpt('fq', '+relation_grants_isOutputOf:'.$this->ro->id);
                }
                break;
            case "programs":
                $ci->solr->setOpt('fq', '+class:activity');
                $ci->solr->setOpt('fq', '+type:program');
                if ($this->ro->class == 'party') {
                    $ci->solr->setOpt('fq', '+relation_grants_isFundedBy:'.$this->ro->id);
                } else {
                    $ci->solr->setOpt('fq', '+relation_grants_isPartOf:'.$this->ro->id);
                }
                break;
            case "grants":
                $ci->solr->setOpt('fq', '+class:activity');
                $ci->solr->setOpt('fq', '+type:grant');
                if ($this->ro->class == 'party') {
                    $ci->solr->setOpt('fq', '+relation_grants_isFundedBy:'.$this->ro->id);
                } else {
                    $ci->solr->setOpt('fq', '+relation_grants_isPartOf:'.$this->ro->id);
                }
                break;
        }

        $solrResult = $ci->solr->executeSearch(true);

        if ($solrResult) {

            $relationships = array_values($solrResult['response']['docs']);
            foreach ($relationships as &$relation) {
                if ($this->ro->class == 'party') {
                    $relationType = 'funds';
                } elseif ($this->ro->class == 'activity') {
                    if ($relation['class'] == 'collection') {
                        $relationType = 'hasOutput';
                    } else {
                        $relationType = 'hasPart';
                    }
                } else {
                    $relationType = 'hasPart';
                }

                $additionalContent = [
                    'registry_object_id' => $relation['id'],
                    'relation_type' => $relationType,
                    'origin' => 'EXPLICIT',
                    'relation_description' => ''
                ];

                $relation = array_merge($relation, $additionalContent);
            }

            return array(
                'count' => $solrResult['response']['numFound'],
                'more' => $solrResult['response']['numFound'] > $limit ? true : false,
                'result' => $relationships
            );
        } else {
            return false;
        }

    }
}