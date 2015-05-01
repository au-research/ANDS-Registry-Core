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

    private $valid_methods = array('core', 'descriptions', 'relationships', 'subjects', 'spatial', 'temporal','citations','dates','connectiontrees','relatedInfo', 'identifiers','rights', 'contact','directaccess', 'suggest', 'logo', 'tags','existenceDates', 'identifiermatch');

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
                        case 'relationships'    :  $result[$m1] = $this->relationships_handler(); break;

                        default : 
                            try {
                                $r = $this->ro_handle($m1);
                                if (!is_array_empty($r)) {
                                     $result[$m1] = $r;
                                }
                               
                            } catch (Exception $e) {
                                $result[$m1] = array();
                            }
                            
                            break;

                    }
                }
            }

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

    /**
    * Relationships handler
    * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
    * @return array
    */
    private function relationships_handler() {
        $relationships = array();

        $ci =& get_instance();
        $ci->load->model('registry_object/registry_objects', 'ro');

        $limit = isset($_GET['related_object_limit']) ? $_GET['related_object_limit'] : 5;

        $types = array('collection','party_one', 'party_multi', 'activity', 'service');

        

        $relationships = $this->ro->getConnections(true,null,$limit,0,true);
        $relationships = $relationships[0];

        if(isset($relationships['activity'])){
            for($i=0;$i<count($relationships['activity']);$i++){
                $funder = $this->getFunders($relationships['activity'][$i]['registry_object_id']);
                if($funder!='') $relationships['activity'][$i]['funder']= "(funded by ".$funder.")";
            }
        }

        //get the correct count in SOLR
        $ci->load->library('solr');
        $search_class = $this->ro->class;
        if($this->ro->class=='party') {
            if (strtolower($this->ro->type)=='person'){
                $search_class = 'party_one';
            } elseif(strtolower($this->ro->type)=='group') {
                $search_class = 'party_multi';
            }
        }

        foreach ($types as $type) {
            if(isset($relationships[$type.'_count'])) {
                $ci->solr->init();
                $ci->solr
                    ->setOpt('fq', '+related_'.$search_class.'_id:'.$this->ro->id)
                    ->setOpt('rows', '0');
                if ($type=='party_one') {
                    $ci->solr->setOpt('fq', '+class:party')->setOpt('fq', '+type:person');
                } elseif ($type=='party_multi') {
                    $ci->solr->setOpt('fq', '+class:party')->setOpt('fq', '+type:group');
                } else {
                    $ci->solr->setOpt('fq', '+class:'.$type);
                }
                $result = $ci->solr->executeSearch(true);
                $relationships[$type.'_count_solr'] = $result['response']['numFound'];
            }
        }

        return $relationships;
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
    switch($type)
    {
        case 'doi':
            if(!strpos($identifier,"doi.org/")) $identifier_href ="http://dx.doi.org/".$identifier;
            else $identifier_href = "http://dx.doi.org/".substr($identifier,strpos($identifier,"doi.org/")+8);
            $identifiers['href'] = $identifier_href;
            $identifiers['display_text'] = strtoupper($type);
            $identifiers['hover_text'] = 'Resolve this DOI';
            $identifiers['display_icon'] = '<img class="identifier_logo" src= '.portal_url().'assets/core/images/icons/doi_icon.png alt="DOI Link"/>';
            return  $identifiers;
            break;
        case 'ark':
            $identifiers['href'] = '';
            $identifiers['display_icon'] = '';
            if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier && str_replace('/ark:/','',$identifier)!=$identifier){
                $identifiers['href'] = $identifier;
                $identifiers['display_icon'] = '<img class="identifier_logo" src= '.portal_url().'assets/core/images/icons/external_link.png alt="External Link"/>';
            }
            elseif(strpos($identifier,'/ark:/')>1){
                $identifiers['href'] = 'http://'.$identifier;
                $identifiers['display_icon'] = '<img class="identifier_logo" src= '.portal_url().'assets/core/images/icons/external_link.png alt="External Link"/>';
            }
            $identifiers['display_text'] = 'ARK';
            $identifiers['hover_text'] = 'Resolve this ARK identifier';
            return $identifiers;
            break;
        case 'orcid':
            if(!strpos($identifier,"orcid.org/")) $identifier_href ="http://orcid.org/".$identifier;
            else $identifier_href = "http://orcid.org/".substr($identifier,strpos($identifier,"orcid.org/")+10);
            $identifiers['href'] = $identifier_href;
            $identifiers['display_text'] = 'ORCID';
            $identifiers['display_icon'] = '<img class="identifier_logo" src= '.portal_url().'assets/core/images/icons/orcid_icon.png alt="ORCID Link"/>';
            $identifiers['hover_text'] = 'Resolve this ORCID';
            return  $identifiers;
            break;
        case 'AU-ANL:PEAU':
            if(!strpos($identifier,"nla.gov.au/")) $identifier_href ="http://nla.gov.au/".$identifier;
            else $identifier_href = "http://nla.gov.au/".substr($identifier,strpos($identifier,"nla.gov.au/")+11);
            $identifiers['href'] = $identifier_href;
            $identifiers['display_text'] = 'NLA';
            $identifiers['display_icon'] = '<img class="identifier_logo" src= '.portal_url().'assets/core/images/icons/nla_icon.png alt="NLA Link"/>';
            $identifiers['hover_text'] = 'View the record for this party in Trove';
            return  $identifiers;
            break;
        case 'handle':
            if(strpos($identifier,"dl:")>0) $identifier_href ="http://hdl.handle.net/".substr($identifier,strpos($identifier,"hdl:")+4);
            elseif(strpos($identifier,"dl.handle.net/")>0) $identifier_href ="http://hdl.handle.net/".substr($identifier,strpos($identifier,"hdl.handle.net/")+15);
            else $identifier_href = "http://hdl.handle.net/".$identifier;
            $identifiers['href'] = $identifier_href;
            $identifiers['display_text'] = 'Handle';
            $identifiers['display_icon'] = '<img class="identifier_logo" src= '.portal_url().'assets/core/images/icons/handle_icon.png alt="Handle Link"/>';
            $identifiers['hover_text'] = 'Resolve this handle';
            return  $identifiers;
            break;
        case 'purl':
            if(strpos($identifier,"url.org/")<1) $identifier_href ="http://purl.org/".$identifier;
            else $identifier_href = "http://purl.org/".substr($identifier,strpos($identifier,"purl.org/")+9);
            $identifiers['href'] = $identifier_href;
            $identifiers['display_text'] = 'PURL';
            $identifiers['display_icon'] = '<img class="identifier_logo" src= '.portal_url().'assets/core/images/icons/external_link.png alt="External Link"/>';
            $identifiers['hover_text'] = 'Resolve this PURL';
            return  $identifiers;
            break;
        case 'uri':
            $identifiers['href'] = $identifier;
            $identifiers['display_text'] = strtoupper($type);
            $identifiers['hover_text'] = 'Resolve this URI';
            $identifiers['display_icon'] = '<img class="identifier_logo" src= '.portal_url().'assets/core/images/icons/external_link.png alt="External Link"/>';
            return $identifiers;
            break;
        case 'urn':
            break;
        case 'local':
            $identifiers['display_text'] = 'Local';
            return $identifiers;
            break;
        case 'isil':
            $identifiers['display_text'] = 'ISIL';
            return $identifiers;
            break;
        case 'abn':
            $identifiers['display_text'] = 'ABN';
            return $identifiers;
            break;
        case 'arc':
            $identifiers['display_text'] = 'ARC';
            return $identifiers;
            break;
        default:
            return false;
            break;
    }



}

