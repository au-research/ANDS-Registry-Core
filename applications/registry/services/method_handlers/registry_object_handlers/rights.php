<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Rights handler
* @author Liz Woods <liz.woods@ands.org.au>
* @return array
*/
class Rights extends ROHandler {
	function handle() {
        $rights = $this->ro->processLicence();
        return $rights;
	}
}