<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class Registry_objectsMethod extends MethodHandler {
    private $default_params = array(
        'q' => '*:*',
        'fl' => 'id,key,slug,title,class,type,data_source_id,group,created,status',
        'wt' => 'json',
        'indent' => 'on',
        'rows' => 20
    );

    private $valid_methods = array('core', 'descriptions', 'relationships', 'subjects', 'spatial', 'temporal','citations','dates',
        'relatedInfo', 'identifiers','rights', 'contact','directaccess', 'suggest', 'logo', 'tags','existenceDates', 'identifiermatch', 'accessPolicy', 'altmetrics');

    public $ro = null;
    public $index = null;
    public $xml = null;

    //var $params, $options, $formatter;
    function handle($params=''){
        $ci =& get_instance();

        $this->params = $params;

        //registry_objects/<id>/method1/method2
        $id = isset($params[1]) ? $params[1] : false;
        $method1 = isset($params[2]) ? $params[2]: 'get';
        $method2 = isset($params[3]) ? $params[3]: false;

        $useCache = $ci->input->get('useCache') ? false : true;

        $cache_id = $id.'-'.$method1;

        $all = implode($this->valid_methods, '-').'-';

        if ($method1 == $all) {
            $cache_id = 'ro-api-'.$id.'-'.'portal';
        }

        if($method1=='get' || strpos($method1, 'rda')!==false) {
            $method1 = implode($this->valid_methods, '-');
            $cache_id = 'ro-api-'.$id.'-'.'portal';
        }

        // $ci->load->library('benchmark');
        $ci->benchmark->mark('code_start');
        $result = array();

        if ($id){
            $ci->load->model('registry_object/registry_objects', 'ro');
            $this->ro = new _registry_object($id);

            //check in cache
            $ci->load->driver('cache');
//            $cache_id = $id.'-'.$method1;
            $updated = (int) $this->ro->getAttribute('updated');

            if (($cache = $ci->cache->file->get($cache_id)) && $useCache) {
                $result = json_decode($cache, true);

                //check cache updated
                $meta = $ci->cache->file->get_metadata($cache_id);

                if ($updated < $meta['mtime']) {
                    $ci->benchmark->mark('code_end');
                    $benchmark = array(
                        'elapsed' => $ci->benchmark->elapsed_time('code_start', 'code_end'),
                        'cached' => $meta['mtime'],
                        'updated' => $updated,
                        'memory_usage' => $ci->benchmark->memory_usage()
                    );
                    return $this->formatter->display($result, $benchmark);
                }
            }

            //delete the cache at the portal side, because the record is updated
            $ci->cache->delete('ro-api-'.$id);

            //fill the result with data
            $this->populate_resource($id);
            $method1s = explode('-', $method1);
            foreach($method1s as $m1){
                if($m1 && in_array($m1, $this->valid_methods)) {
                    switch($m1) {

                        case 'get':
                        case 'registry':           $result[$m1] = $this->ro_handle('core'); break;

                        default :
                            try {
                                $r = $this->ro_handle($m1);
                                if (!is_array_empty($r)) {
                                     $result[$m1] = $r;
                                }

                            } catch (Exception $e) {
                                $result[$m1] = $e->getMessage();
                            }

                            break;

                    }
                }
            }

            $extra = module_hook('append_roapi', $this->ro);
            $result = array_merge($result, $extra);


            //store result in cache
            $cache_content = json_encode($result, true);

            $ci->cache->file->save($cache_id, $cache_content, 36000);

        } else {
            $result = $this->searcher($params);
        }
        $ci->benchmark->mark('code_end');
        $benchmark = array(
            'elapsed' => $ci->benchmark->elapsed_time('code_start', 'code_end'),
            'memory_usage' => $ci->benchmark->memory_usage()
        );
        return $this->formatter->display($result, $benchmark);
    }

    /**
     * Handle an RO handler
     * @param  string $handler NOT NULL
     * @return handler
     */
    private function ro_handle($handler) {
        require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/'.$handler.'.php');
        $handler = new $handler($this->get_resource());
        return $handler->handle();
    }

    /**
     * populate the SOLR index for fast searching on normalized fields and the commonly used Simple XML
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param  registry_object_id $id
     * @return [populated $this->index and $this->xml]
     */
    private function populate_resource($id) {

        //local SOLR index for fast searching
        $ci =& get_instance();
        $ci->load->library('solr');
        $ci->solr->setOpt('fq', '+id:'.$id);
        $result = $ci->solr->executeSearch(true);

        if(sizeof($result['response']['docs']) == 1) {
            $this->index = $result['response']['docs'][0];
        }

        //local XML resource
        $xml = $this->ro->getSimpleXML();
        $xml = addXMLDeclarationUTF8(($xml->registryObject ? $xml->registryObject->asXML() : $xml->asXML()));
        $xml = simplexml_load_string($xml);
        $xml = simplexml_load_string( addXMLDeclarationUTF8($xml->asXML()) );
        if ($xml) {
            $this->xml = $xml;
            $rifDom = new DOMDocument();
            $rifDom->loadXML($this->ro->getRif());
            $gXPath = new DOMXpath($rifDom);
            $gXPath->registerNamespace('ro', 'http://ands.org.au/standards/rif-cs/registryObjects');
            $this->gXPath = $gXPath;
        }
    }

    /**
     * Get the pre constructed resource for this registry object
     * Mainly used for constructing new registry objects handlers
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return array
     */
    function get_resource() {
        return array(
            'index' => $this->index,
            'xml' => $this->xml,
            'gXPath' => $this->gXPath,
            'ro' => $this->ro,
            'params' => $this->params,
            'default_params' => $this->default_params
        );
    }

    /**
    * Search handler
    * Used for searching and interacting with the SOLR index at a RESTful level
    * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
    * @return solr_result
    */
    private function searcher($params) {
        $result = array();
        $ci =& get_instance();
        $ci->load->library('solr');

        //construct the search fields
        $permitted_forwarding_params = explode(',',$this->options['valid_solr_params']);
        $forwarded_params = array_intersect_key(array_flip($permitted_forwarding_params), $this->params);
        $fields = array();
        foreach ($forwarded_params AS $param_name => $_) {
            $fields[$param_name] = $this->params[$param_name];
        }
        $fields = array_merge($this->default_params, $fields);

        //setting search field constraints
        if (isset($this->params['mode']) && $this->params['mode']=='portal_search') {
            $ci->solr->setFilters($fields);
        } else {
            foreach($fields AS $key => $field) {
                $ci->solr->setOpt($key, $field);
            }
        }

        //special fix for facet
        if(isset($this->params['facet_field'])) {
            $facets = explode(',', $this->params['facet_field']);
            foreach($facets as $f) {
                $ci->solr->setFacetOpt('field', $f);
            }
        }

        //get results
        $result = $ci->solr->executeSearch(true);
        return $result;
    }

    private function getFunders($ro_id)
    {
        $CI =& get_instance();
        $CI->load->model('registry_object/registry_objects', 'mro');
        $funders = "";

        $grant_object = $CI->mro->getByID($ro_id);
        if($grant_object->status == PUBLISHED){
           $related_party = $grant_object->getRelatedObjectsByClassAndRelationshipType(['party'] ,['isFunderOf','isFundedBy']);
           if (is_array($related_party) && isset($related_party[0]))
           {
               foreach($related_party as $aFunder)
               $funders .= " ".$aFunder['title'];
           }
        }
        return $funders;
    }


}

///citation formation helper functions

//function to sort contributor names based on the seq number if it exist
function seq($a, $b)
{
    if ($a['seq'] == $b['seq']) {
        return 0;
    }
    return ($a['seq'] < $b['seq']) ? -1 : 1;
}

function cmpTitle($a, $b)
{
    if ($a['name'] == $b['name']) {
        return 0;
    }
    return ($a['name'] < $b['name']) ? -1 : 1;
}

//function to concatenate name values based on the name part type
function formatName($a)
{
    $order = array('family','given','initial','title','superior');
    $displayName = '';
    foreach($order as $o){
        $givenFound = false;
        foreach($a as $namePart)
        {
            if($namePart['namePart_type']==$o) {
                if($namePart['namePart_type']=='given') $givenFound = true;
                if($namePart['namePart_type']=='initial' && $givenFound) $namePart['name']='';
                else $displayName .=  $namePart['name'].", ";
            }
        }

    }
    foreach($a as $namePart)
    {
        if(!$namePart['namePart_type']) {
            $displayName .=  $namePart['name'].", ";
        }
    }
    return trim($displayName,", ")." ";
}

//function to create resolvable link for citation identifiers
function identifierResolution($identifier,$type)
{
    // refactored to IdentifierProvider so that it can be called statically from everywhere
    return \ANDS\Registry\Providers\RIFCS\IdentifierProvider::format($identifier, $type);
}

