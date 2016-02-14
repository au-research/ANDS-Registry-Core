<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Class:  relationships handler
 * Getting data from the relations index instead of the database call
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Relationships extends ROHandler {

    /**
     * Primary handle function
     *
     * @return array
     */
    public function handle() {
        $result = array(
            'data' => $this->getRelatedFromIndex('data'),
            'publications' => $this->getRelatedFromIndex('publications'),
            'programs'=> $this->getRelatedFromIndex('programs'),
            'grants_projects' => $this->getRelatedFromIndex('grants_projects'),
            'services' => $this->getRelatedFromIndex('services'),
            'websites' => $this->getRelatedFromIndex('websites'),
            'researchers' => $this->getRelatedFromIndex('researchers'),
            'organisations' => $this->getRelatedFromIndex('organisations')
        );

        return $result;
    }

    /**
     * @param $type
     * @return array
     */
    private function getRelatedFromIndex($type)
    {
        $ci =& get_instance();
        $ci->load->library('solr');
        $ci->solr->setCore('relations');
        $ci->solr
            ->init()
            ->setOpt('rows', 5)
            ->setOpt('fq', '+from_id:'.$this->ro->id);
        switch ($type) {
            case "data":
                $ci->solr->setOpt('fq', '+to_class:collection');
                break;
            case "programs":
                $ci->solr->setOpt('fq', '+to_class:activity');
                $ci->solr->setOpt('fq', '+to_type:program');
                break;
            case "grants_projects":
                $ci->solr->setOpt('fq', '+to_class:activity');
                $ci->solr->setOpt('fq', '-to_type:program');
                break;
            case "services":
                $ci->solr->setOpt('fq', '+to_class:service');
                break;
            case "researchers":
                $ci->solr->setOpt('fq', '+to_class:party');
                $ci->solr->setOpt('fq', '+to_type:person');
                break;
            case "organisations":
                $ci->solr->setOpt('fq', '+to_class:party');
                $ci->solr->setOpt('fq', '-to_type:person');
                break;
            case "publications":
                $ci->solr->setOpt('fq', '+to_class:publication');
                break;
            case "websites":
                $ci->solr->setOpt('fq', '+to_class:website');
                break;
            default:
                // returns 0 for any other case
                $ci->solr->setOpt('fq', '+to_class:UNKNOWN');
                break;
        }

        $solrResult = $ci->solr->executeSearch(true);

        // default result
        $result = array(
            'count' => 0,
            'docs' => []
        );

        if ($solrResult && $solrResult['response']['numFound'] > 0) {
            $result['count'] = $solrResult['response']['numFound'];
            foreach ($solrResult['response']['docs'] as $doc) {
                $data = $doc;
                $result['docs'][] = $data;
            }
        }

        return $result;
    }

}
