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
        
        $alt_title = array();
        if($this->xml) {
            foreach($this->xml->{$this->ro->class}->name as $name) {
                $type = (string) $name['type'];
                if (($type=='abbreviated' || $type=='alternative') && $name->namePart) {
                    $alt_title[] = (string) $name->namePart;
                }
            }
        }
        if(!empty($alt_title)) $result['alt_title'] = $alt_title;

        $result['site_name'] = "Research Data Australia";
        $result['description'] = isset($this->index['list_description']) ? $this->index['list_description'] : 'No description text available.';

        if($this->ro->class == 'activity' && $this->ro->type == 'grant' && strrpos($this->ro->key, 'purl') > 0) {
            $result['url'] = $this->ro->key;
            $result['description'] = "Identifier: " .$this->ro->key.NL.$this->index['list_description'];
        }
        return $result;
	}
}