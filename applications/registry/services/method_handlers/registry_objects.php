<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class Registry_objectsMethod extends MethodHandler {
    private $default_params = array(
        'q' => '*:*',
        'fl' => 'id,key,slug,title,class,data_source_id',
        'wt' => 'json',
        'indent' => 'on',
        'rows' => 20
    );

    private $valid_methods = array(
        'get', 'core', 'relationships', 'identifiers'
    );
    
    //var $params, $options, $formatter; 
    function handle($params=''){
        //registry_objects/<id>/method1/method2
        $id = isset($params[1]) ? $params[1] : false;
        $method1 = isset($params[2]) ? $params[2]: 'get';
        $method2 = isset($params[3]) ? $params[3]: false;
        // var_dump($id, $method1, $method2);
        
        $ci =& get_instance();
        
        //prepare the fields
        

        $result = array();
        if ($id){
            $ci->load->model('registry_object/registry_objects', 'ro');
            $ro = new _registry_object($id);
            $method1s = explode('-', $method1);
            foreach($method1s as $m1){
                if($m1 && in_array($m1, $this->valid_methods)) {
                    switch($m1) {
                        case 'get':
                        case 'core':
                             $result['registry_object'] = $this->core_handler($ro, $params); break;
                        case 'relationships' : $result[$m1] = $this->relationships_handler($ro, $params); break;
                        case 'identifiers' : throw new Exception('Method Not Implemented'); break;
                    }
                }

            } 
            // if($method1 && in_array($method1, $this->valid_methods)) {
            //     switch($method1) {
            //         case 'get': $result['registry_object'] = $this->core_handler($ro, $params); break;
            //         case 'relationships' : $result[$method1] = $this->relationships_handler($ro, $params); break;
            //         case 'identifiers' : throw new Exception('Method Not Implemented'); break;
            //     }
            // }
        } else {
            $result = $this->searcher($params);
        }

        return $this->formatter->display($result);
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

    private function core_handler($ro, $params) {
        $result = array();
        $fl = isset($this->params['fl']) ? explode(',',$this->params['fl']) : explode(',',$this->default_params['fl']);
        foreach($fl as $f) {
            $attr = $ro->{$f};
            if(!$attr) $attr = $ro->getAttribute($f);
            if(!$attr) $attr = null;
            $result[$f] = $attr;
        }
        return $result;
    }

    private function relationships_handler($ro, $params) {
        $result = array();
        $specific = isset($params[3]) ? $params[3]: null;
        if (isset($this->params['mode']) && $this->params['mode']=='unordered') {
            $relationships = $ro->getAllRelatedObjects(false, true, true);
        } else {
            $relationships = $ro->getConnections(true, $specific);
        }
        return $relationships;
    }
}