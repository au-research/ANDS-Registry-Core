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
        'get', 'core', 'relationships', 'identifiers','descriptions', 'registry', 'subjects', 'spatial', 'temporal', 'citations', 'reuse', 'quality', 'suggest', 'dates', 'connectiontree', 'publications', 'rights', 'directaccess','contact'
    );

    private $ro = null;
    private $index = null;
    private $xml = null;
    
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
                        case 'core':            $result[$m1] = $this->core_handler(); break;
                        case 'descriptions':    $result[$m1] = $this->descriptions_handler();break;
                        case 'relationships' :  $result[$m1] = $this->relationships_handler(); break;
                        case 'identifiers' :    $result[$m1] = $this->identifiers_handler(); break;
                        case 'subjects' :       $result[$m1] = $this->subjects_handler(); break;
                        case 'suggest' :        $result[$m1] = $this->suggest_handler(); break;
                        case 'spatial' :        $result[$m1] = $this->spatial_handler(); break;
                        case 'temporal' :       $result[$m1] = $this->temporal_handler(); break;
                        case 'citations' :      $result[$m1] = $this->citations_handler(); break;
                        case 'reuse' :          $result[$m1] = $this->relatedInfo_handler('reuseInformation'); break;
                        case 'quality' :        $result[$m1] = $this->relatedInfo_handler('dataQualityInformation'); break;
                        case 'dates' :          $result[$m1] = $this->dates_handler(); break;
                        case 'publications' :   $result[$m1] = $this->relatedInfo_handler('publication'); break;
                        case 'connectiontree' : $result[$m1] = $this->connectiontree_handler($id); break;
                        case 'rights' :         $result[$m1] = $this->rights_handler(); break;
                        case 'directaccess' :   $result[$m1] = $this->download_handler(); break;
                        case 'contact' :        $result[$m1] = $this->contact_handler(); break;
                    }
                }
            }
        } else {
            $result = $this->searcher($params);
        }
        $ci->benchmark->mark('code_end');
        $benchmark = array(
            'elapsed' => $ci->benchmark->elapsed_time('code_start', 'code_end')
        );
        return $this->formatter->display($result, $benchmark);
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
     * Subjects handler
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return array list of subjects from SOLR index
     */
    private function subjects_handler() {
        $result = array();
        if($this->index) {
            //subject_value_unresolved, subject_value_resolved, subject_type, subject_vocab_uri
            foreach($this->index['subject_value_unresolved'] as $key=>$sub) {
                $result[] = array(
                    'subject' => $sub,
                    'resolved' => titleCase($this->index['subject_value_resolved'][$key]),
                    'type' => $this->index['subject_type'][$key],
                    'vocab_uri' => $this->index['subject_vocab_uri'][$key],
                );
            }
        }
        return $result;
    }

    /**
     * Suggested Datasets handler
     * returns a list of suggested datasets based on different type of pools
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return array
     */
    private function suggest_handler() {
        $result = array();

        //pools
        $suggestors = array('subjects');

        //populate the pool with different suggestor
        $ci =& get_instance();

        foreach ($suggestors as $suggestor) {
            $ci->load->model('registry_object/suggestors/'.$suggestor, 'ss');
            $ci->ss->set_ro($this->ro);
            $result[$suggestor] = $ci->ss->suggest();
        }

        return $result;
    }

    /**
     * Identifiers handler
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return array list of identifier from SOLR index
     */
    private function identifiers_handler() {
        $result = array();
        if($this->index) {
            //identifier_type, identifier_value
            foreach($this->index['identifier_type'] as $key=>$type) {
                $result[] = array(
                    'type' => $type,
                    'value' => $this->index['identifier_value'][$key],
                    'identifier' => identifierResolution($this->index['identifier_value'][$key],$type)
                );
            }
        }
        return $result;
    }

    /**
     * Spatial handler
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return array list of spatial polygons, extents and center from SOLR index, provide an area sum
     */
    private function spatial_handler() {
        $result = array();
        if($this->index && isset($this->index['spatial_coverage_extents'])) {
            //spatial_coverage_extents, spatial_coverage_polygons, spatial_coverage_centres, spatial_coverage_area_sum
            foreach($this->index['spatial_coverage_extents'] as $key=>$sub) {
                $result[] = array(
                    'extent' => $sub,
                    'polygon' => $this->index['spatial_coverage_polygons'][$key],
                    'center' => $this->index['spatial_coverage_centres'][$key],
                );
                if($this->index['spatial_coverage_area_sum']) $result['area_sum'] = $this->index['spatial_coverage_area_sum'];
            }
        }
        return $result;
    }

    /**
    * Temporal handler
    * @author Liz Woods <liz.woods@ands.org.au>
    * @return array list of all temporal fields
    */
    private function temporal_handler() {
        $result = array();
        if ($this->index && $this->xml->{$this->ro->class}->coverage) {
            if($this->xml->{$this->ro->class}->coverage->temporal){
                foreach($this->xml->{$this->ro->class}->coverage->temporal->date as $date){
                    $eachDate = Array();
                        $eachDate[] = Array(
                            'type'=>(string)$date['type'],
                            'dateFormat'=>(string)$date['dateFormat'],
                            'date'=>(string)($date)
                        );
                    $result[] = Array(

                        'type' => 'date',
                        'date' => $eachDate
                    );
                }
                foreach($this->xml->{$this->ro->class}->coverage->temporal->text as $temporal){
                    $result[] = Array(
                        'type' => 'text',
                        'date' => (string)$temporal
                    );
                }
            }
        }
        return $result;
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
    * CORE handler
    * Returns core registry object attribute
    * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
    * @return array
    */
    private function core_handler() {
        $result = array();
        $fl = isset($this->params['fl']) ? explode(',',$this->params['fl']) : explode(',',$this->default_params['fl']);
        foreach($fl as $f) {
            $attr = $this->ro->{$f};
            if(!$attr) $attr = $this->ro->getAttribute($f);
            if(!$attr) $attr = null;
            $result[$f] = $attr;
        }
        return $result;
    }

    /**
    * Descriptions handler
    * As an example on how to get data from multiple source, index and xml
    * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
    * @return array list of description with types
    */
    private function descriptions_handler() {
        $result = array();
        if ($this->xml) {
            foreach($this->xml->{$this->ro->class}->description as $description){
                $type = (string) $description['type'];
                $description_str = html_entity_decode((string) $description);
                $result[] = array(
                    'type' => $type,
                    'description' => $description_str
                );
            }
        }
        return $result;
    }

    /**
    * Related Info handler
    * @author Liz Woods <liz.woods@ands.org.au>
    * @param  string type
    * @return array
    */
    private function relatedInfo_handler($relatedInfo_type) {
        $result = array();
        if ($this->xml) {
            foreach($this->xml->{$this->ro->class}->relatedInfo as $relatedInfo){
                $type = (string) $relatedInfo['type'];
                $identifier_resolved = identifierResolution((string) $relatedInfo->identifier, (string) $relatedInfo->identifier['type']);
                if($type==$relatedInfo_type)
                $result[] = array(
                    'type' => $type,
                    'title' =>  (string) $relatedInfo->title,
                    'identifier' => Array('identifier_type'=>(string) $relatedInfo->identifier['type'],'identifier_value'=>(string) $relatedInfo->identifier,'identifier_href'=>$identifier_resolved),
                    'relation' =>Array('relation_type'=>(string) $relatedInfo->relation['type'],'description'=>(string) $relatedInfo->relation->description,'url'=>(string) $relatedInfo->relation->url),
                    'notes' => (string) $relatedInfo->notes
                );
            }
        }
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
    * Citations handler
    * @author Liz Woods <liz.woods@ands.org.au>
    * @return array
    */
    private function dates_handler() {
        $result = array();
        if ($this->xml) {
            foreach($this->xml->{$this->ro->class}->dates as $dates){
                $eachDate = Array();
                $displayType = titleCase(str_replace("dc.","",(string) $dates['type']));
                foreach($dates as $date) {
                   $eachDate[] = Array(
                       'type'=>(string)$date['type'],
                       'dateFormat'=>(string)$date['dateFormat'],
                       'date'=>(string)($date)

                   );
                }
                $result[] = Array(
                    'type' => (string) $dates['type'],
                    'displayType' => $displayType,
                    'date' => $eachDate
                );
            }
        }
        return $result;
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

    /**
    * Rights handler
    * @author Liz Woods <liz.woods@ands.org.au>
    * @return array
    */
    private function rights_handler() {
        $rights = array();
        if ($this->xml) {
            foreach($this->xml->{$this->ro->class}->rights as $right){

                foreach($right->accessRights as $accessRights){
                    $rights[] = Array(
                      'rights_type' => 'accessRights',
                      'uri'=>(string)$accessRights['rightsUri'],
                      'type' => (string)$accessRights['type'],
                      'value' => (string)$accessRights
                    );
                }
                foreach($right->rightsStatement as $rightsStatement){
                    $rights[] = Array(
                        'rights_type' => 'rightsStatement',
                        'uri'=>(string)$rightsStatement['rightsUri'],
                        'type' => (string)$rightsStatement['type'],
                        'value' => (string)$rightsStatement
                    );
                }
                foreach($right->licence as $licence){
                    $rights[] = Array(
                        'rights_type' => 'licence',
                        'uri'=>(string)$licence['rightsUri'],
                        'type' => (string)$licence['type'],
                        'value' => (string)$licence
                    );
                }
            }
        }
        return $rights;
    }

    /**
    * Download Direct Access handler
    * @author Liz Woods <liz.woods@ands.org.au>
    * @return array
    */
    private function download_handler()
    {
        $download = array();
        if ($this->xml && $this->xml->{$this->ro->class}->location && $this->xml->{$this->ro->class}->location->address) {
            foreach($this->xml->{$this->ro->class}->location->address->electronic as $directaccess){
                if($directaccess['type']=='url'&& $directaccess['target']=='directDownload'){
                    $download[] = Array(
                        'contact_type' => 'url',
                        'contact_value' => (string)$directaccess->value,
                        'title'=>(string)$directaccess->title,
                    );
                }
            }
        }
        return $download;
    }

    /**
    * Contacts handler
    * @author Liz Woods <liz.woods@ands.org.au>
    * @return array
    */
    private function contact_handler()
    {
        $contacts = array();
        if ($this->xml && $this->xml->{$this->ro->class}->location && $this->xml->{$this->ro->class}->location->address) {
            foreach($this->xml->{$this->ro->class}->location->address->electronic as $contact) {
                if($contact['type']=='url'){
                    $contacts[] = Array(
                        'contact_type' => 'url',
                        'contact_value' => (string)$contact
                    );
                }
            }
            foreach($this->xml->{$this->ro->class}->location->address->physical as $contact){
                if($contact['type']=='physical'){
                    $contacts[] = Array(
                        'contact_type' => 'telephoneNumber',
                        'contact_value' => (string)$contact
                    );

                }
                if($contact->addressPart['type']=='telephoneNumber'){
                    $contacts[] = Array(
                        'contact_type' => 'telephoneNumber',
                        'contact_value' => (string)$contact
                    );

                }
                if($contact->addressPart['type']=='faxNumber'){
                    $contacts[] = Array(
                        'contact_type' => 'faxNumber',
                        'contact_value' => (string)$contact
                    );

                }
            }
        }
        return $contacts;
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
