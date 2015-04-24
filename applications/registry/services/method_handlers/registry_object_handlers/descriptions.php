<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Descriptions handler
* As an example on how to get data from multiple source, index and xml
* @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
* @return array list of description with types
*/
class Descriptions extends ROHandler {
	function handle() {
		$result = array();
        if ($this->xml) {
            foreach($this->xml->{$this->ro->class}->description as $description){
                $type = (string) $description['type'];
                if($type!='rights' && $type!='accessRights'){
                    $description_str = html_entity_decode((string) $description);
                    if($type=='fundingScheme'||$type=='fundingAmount'){
                        $description_str = strip_tags($description_str);
                    }
                    $result[] = array(
                        'type' => $type,
                        'description' => $description_str
                    );
                }
            }
        }
        return $result;
	}
}