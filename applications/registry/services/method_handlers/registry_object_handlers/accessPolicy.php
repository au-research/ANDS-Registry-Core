<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Access Policy handler
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @return array
 */
class AccessPolicy extends ROHandler {
    function handle() {
        $result = array();
        if($this->xml->registryObject->{$this->index['class']}->accessPolicy){
            foreach($this->xml->registryObject->{$this->index['class']} as $policy){
                $result[] = (string) $policy;
            }
        }
        return $result;
    }
}