<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* CORE handler
* Returns core registry object attribute
* @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
* @return array
*/
use ANDS\Registry\Providers\RIFCS\AccessProvider;
use ANDS\RegistryObject;
class Core extends ROHandler {
	function handle() {
		$result = array();
        $record = RegistryObject::find($this->ro->id);
        $fl = isset($this->params['fl']) ? explode(',',$this->params['fl']) : explode(',',$this->default_params['fl']);
        foreach($fl as $f) {
            $attr = $this->ro->{$f};
            if(!$attr) $attr = $this->ro->getAttribute($f);
            if(!$attr) $attr = null;
            $result[$f] = $attr;
        }

        // CC-2112. Alt title ordering
        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($this->ro->id);
        $titles = \ANDS\Registry\Providers\RIFCS\TitleProvider::get($record);
        if (array_key_exists('alt_titles', $titles)) {
            $result['alt_title'] = $titles['alt_titles'];
        }


        $result['site_name'] = "Research Data Australia";
        $result['description'] = isset($this->index['list_description']) ? $this->index['list_description'] : 'No description text available.';
        if(isset($this->index['theme_page'])) {
            $result['theme_page'] = array();
            if(is_array($this->index['theme_page'])){
                foreach($this->index['theme_page'] as $theTheme){
                    $result['theme_page'][]=$theTheme;
                }
            }else{
                $result['theme_page'][] = $this->index['theme_page'];
            }

        }

        if($this->ro->class == 'activity' && $this->ro->type == 'grant' && strrpos($this->ro->key, 'purl') > 0) {
            $result['url'] = $this->ro->key;

            // get Landing Page for Activities -- TODO determine if we actually use this value ?
            $access = AccessProvider::getLandingPage($record,$record->getCurrentData()->data);
            if(isset($access[0]->url)) $result['landingPage'] = $access[0]->url;

             /**
             * Check if list_description exists in the index
             * @todo default description?
             */
            if (isset($this->index['list_description'])) {
                $result['description'] = "Identifier: " .$this->ro->key.NL.$this->index['list_description'];
            }
        }
        $this->ro->save();
        return $result;
	}
}