<?php
namespace ANDS\API\Registry\Handler;
use ANDS\API\Task\ImportTask;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use ANDS\Util\IPValidator;
use \Exception as Exception;
use \XSLTProcessor as XSLTProcessor;

define('SERVICES_MODULE_PATH', REGISTRY_APP_PATH . 'services/');
// TODO: find a better way to handle hanging contet generation
// cache what we can and generate the rest
set_time_limit(60);

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
    private $valid_methods = array('core', 'descriptions', 'relationships', 'subjects', 'spatial', 'temporal', 'citations', 'dates', 'relatedInfo', 'identifiers', 'rights', 'contact', 'directaccess', 'logo', 'tags', 'existenceDates', 'identifiermatch', 'suggest','jsonld', 'altmetrics');
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
                    default:
                        try {
                            $r = $this->ro_handle($m1, $resource);
                            if (!is_array_empty($r)) {
                                $result[$m1] = $r;
                            }
                        } catch (Exception $e) {
                            // soft error
                            $result[$m1] = [];

                            // TODO: log the error and action on them
                            monolog(get_exception_msg($e), 'error', 'error');
                        }
                        break;
                }
            } else {

                // middleware
                $whitelist = Config::get('app.api_whitelist_ip');
                if (!$whitelist) {
                    throw new Exception("Whitelist IP not configured properly. This operation is unsafe.");
                }
                $ip = $this->getIPAddress();
                if (!IPValidator::validate($ip, $whitelist)) {
                    throw new Exception("IP: $ip is not whitelisted for this behavior");
                }

                if ($m1 == "sync") {
                    $ro = $resource['ro'];
                    return $this->syncRecordById($ro->id);
                } elseif ($m1 == "scholix") {
                    $r = $this->ro_handle($m1, $resource);
                    if (!is_array_empty($r)) {
                        $result[$m1] = $r;
                    }
                    return $result;
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
     * @return string
     */
    private function getIPAddress()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }

        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }

        if (isset($_SERVER["REMOTE_ADDR"])) {
            return $_SERVER["REMOTE_ADDR"];
        }

        // Run by command line??
        return "127.0.0.1";
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

    private function syncRecordById($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);

        if (!RegistryObjectsRepository::isPublishedStatus($record->status)) {
             return "Record $record->title($record->id) is NOT PUBLISHED";
        }

        $importTask = new ImportTask;
        $importTask->init([
            'name' => 'ImportTask',
            'params' => http_build_query([
                'ds_id' => $record->datasource->data_source_id,
                'pipeline' => 'SyncWorkflow'
            ])
        ]);

        $importTask
            ->setCI($this->ci)
            ->initialiseTask()
            ->skipLoadingPayload()
            ->enableRunAllSubTask()
            ->setTaskData('importedRecords', [$record->id])
            ->setTaskData('targetStatus','PUBLISHED');

        $importTask->run();

        return $importTask->toArray();
    }

}