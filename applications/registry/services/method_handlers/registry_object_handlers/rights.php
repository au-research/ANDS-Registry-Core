<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Rights handler
* @author Liz Woods <liz.woods@ands.org.au>
* @return array
*/
class Rights extends ROHandler {
	function handle() {
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
}