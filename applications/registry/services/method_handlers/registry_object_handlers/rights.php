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
        

        //if there's a secret tag of SECRET_TAG_ACCESS_OPEN (defined in constants), add a right of accessRights_type open
        if (!$skip) {
            if ($this->ro->hasTag(SECRET_TAG_ACCESS_OPEN)) {
                $rights[] = array(
                    'value' => '',
                    'type' => 'accessRights',
                    'accessRights_type' =>'open'
                );
            } elseif ($this->ro->hasTag(SECRET_TAG_ACCESS_CONDITIONAL)) {
                $rights[] = array(
                    'value' => '',
                    'type' => 'accessRights',
                    'accessRights_type' =>'conditional'
                );
            } elseif ($this->ro->hasTag(SECRET_TAG_ACCESS_RESTRICTED)) {
                $rights[] = array(
                    'value' => '',
                    'type' => 'accessRights',
                    'accessRights_type' =>'restricted'
                );
            }
        }
        
        return $rights;
	}
}