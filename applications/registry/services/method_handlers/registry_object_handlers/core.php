<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* CORE handler
* Returns core registry object attribute
* @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
* @return array
*/
class Core extends ROHandler {
	function handle() {
		$result = array();
        $fl = isset($this->params['fl']) ? explode(',',$this->params['fl']) : explode(',',$this->default_params['fl']);
        foreach($fl as $f) {
            $attr = $this->ro->{$f};
            if(!$attr) $attr = $this->ro->getAttribute($f);
            if(!$attr) $attr = null;
            $result[$f] = $attr;
        }
        return $result;
	}
}