<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Related Info handler
 * @author Liz Woods <liz.woods@ands.org.au>
 * @param  string type
 * @return array
 */
class relatedInfo extends ROHandler {
	function handle() {
        $result = array();
        if ($this->xml) {
            foreach($this->xml->registryObject->{$this->ro_class}->relatedInfo as $relatedInfo){
                $type = (string) $relatedInfo['type'];

                $identifier = array();

                if($relatedInfo->identifier!=''){
                    $identifier_resolved = identifierResolution((string) $relatedInfo->identifier, (string) $relatedInfo->identifier['type']);
                    $identifier['identifier_type']=(string) $relatedInfo->identifier['type'];
                    $identifier['identifier_value']=(string) $relatedInfo->identifier;
                    $identifier['identifier_href']=$identifier_resolved;
                }
                if($relatedInfo->title!='' || $relatedInfo->identifier!='' || $relatedInfo->notes!='' ||(string) $relatedInfo->relation->description!='' || (string) $relatedInfo->relation->url!=''){
                $result[] = array(
                    'type' => $type,
                    'title' =>  (string) $relatedInfo->title,
                    'identifier' => $identifier,
                    'relation' =>Array('relation_type'=>(string) $relatedInfo->relation['type'],'description'=>(string) $relatedInfo->relation->description,'url'=>(string) $relatedInfo->relation->url),
                    'notes' => (string) $relatedInfo->notes
                     );
                }
            }
        }
        return $result;
	}
}


