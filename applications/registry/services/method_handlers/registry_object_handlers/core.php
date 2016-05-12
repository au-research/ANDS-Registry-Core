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
        //$result['version_id'] = $this->ro_version_id;

        if(isset($this->index['status']) && $this->index['status'] == 'PUBLISHED')
        {
            foreach($fl as $f) {
                if(isset($this->index[$f]))
                $result[$f] = $this->index[$f];
            }
            if(isset($this->index['class']))
                $this->ro_class = $this->index['class'];
            if(isset($this->index['type']))
                $this->ro_type = $this->index['type'];
            if(isset($this->index['alt_title']))
             $result['alt_title'] = $this->index['alt_title'];


            $result['description'] = isset($this->index['list_description']) ? $this->index['list_description'] : 'No description text available.';
            if(isset($this->index['theme_page'])) {
                if(is_array($this->index['theme_page'])){
                    foreach($this->index['theme_page'] as $theTheme){
                        $result['theme_page']=$theTheme;
                    }
                }else{
                    $result['theme_page'] = $this->index['theme_page'];
                }

            }
        }else{ // no index for record can be draft or not indexed
            foreach($fl as $f) {
                if(isset($this->ro_record[$f]))
                    $result[$f] = $this->ro_record[$f];
                elseif(isset($this->ro[$f]))
                    $result[$f] = $this->ro[$f];

            }
            $result['group'] = (string)$this->xml->registryObject['group'];
            $this->ro_class = $this->ro['class'];
            $this->ro_type = $this->ro['type'];
        }
        $result['version_id'] = $this->ro_record['id'];
        $result['id'] = $this->ro_record['registry_object_id'];
        $result['site_name'] = "Research Data Australia";

        if($this->ro_class == 'activity' && $this->ro_type == 'grant' && strrpos($this->ro_key, 'purl') > 0) {
            $result['url'] = $this->ro_key;

            /**
             * Check if list_description exists in the index
             * @todo default description?
             */
            if (isset($this->index['list_description'])) {
                $result['description'] = "Identifier: " .$this->ro->key.NL.$this->index['list_description'];
            }
        }
        return $result;
	}
}