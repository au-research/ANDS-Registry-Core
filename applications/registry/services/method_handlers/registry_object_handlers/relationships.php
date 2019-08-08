<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Class:  relationships handler
 * Getting data from the relations index instead of the database call
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Relationships extends ROHandler
{

    /**
     * Primary handle function
     *
     * @return array
     */
    public function handle()
    {
        $result = array(
            'data' => $this->getRelatedFromIndex('data'),
            'software' => $this->getRelatedFromIndex('software'),
            'publications' => $this->getRelatedFromIndex('publications'),

            'programs' => $this->getRelatedFromIndex('programs'),
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
        $relationships = $this->searchById($type, $this->ro->id);

        // get my duplicates and search for their relationships too
        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($this->ro->id);
        $duplicates = $record
            ->getDuplicateRecords()
            ->pluck('registry_object_id')
            ->toArray();

        // early return if there's no duplicate records found
        if (count($duplicates) == 0) {
           return $relationships;
        }

        // find duplicate relationships
        $idQuery = implode(' OR ', $duplicates);
        $duplicateRelationships = $this->searchById($type, $idQuery);

        // return if there's no additional relationships found
        if ($duplicateRelationships['count'] == 0) {
            return $relationships;
        }

        // check for duplicate that already points to existing records
        $existing_to_ids = collect($relationships['docs'])
            ->pluck('to_id')->toArray();
        $existing_to_identifiers = collect($relationships['docs'])
            ->pluck('to_identifier')->toArray();

        // add the relationships that is not already in the existing
        foreach ($duplicateRelationships['docs'] as $relations) {
            if (!in_array($relations['to_id'], $existing_to_ids) || (array_key_exists('to_identifier', $relations) && !in_array($relations['to_identifier'], $existing_to_identifiers))) {
                $relationships['docs'][] = $relations;
                $relationships['count']++;
            }
        }

        return $relationships;
    }

    private function searchById($type, $idQuery)
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
                $ci->solr->setOpt('fq', '-to_type:software');
                break;
            case "software":
                $ci->solr->setOpt('fq', '+to_class:collection');
                $ci->solr->setOpt('fq', '+to_type:software');
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
                $ci->solr->setOpt('fq',
                    '+to_class:party OR relation_origin:IDENTIFIER');
                $ci->solr->setOpt('fq', '-to_type:group');
                //boost hasPrincipalInvestigator type relationships
                $ci->solr->setOpt('defType', 'edismax');
                $ci->solr->setOpt('bq', 'relation:hasPrincipalInvestigator^5');
                break;
            case "organisations":
                $ci->solr->setOpt('fq', '+to_class:party');
                $ci->solr->setOpt('fq', '+to_type:group');
                //exclude relation with identifier (because they fall into researchers category)
                $ci->solr->setOpt('fq', '-relation_identifier_identifier:*');
                break;
            case "publications":
                $ci->solr->setOpt('fq', '+to_class:publication');
                $ci->solr->setOpt('rows', 999);
                break;
            case "websites":
                $ci->solr->setOpt('fq', '+to_class:website');
                $ci->solr->setOpt('rows', 999);
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

        if ($solrResult &&
            array_key_exists('response',
                $solrResult) && $solrResult['response']['numFound'] > 0
        ) {
            $result['count'] = $solrResult['response']['numFound'];
            foreach ($solrResult['response']['docs'] as $doc) {
                $data = $doc;
                $result['docs'][] = $data;
            }
        }

        /**
         * Find all relations for this object
         * Find any extra relations that goes to different objects from my duplicates
         */

        return $result;
    }

}
