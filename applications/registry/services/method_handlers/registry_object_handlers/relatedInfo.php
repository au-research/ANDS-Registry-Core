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
            foreach($this->xml->{$this->ro->class}->relatedInfo as $relatedInfo){
                $type = (string) $relatedInfo['type'];
                $identifier_resolved = identifierResolution((string) $relatedInfo->identifier, (string) $relatedInfo->identifier['type']);
                $result[] = array(
                    'type' => $type,
                    'title' =>  (string) $relatedInfo->title,
                    'identifier' => Array('identifier_type'=>(string) $relatedInfo->identifier['type'],'identifier_value'=>(string) $relatedInfo->identifier,'identifier_href'=>$identifier_resolved),
                    'relation' =>Array('relation_type'=>(string) $relatedInfo->relation['type'],'description'=>(string) $relatedInfo->relation->description,'url'=>(string) $relatedInfo->relation->url),
                    'notes' => (string) $relatedInfo->notes
                );
            }
        }
        return $result;
	}
}


