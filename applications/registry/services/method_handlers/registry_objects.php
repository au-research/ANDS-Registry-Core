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
        'get', 'core', 'relationships', 'identifiers','descriptions', 'registry', 'subjects', 'spatial', 'temporal', 'citations'
    );

    private $ro = null;
    private $index = null;
    
    //var $params, $options, $formatter; 
    function handle($params=''){
        $this->params = $params;

        //registry_objects/<id>/method1/method2
        $id = isset($params[1]) ? $params[1] : false;
        $method1 = isset($params[2]) ? $params[2]: 'get';
        $method2 = isset($params[3]) ? $params[3]: false;

        $ci =& get_instance();

        $result = array();
        if ($id){
            $ci->load->model('registry_object/registry_objects', 'ro');
            $this->ro = new _registry_object($id);
            $this->populate_index($id);
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
                        case 'spatial' :        $result[$m1] = $this->spatial_handler(); break;
                        case 'temporal' :       $result[$m1] = $this->temporal_handler(); break;
                        case 'citations' :      $result[$m1] = $this->citations_handler(); break;
                    }
                }
            }
        } else {
            $result = $this->searcher($params);
        }
        return $this->formatter->display($result);
    }

    private function populate_index($id) {
        $ci =& get_instance();
        $ci->load->library('solr');
        $ci->solr->setOpt('fq', '+id:'.$id);
        $result = $ci->solr->executeSearch(true);
        
        if(sizeof($result['response']['docs']) == 1) {
            $this->index = $result['response']['docs'][0];
        }
    }

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

    private function identifiers_handler() {
        $result = array();
        if($this->index) {
            //identifier_type, identifier_value
            foreach($this->index['identifier_type'] as $key=>$sub) {
                $result[] = array(
                    'type' => $sub,
                    'value' => $this->index['identifier_value'][$key],
                );
            }
        }
        return $result;
    }

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

    private function temporal_handler() {
        // var_dump($this->index);
        $result = array();
        if($this->index) {
            //date_from, date_to, earliest_year, latest_year
            if(isset($this->index['date_from'])){
                foreach($this->index['date_from'] as $sub) {
                    $result['date_from'][] = $sub;
                }
            }
            if(isset($this->index['date_to'])){
                foreach($this->index['date_to'] as $sub) {
                    $result['date_to'][] = $sub;
                }
            }
            if(isset($this->index['earliest_year'])) $result['earliest_year'] = $this->index['earliest_year'];
            if(isset($this->index['latest_year'])) $result['latest_year'] = $this->index['latest_year'];
        }
        return $result;
    }

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

    private function descriptions_handler() {
        $result = array();
        $xml = $this->ro->getSimpleXML();
        $xml = addXMLDeclarationUTF8(($xml->registryObject ? $xml->registryObject->asXML() : $xml->asXML()));
        $xml = simplexml_load_string($xml);
        $xml = simplexml_load_string( addXMLDeclarationUTF8($xml->asXML()) );
        foreach($xml->{$this->ro->class}->description as $description){
            $type = (string) $description['type'];
            $description_str = html_entity_decode((string) $description);
            $result[] = array(
                'type' => $type,
                'description' => $description_str
            );
        }
        return $result;
    }

    private function citations_handler() {
        $result = array();
        $xml = $this->ro->getSimpleXML();
        $xml = addXMLDeclarationUTF8(($xml->registryObject ? $xml->registryObject->asXML() : $xml->asXML()));
        $xml = simplexml_load_string($xml);
        $xml = simplexml_load_string( addXMLDeclarationUTF8($xml->asXML()) );
        foreach($xml->{$this->ro->class}->citationInfo as $citation){
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
                     'contributors' => $displayNames
                 );

             }
            foreach($citation->fullCitation as $fullCitation){
                $result[] = array(
                    'type'=> 'fullCitation',
                    'value' => (string)$fullCitation,
                    'citation_type' => (string)$fullCitation['style']
                );

            }
        }
        return $result;
    }
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
            return $identifier_href;
            break;
        case 'ark':
            break;
        case 'AU-ANL:PEAU':
            break;
        case 'handle':
            break;
        case 'purl':
            break;
        case 'uri':
            break;
        case 'urn':
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