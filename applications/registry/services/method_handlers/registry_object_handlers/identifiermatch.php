<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Identifier matching handler
* @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
* @return array
*/
class Identifiermatch extends ROHandler {
	function handle() {
		$result = array();
        
        $matching = $this->ro->findMatchingRecords();

        $ci =& get_instance();
        $ci->load->model('registry_object/registry_objects', 'ro');
        foreach($matching as $m) {
            $ro = $ci->ro->getByID($m);
            if($ro){
                $result[] = array(
                    'registry_object_id' => $ro->id,
                    'slug' => $ro->slug,
                    'title' => $ro->title,
                    'group' => $ro->group
                );
            }
            unset($ro);
        }

        return $result;
	}
}