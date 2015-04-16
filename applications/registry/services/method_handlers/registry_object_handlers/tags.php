<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
 * Tags handler
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @return array list of tags
 */
class Tags extends ROHandler {
	function handle() {
		$result = array();
        $result = $this->ro->getTags($pubLicOnly = true);
        return $result;
	}
}