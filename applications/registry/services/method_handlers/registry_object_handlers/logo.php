<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* CORE handler
* Returns core registry object attribute
* @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
* @return array
*/
class Logo extends ROHandler {
	function handle() {
		$result = array();

        $logo = false;
        //get logo from description type logo
        if ($this->xml) {
            foreach ($this->xml->{$this->ro->class}->description as $description) {
                if (strtolower((string) $description['type'])=='logo') {
                    $logo = html_entity_decode((string) $description);
                }
            }
        } 
        if ($logo) {
            $logo = strip_tags($logo);
            $result[] = $logo;
        }
        return $result;
	}
}