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

        // get my duplicates and search for their relationships too
        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($this->ro->id);
        $duplicates = $record->getDuplicateRecords()->pluck('registry_object_id')->toArray();
        $ids = [ $this->ro->id ];
        if (count($duplicates) > 0) {
            $ids = array_merge($ids, $duplicates);
        }
        $idQuery = implode(' OR ', $ids);

        $result = array(
            'data' => $this->getRelatedFromIndex('data', $idQuery),
            'publications' => $this->getRelatedFromIndex('publications', $idQuery),
            'programs'=> $this->getRelatedFromIndex('programs', $idQuery),
            'grants_projects' => $this->getRelatedFromIndex('grants_projects', $idQuery),
            'services' => $this->getRelatedFromIndex('services', $idQuery),
            'websites' => $this->getRelatedFromIndex('websites', $idQuery),
            'researchers' => $this->getRelatedFromIndex('researchers', $idQuery),
            'organisations' => $this->getRelatedFromIndex('organisations', $idQuery)
        );

        return $result;
    }

    /**
     * @param $type
     * @param null $idQuery
     * @return array
     */
    private function getRelatedFromIndex($type, $idQuery = null)
    {
        $ci =& get_instance();
        $ci->load->library('solr');

       if ($idQuery === null) {
           $idQuery = $this->ro->id;
       }
       $ci->solr
            ->init()
            ->setCore('relations')
            ->setOpt('rows', 5)
            ->setOpt('fq', "+from_id:({$idQuery})");
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
                $ci->solr->setOpt('fq', '+to_class:party OR relation_origin:IDENTIFIER');
                $ci->solr->setOpt('fq', '-to_type:group');
                break;
            case "organisations":
                $ci->solr->setOpt('fq', '+to_class:party');
                $ci->solr->setOpt('fq', '+to_type:group');
                //exclude relation with identifier (because they fall into researchers category)
                $ci->solr->setOpt('fq', '-relation_identifier_identifier:*');
                break;
            case "publications":
                $ci->solr->setOpt('fq', '+to_class:publication');
                $ci->solr->setOpt('rows',999);
                break;
            case "websites":
                $ci->solr->setOpt('fq', '+to_class:website');
                $ci->solr->setOpt('rows',999);
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

        if ($solrResult && array_key_exists('response', $solrResult) && $solrResult['response']['numFound'] > 0) {
            $result['count'] = $solrResult['response']['numFound'];
            foreach ($solrResult['response']['docs'] as $doc) {
                $data = $doc;
                $result['docs'][] = $data;
            }
        }

        return $result;
    }

}
