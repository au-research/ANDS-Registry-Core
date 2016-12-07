<?php
namespace ANDS\API\Registry\Handler;
use ANDS\API\Task\FixRelationshipTask;
use ANDS\API\Task\ImportTask;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;
use \Exception as Exception;
use \XSLTProcessor as XSLTProcessor;

define('SERVICES_MODULE_PATH', REGISTRY_APP_PATH . 'services/');

/**
 * Handles registry/object
 * Used for Registry Object API
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class ObjectHandler extends Handler{

     private $default_params = array(
        'q' => '*:*',
        'fl' => 'id,key,slug,title,class,type,data_source_id,group,created,status',
        'wt' => 'json',
        'indent' => 'on',
        'rows' => 20,
    );
    private $valid_methods = array('core', 'descriptions', 'relationships', 'subjects', 'spatial', 'temporal', 'citations', 'dates', 'relatedInfo', 'identifiers', 'rights', 'contact', 'directaccess', 'logo', 'tags', 'existenceDates', 'identifiermatch', 'suggest', 'connectiontrees');

    private $valid_filters = array('q', 'fq', 'fl', 'sort', 'start', 'rows', 'int_ref_id', 'facet_field', 'facet_mincount', 'facet');

    /**
     * Handles registry/object
     * Used for Registry Object API
     * @return array
     */
    public function handle()
    {
        if ((int) ini_get("memory_limit") < 384000000) {
            ini_set('memory_limit', 384000000);
        }
        if ($this->params['identifier']) {
            // registry/object/(:id)
            return array(
                $this->record_api(),
            );
        } else {
            // registry/object
            return array(
                $this->search_api(),
            );
        }
    }

    /**
     * Handles registry/object
     * Search API
     * @return array
     */
    private function search_api()
    {
        $result = array();
        $this->ci->load->library('solr');

        //default search filters
        $filters = $this->default_params;
        $mode = $this->ci->input->get('mode') ? $this->ci->input->get('mode') : 'normal';

        //getting GETs and merge that with valid filters to determine search filters
        $gets = $this->ci->input->get() ? $this->ci->input->get() : array();
        $forwarded_params = array_intersect_key(array_flip($this->valid_filters), $gets);
        foreach ($forwarded_params as $param_name => $_) {
            $filters[$param_name] = $this->ci->input->get($param_name);
        }

        //setting search mode
        if ($mode == 'portal_search') {
            $this->ci->solr->setFilters($filters);
        } else {
            foreach ($filters as $key => $field) {
                $this->ci->solr->setOpt($key, $field);
            }
        }

        //fix for multiple facets field
        //eg: facet_field=class,type,group
        if (isset($filters['facet_field'])) {
            $facets = explode(',', $filters['facet_field']);
            foreach ($facets as $f) {
                $this->ci->solr->setFacetOpt('field', $f);
            }
        }

        $result = $this->ci->solr->executeSearch(true);

        return $result;
    }

    /**
     * Handles registry/object/{id}
     * called via @object
     * @return array
     */
    private function record_api()
    {
        initEloquent();

        $result = array();

        if ($this->params['object_module']) {
            $method1s = explode('-', $this->params['object_module']);
        } else {
            $method1s = $this->valid_methods;
        }

        $id = $this->params['identifier'] ? $this->params['identifier'] : false;
        if (!$id) {
            $id = $this->ci->input->get('id') ? $this->ci->input->get('id') : false;
        }

        if (!$id) {
            $id = $this->ci->input->get('q') ? $this->ci->input->get('q') : false;
        }

        $benchmark = $this->ci->input->get('benchmark') ? $this->ci->input->get('benchmark') : false;

        if ($benchmark) {
            $result['benchmark'] = array();
        }

        $resource = $this->populate_resource($this->params['identifier']);


        foreach ($method1s as $m1) {

            if ($benchmark) {
                $this->ci->benchmark->mark('start');
            }

            if ($m1 && in_array($m1, $this->valid_methods)) {
                switch ($m1) {
                    case 'get':
                    case 'registry':
                        $result[$m1] = $this->ro_handle('core', $resource);
                        break;
                    case 'relationships-old':
                        $result[$m1] = $this->relationships_handler($resource);
                        break;
                    default:
                        try {
                            $r = $this->ro_handle($m1, $resource);
                            if (!is_array_empty($r)) {
                                $result[$m1] = $r;
                            }
                        } catch (Exception $e) {
                            $result[$m1] = $e->getMessage();
                        }
                        break;
                }
            } else {

                acl_enforce("REGISTRY_USER");

                //special case
                if ($m1 == 'solr_index') {
                    $ro = $resource['ro'];
                    $ro->sync();
                    return $ro->indexable_json();
                } elseif ($m1 == 'grants_structure') {
                    $this->ci->load->library('solr');
                    $ro = $resource['ro'];
                    $result = [
                        'id' => $ro->id,
                        'title' => $ro->title,
                        'childs' => $ro->getGrantsStructureSOLR()
                    ];

                    return $result;
                } elseif ($m1 == 'sync') {
                    $ro = $resource['ro'];
                    return $ro->sync();
                } else if($m1 == 'relations_index') {
                    $ro = $resource['ro'];

                    $importTask = new ImportTask;
                    $importTask->init([
                        "name" => "Get Relationship Index for $ro->id",
                        'params' => http_build_query([
                            'targetStatus' => 'PUBLISHED',
                            'ds_id' => $ro->data_source_id,
                            'runAll' => 1,
                            'pipeline' => 'UpdateRelationshipWorkflow'
                        ])
                    ])->skipLoadingPayload()->initialiseTask();
                    $task = $importTask->getTaskByName("IndexRelationship");

                    $record = RegistryObjectsRepository::getRecordByID($ro->id);
                    $relationships = RelationshipProvider::getMergedRelationships($record);
                    $index = $task->getRelationshipIndex($relationships);

                    return $index;

                } else if ($m1 == 'fixRelationship') {

                    $ro = $resource['ro'];

                    $task = new ImportTask;
                    $task->init([
                        "name" => "Fix Relationship for $ro->id",
                       'params' => http_build_query([
                           'targetStatus' => 'PUBLISHED',
                           'ds_id' => $ro->data_source_id,
                           'runAll' => 1,
                           'pipeline' => 'UpdateRelationshipWorkflow'
                       ])
                    ])->skipLoadingPayload()->initialiseTask();

                    $task->setTaskData('importedRecords', [$ro->id]);
                    $task->run();

                    return $task->toArray();

                } else if ($m1 == 'relatedObjects') {
                    $ro = $resource['ro'];
                    $record = RegistryObjectsRepository::getRecordByID($ro->id);
                    return RelationshipProvider::get($record);
                } else if ($m1 == 'relatedGrantsNetwork') {
                    $ro = $resource['ro'];
                    $record = RegistryObjectsRepository::getRecordByID($ro->id);
                    RelationshipProvider::process($record);
                    return RelationshipProvider::getImplicitRelationship($record);
                } else if ($m1 == 'relatedObjectsIndex') {
                    $this->ci->benchmark->mark('start');
                    $ro = $resource['ro'];
                    $relatedObjects = $ro->getAllRelatedObjects();
                    return $relatedObjects;
                } else if ($m1 == 'updatePortalIndex') {
                    $ro = $resource['ro'];
                    $includes = explode(',', $this->ci->input->get('includes'));
                    $index = $ro->getPortalRelationshipIndex($includes);
                    return $index;
                } else if ($m1 == 'updateRelationsIndex') {
                    $this->ci->benchmark->mark('start');
                    $ro = $resource['ro'];
                    $includes = explode(',', $this->ci->input->get('includes'));
                    $index = $ro->getRelationshipIndex($includes);

                    $this->ci->load->library('solr');
                    $this->ci->solr->init()->setCore('relations');
                    $add = $this->ci->solr->add_json(json_encode($index), true);
                    $commit = $this->ci->solr->commit();

                    $this->ci->benchmark->mark('end');
                    $took = $this->ci->benchmark->elapsed_time('start', 'end');

                    return [
                        'took' => $took,
                        'size' => sizeof($index),
                        'add' => json_decode($add, true),
                        'commit' => json_decode($commit, true)
                    ];
                }
            }

            if ($benchmark) {
                $this->ci->benchmark->mark('end');
                $result['benchmark'][$m1] = $this->ci->benchmark->elapsed_time('start', 'end');
            }
        }

        return $result;
    }

    /**
     * Handles specific handler for registry object API
     * Handlers are located at registry/services/method_handlers/registry_object_handlers
     *
     * @todo  migrate method handlers over to the API
     * @todo  enable method handlers to be backward compatible to old registry_api
     * @param  string $handler
     * @param  mixed $resource
     * @return response
     */
    private function ro_handle($handler, $resource)
    {
        require_once SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/' . $handler . '.php';
        $handler = new $handler($resource);
        $result = $handler->handle();
        unset($handler);
        return $result;
    }

    /**
     * Relationships handler
     *
     * @todo   migrate to own handler at registry_object_handlers
     * @todo   migrate along with getFunders()
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param bool $resource
     * @return array
     * @throws Exception
     */
    private function relationships_handler($resource = false)
    {
        if (!$resource) throw new Exception("No resource constructed for relationship handler");

        $this->ci->load->model('registry_object/registry_objects', 'ro');

        $limit = isset($_GET['related_object_limit']) ? $_GET['related_object_limit'] : 5;
        $types = array('collection', 'party_one', 'party_multi', 'activity', 'service');

        $record = $resource['ro'];
        $relationships = $record->getConnections(true, null, $limit, 0, false);
        $relationships = $relationships[0];

        if (isset($relationships['activity'])) {
            //fix array key values
            $relationships['activity'] = array_values($relationships['activity']);
            for ($i = 0; $i < count($relationships['activity']); $i++) {
                $funder = $this->getFunders($relationships['activity'][$i]['registry_object_id']);
                if ($funder != '') {
                    $relationships['activity'][$i]['funder'] = "(funded by " . $funder . ")";
                }
            }
        }

        //get the correct count in SOLR
        $this->ci->load->library('solr');
        $search_class = $record->class;
        if ($record->class == 'party') {
            if (strtolower($record->type) == 'person') {
                $search_class = 'party_one';
            } elseif (strtolower($record->type) == 'group') {
                $search_class = 'party_multi';
            }
        }

        foreach ($types as $type) {
            if (isset($relationships[$type . '_count'])) {
                $this->ci->solr->init();
                $this->ci->solr
                    ->setOpt('fq', '+related_' . $search_class . '_id:' . $record->id)
                    ->setOpt('rows', '0');
                if ($type == 'party_one') {
                    $this->ci->solr->setOpt('fq', '+class:party')->setOpt('fq', '+type:person');
                } elseif ($type == 'party_multi') {
                    $this->ci->solr->setOpt('fq', '+class:party')->setOpt('fq', '+type:group');
                } else {
                    $this->ci->solr->setOpt('fq', '+class:' . $type);
                }
                $result = $this->ci->solr->executeSearch(true);
                $relationships[$type . '_count_solr'] = $result['response']['numFound'];
            }
        }

        $includes = explode(',', $this->ci->input->get('includes'));


        if (in_array('grants', $includes)) {
            $relatedObjects = $record->getAllRelatedObjects();
            $childActivities = $record->getChildActivities($relatedObjects);

            $grants = [];
            $programs = [];
            if ($childActivities && sizeof($childActivities) > 0) {
                foreach ($childActivities as $childActivity) {
                    if (isset($childActivity['type'])) {
                        if (trim(strtolower($childActivity['type'])) == 'program') {
                            $programs[] = $childActivity;
                        } elseif (trim(strtolower($childActivity['type'])) == 'grant') {
                            $grants[] = $childActivity;
                        }
                    }
                }
            }

            $relationships['grants'] = [
                'programs' => $programs,
                'grants' => $grants,
                'data_output' => $record->getDataOutput($childActivities, $relatedObjects),
                'funders' => $record->getFunders(),
//                'structure' => $record->getStructuredGrantsAtNode($relatedObjects)
            ];

            if ($record->class == 'activity') {
                $relationships['grants']['publications'] = $record->getDirectPublication();
            }

            //useful for debugging
            if ($only = $this->ci->input->get('only')) {
                $relationships = $relationships['grants'][$only];
            }
        }

        return $relationships;
    }


    /**
     * Helper method for relationship_handlers
     * @param  string $ro_id registry_object_id
     * @return array
     */
    private function getFunders($ro_id)
    {
        $CI = &get_instance();
        $CI->load->model('registry_object/registry_objects', 'mro');
        $funders = "";

        $grant_object = $CI->mro->getByID($ro_id);
        if ($grant_object->status == PUBLISHED) {
            $related_party = $grant_object->getRelatedObjectsByClassAndRelationshipType(['party'], ['isFunderOf', 'isFundedBy']);
            if (is_array($related_party) && isset($related_party[0])) {
                foreach ($related_party as $aFunder) {
                    $funders .= " " . $aFunder['title'];
                }
            }
        }
        return $funders;
    }

    /**
     * populate the SOLR index for fast searching on normalized fields and the commonly used Simple XML
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param  registry_object_id $id
     */
    private function populate_resource($id)
    {

        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $record = $this->ci->ro->getByID($id);
        if (!$record) {
            throw new Exception('No record with ID ' . $this->params['identifier'] . ' found');
        }

        $resource = array();

        //local SOLR index for fast searching
        $ci = &get_instance();
        $ci->load->library('solr');
        $ci->solr->setOpt('fq', '+id:' . $id);
        $result = $ci->solr->executeSearch(true);

        if (sizeof($result['response']['docs']) == 1) {
            $resource['index'] = $result['response']['docs'][0];
        }

        //local XML resource
        $xml = $record->getSimpleXML();
        $xml = addXMLDeclarationUTF8(($xml->registryObject ? $xml->registryObject->asXML() : $xml->asXML()));
        $xml = simplexml_load_string($xml);
        $xml = simplexml_load_string(addXMLDeclarationUTF8($xml->asXML()));
        if ($xml) {
            $resource['xml'] = $xml;
            $rifDom = new \DOMDocument();
            $rifDom->loadXML($record->getRif());
            $gXPath = new \DOMXpath($rifDom);
            $gXPath->registerNamespace('ro', 'http://ands.org.au/standards/rif-cs/registryObjects');
            $resource['gXPath'] = $gXPath;
        }

        $resource['ro'] = $record;
        $resource['params'] = array();
        $resource['default_params'] = $this->default_params;

        return $resource;
    }

}