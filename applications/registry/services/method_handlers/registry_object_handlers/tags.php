<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
 * Tags handler
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @return array list of tags
 */
class Tags extends ROHandler {
	function handle() {
        $ci = &get_instance();
        $db = $ci->load->database('registry', true);
        $tags = array();
        $results = $db->select('tag, type')->from('registry_object_tags')->where('key', $this->ro_key)->order_by('tag','ASC')->get();
        if($results && $results->num_rows() > 0) $results = $results->result_array();
        if(is_array($results) && sizeof($results)>0){
            foreach($results as $r){
                if($r['type'] != 'secret'){
                    array_push($tags, array(
                        'name' => $r['tag'],
                        'type' => $r['type']
                    ));
                }
            }
        }
        return $tags;
	}
}