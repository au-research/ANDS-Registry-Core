<?php
use ANDS\Mycelium\RelationshipSearchService;
use MinhD\SolrClient\SolrClient;

if (!defined('BASEPATH')) {
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

    /** @var \MinhD\SolrClient\SolrClient */
    private $solrClient = null;

    /**
     * Primary handle function
     *
     * @return array
     */
    public function handle($params='')
    {
        //$this->solrClient = new SolrClient(\ANDS\Util\Config::get('app.solr_url'), 8983, 'relationships');

        return [
            'data' => $this->getRelatedData(),
            'software' => $this->getRelatedSoftware(),
            'publications' => $this->getRelatedPublication(),
            'programs' => $this->getRelatedPrograms(),
            'grants_projects' =>$this->getRelatedGrantsProjects(),
            'services' => $this->getRelatedService(),
            'websites' => $this->getRelatedWebsites(),
            'researchers' => $this->getRelatedResearchers(),
            'organisations' => $this->getRelatedOrganisations()
        ];
    }

    /**
     * Obtain related data from SOLR
     * @return array
     */
    private function getRelatedData() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'collection',
            'not_to_type' => 'software',
            'rows' => 5
        ]);

        return $result->toArray();
    }

    /**
     * Obtain related software from SOLR
     * @return array
     */
    private function getRelatedSoftware() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'collection',
            'to_type' => 'software',
            'rows' => 5
        ]);

        return $result->toArray();
    }

    /**
     * Obtain related programs from SOLR
     * @return array
     */
    private function getRelatedPrograms() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'activity',
            'to_type' => 'program',
            'rows' => 5
        ]);

        return $result->toArray();
    }

    /**
     * Obtain related activity that are grants or projects from SOLR
     * @return array
     */
    private function getRelatedGrantsProjects() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'activity',
            'not_to_type' => 'program',
            'rows' => 5
        ]);

        return $result->toArray();
    }

    /**
     * Obtain related publications from SOLR
     * @return array
     */
    private function getRelatedPublication() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'publication',
            'rows' => 5
        ]);

        return $result->toArray();
    }

    /**
     * Obtain related services from SOLR
     * @return array
     */
    private function getRelatedService() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'service',
            'rows' => 5
        ]);

        return $result->toArray();
    }

    /**
     * Obtain related websites from SOLR
     * @return array
     */
    private function getRelatedWebsites() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'website',
            'rows' => 5
        ]);

        return $result->toArray();
    }

    /**
     * Obtain related researchers from SOLR
     * relationships where there's a hasPrincipalInvestigator edge is ranked higher via boosted query
     * @return array
     */
    private function getRelatedResearchers() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'party',
            'not_to_type' => 'group',
            'boost_relation_type' => 'hasPrincipalInvestigator',
            'rows' => 5
        ]);

        return $result->toArray();

    }

    /**
     * Obtain related organisations from SOLR
     * @return array
     */
    private function getRelatedOrganisations() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'party',
            'to_type' => 'group',
            'rows' => 5
        ]);

        return $result->toArray();
    }


    /**
     * Render a backward compatible array to be used for displaying in Portal
     * @param \MinhD\SolrClient\SolrSearchResult $result
     * @return array
     */
    private function renderBackwardCompatibleArray(\MinhD\SolrClient\SolrSearchResult $result) {
        $docs = json_decode($result->getDocs('json'), true);

        $formattedDoc = collect($docs)->map(function($doc) {

            $toRecord = null;
            if ($doc['to_identifier_type'] === "ro:id") {
                $toRecord = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($doc['to_identifier']);
            }

            return [
                'from_class' => $this->ro->class,
                'from_id' => $this->ro->id,
                'from_key' => $this->ro->key,
                'from_slug' => $this->ro->slug,
                'from_status' => $this->ro->status,
                'from_title' => $this->ro->title,
                'from_type' => $this->ro->type,
                'id' => $doc['id'],
                'relation' => collect($doc['relations'])->map(function ($relation) {
                    return array_key_exists('relation_type', $relation) ? $relation['relation_type'] : '';
                }),
                'relation_identifier_id' => $toRecord ? null : $doc['to_identifier'],
                'relation_identifier_identifier' => $toRecord ? null : (isset($doc['to_title']) ? : ''),
                'relation_identifier_type' => $toRecord ? null : $doc['to_identifier_type'],
                'relation_origin' => collect($doc['_childDocuments_'])->map(function ($relation) {
                    return array_key_exists('relation_origin', $relation) ? $relation['relation_origin'] : '';
                }),
                'to_class' => $doc['to_class'],
                'to_id' => $toRecord ? $doc['to_identifier'] : false,
                'to_key' => $toRecord ? $toRecord->key : false,
                'to_slug' => $toRecord ? $toRecord->slug : false,
                'to_title' => $toRecord ? $doc['to_title'] : false,
                'to_type' => $toRecord ? $doc['to_type'] : false,
            ];
        });

        return [
            'count' => $result->getNumFound(),
            'docs' => $formattedDoc
        ];
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
                //boost PrincipalInvestigator type relationships
                $ci->solr->setOpt('defType', 'edismax');
                $ci->solr->setOpt('bq', 'relation:*PrincipalInvestigator*');
                $ci->solr->setOpt('boost', '5');
                $ci->solr->setOpt('sort', 'score desc, to_title asc');
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
