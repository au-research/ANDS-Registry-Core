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

        if(!$rights) $rights = array();

        $skip = false;
        if($rights && sizeof($rights) > 0) {
        	foreach($rights as $right) {
        		if($right['type']=='accessRights') $skip = true;
        	}
        }
        

        //if there's a secret tag of SYSTEM_open, assign license_class to open
        $tags = $this->ro->getTags();
        if($tags && !$skip){
			foreach($tags as $tag){
				if ($tag['name']=='SYSTEM_open') {
					$rights[] = array(
						'value' => '',
						'type' => 'accessRights',
						'accessRights_type' =>'open'
					);
				}
			}
		}
        
        return $rights;
	}
}