<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class Registry_objectsMethod extends MethodHandler {
    private $default_params = array(
        'q' => '*:*',
        'fl' => 'id,key,slug,title,class,data_source_id,group',
        'wt' => 'json',
        'indent' => 'on',
        'rows' => 20
    );

    private $valid_methods = array(
        'get', 'core', 'relationships', 'identifiers','descriptions', 'registry', 'subjects', 'spatial', 'temporal', 'citations', 'relatedInfo','suggest', 'dates', 'connectiontree', 'rights', 'directaccess','contact'
    );

    public $ro = null;
    public $index = null;
    public $xml = null;
    
    //var $params, $options, $formatter; 
    function handle($params=''){
        $this->params = $params;

        //registry_objects/<id>/method1/method2
        $id = isset($params[1]) ? $params[1] : false;
        $method1 = isset($params[2]) ? $params[2]: 'get';
        $method2 = isset($params[3]) ? $params[3]: false;

        $ci =& get_instance();
        // $ci->load->library('benchmark');
        $ci->benchmark->mark('code_start');
        $result = array();
        if ($id){
            $ci->load->model('registry_object/registry_objects', 'ro');
            $this->ro = new _registry_object($id);
            $this->populate_resource($id);
            $method1s = explode('-', $method1);
            foreach($method1s as $m1){
                if($m1 && in_array($m1, $this->valid_methods)) {
                    switch($m1) {

                        case 'get':
                        case 'registry':
                        case 'relationships' :  $result[$m1] = $this->relationships_handler(); break;
                        case 'citations' :      $result[$m1] = $this->citations_handler(); break;
                        case 'connectiontree' : $result[$m1] = $this->connectiontree_handler($id); break;

                        default :  $result[$m1] = $this->ro_handle($m1);  break;

                    }
                }
            }
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
    * Citations handler
    * @author Liz Woods <liz.woods@ands.org.au>
    * @return array
    */
    private function citations_handler() {
        $result = array();
        if ($this->xml) {
            $endNote = 'Provider: Australian National Data Service
Database: Research Data Australia
Content:text/plain; charset="utf-8"


TY  - DATA';
            foreach($this->xml->{$this->ro->class}->citationInfo as $citation){
                 foreach($citation->citationMetadata as $citationMetadata){
                     $contributors = Array();
                     foreach($citationMetadata->contributor as $contributor)
                     {
                        $nameParts = Array();
                        foreach($contributor->namePart as $namePart)
                         {
                                 $nameParts[] = array(
                                 'namePart_type' => (string)$namePart['type'],
                                 'name' => (string)$namePart
                             );
                         }
                         $contributors[] =array(
                             'name' => $nameParts,
                             'seq' => (string)$contributor['seq'],
                         );
                     }
                     usort($contributors,"seq");
                     $displayNames ='';
                     $contributorCount = 0;
                     foreach($contributors as $contributor){
                        $contributorCount++;
                        $displayNames .= formatName($contributor['name']);
                        if($contributorCount < count($contributors)) $displayNames .= "; ";
                     }
                     $identifierResolved = identifierResolution((string)$citationMetadata->identifier, (string)$citationMetadata->identifier['type']);

                     $result[] = array(
                         'type'=> 'metadata',
                         'identifier' => (string)$citationMetadata->identifier,
                         'identifier_type' => strtoupper((string)$citationMetadata->identifier['type']),
                         'identifierResolved' => $identifierResolved,
                         'version' => (string)$citationMetadata->version,
                         'publisher' => (string)$citationMetadata->publisher,
                         'url' => (string)$citationMetadata->url,
                         'context' => (string)$citationMetadata->context,
                         'placePublished' => (string)$citationMetadata->placePublished,
                         'title' => (string)$citationMetadata->title,
                         'date_type' => (string)$citationMetadata->date['type'],
                         'date' => date("Y",strtotime((string)$citationMetadata->date)),
                         'contributors' => $displayNames,
                         'endNote' => $endNote
                     );

                 }
                foreach($citation->fullCitation as $fullCitation){
                    $result[] = array(
                        'type'=> 'fullCitation',
                        'value' => (string)$fullCitation,
                        'citation_type' => (string)$fullCitation['style'],
                        'endNote' => $endNote
                    );

                }
            }

        }
        return $result;
    }

    /**
    * Relationships handler
    * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
    * @return array
    */
    private function relationships_handler() {
        $result = array();
        $specific = isset($this->params[3]) ? $this->params[3]: null;
        if (isset($this->params['mode']) && $this->params['mode']=='unordered') {
            $relationships = $this->ro->getAllRelatedObjects(false, true, true);
        } else {
            $relationships = $this->ro->getConnections(true, $specific);
        }
        return $relationships;
    }

    /**
    * Connection Tree handler
    * @author Liz Woods <liz.woods@ands.org.au>
    * @return array
    */
    private function connectiontree_handler($id) {
        $ci =& get_instance();
        $ci->load->model('registry_object/registry_objects','thisro');
        $ci->load->model('services/connectiontree','connectiontree');
        $ro = $ci->thisro->getByID($id);

        $trees = array();

        if ($ro->class == 'collection') {
            $ancestors = $ci->connectiontree->getImmediateAncestors($ro, true);
            $depth = 5;
            if ($ancestors) {
               foreach ($ancestors AS $ancestor_element) {
                    if($ro->id != $ancestor_element['registry_object_id']){
                        $root_element_id = $ci->connectiontree->getRootAncestor($ci->thisro->getByID($ancestor_element['registry_object_id']), true);
                        $root_registry_object = $ci->thisro->getByID($root_element_id->id);

                        // Only generate the tree if this is a unique ancestor
                        if (!isset($ci->connectiontree->recursed_children[$root_registry_object->id])) {
                            $trees[] = $ci->connectiontree->get($root_registry_object, $depth, true, $ro->id);
                        }
                    }
                }
            } else {
                $trees[] = $ci->connectiontree->get($ro, $depth, true);
            }
        }
        return $trees;
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
            $identifiers['hover_text'] = '';
            $identifiers['display_icon'] = '<img class="identifier_logo" src= '.portal_url().'assets/core/images/icons/doi_icon.png alt="External Link"/>';
            return  $identifiers;
            break;
        case 'ark':
            $identifier = str_replace('http://','',str_replace('https://','',$identifier));
            $identifiers['href'] = '';
            $identifiers['display_text'] = $identifier;
            $identifiers['hover_text'] = '';
            return $identifiers;
            break;
        case 'AU-ANL:PEAU':
            if(!strpos($identifier,"nla.gov.au/")) $identifier_href ="http://nla.gov.au/".$identifier;
            else $identifier_href = "http://nla.gov.au/".substr($identifier,strpos($identifier,"nla.gov.au/")+11);
            $identifiers['href'] = $identifier_href;
            $identifiers['display_text'] = strtoupper($type);
            $identifiers['display_icon'] = '<img class="identifier_logo" src= '.portal_url().'assets/core/images/icons/nla_icon.png alt="External Link"/>';
            return  $identifiers;
            break;
        case 'handle':

            break;
        case 'purl':
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
        default:
            return false;
            break;
    }

}

// generic function to title case a given string

function titleCase($title)
{
    $smallwordsarray = array(
        'of','a','the','and','an','or','nor','but','is','if','then','else','when',
        'at','from','by','on','off','for','in','out','over','to','into','with'
    );

    $re = '/(?#! splitCamelCase Rev:20140412)
    # Split camelCase "words". Two global alternatives. Either g1of2:
      (?<=[a-z])      # Position is after a lowercase,
      (?=[A-Z])       # and before an uppercase letter.
    | (?<=[A-Z])      # Or g2of2; Position is after uppercase,
      (?=[A-Z][a-z])  # and before upper-then-lower case.
    /x';
    $words = explode(' ', $title);

    foreach ($words as $key => $word)
    {
        $a = preg_split($re, $word);
        $count = count($a);
        if($count>1){
            $words[$key] = '';
            for ($i = 0; $i < $count; ++$i) {
                $words[$key] .= ucwords($a[$i])." ";
            }

        } else {
            $word = strtolower($word);
            if ($key == 0 or !in_array($word, $smallwordsarray))
                $words[$key] = ucwords($word);
            else
                $words[$key] = $word;
            }
        }

    $newtitle = implode(' ', $words);

    return $newtitle;

}
