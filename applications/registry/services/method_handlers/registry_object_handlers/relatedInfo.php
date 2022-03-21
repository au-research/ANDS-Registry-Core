<?php 
use ANDS\Registry\Providers\ORCID\ORCIDRecordsRepository;
 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Related Info handler
 * @author Liz Woods <liz.woods@ands.org.au>
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @param  string type
 * @return array
 */
class relatedInfo extends ROHandler {
	function handle() {
        $result = array();
        if ($this->xml) {
            foreach($this->xml->{$this->ro->class}->relatedInfo as $relatedInfo){
                $type = (string) $relatedInfo['type'];

                $identifier = array();

                if($relatedInfo->identifier!=''){
                    $identifier_resolved = identifierResolution((string) $relatedInfo->identifier, (string) $relatedInfo->identifier['type']);
                    $identifier['identifier_type']=(string) $relatedInfo->identifier['type'];
                    $identifier['identifier_value']=(string) $relatedInfo->identifier;
                    $identifier['identifier_href']=$identifier_resolved;
                }
                if($relatedInfo->title!='' || $relatedInfo->identifier!='' || $relatedInfo->notes!='' ||(string) $relatedInfo->relation->description!='' || (string) $relatedInfo->relation->url!=''){

                    // CC-2049. Attempt to resolve title for display for ORCID
                    $title = (string) $relatedInfo->title;
                    $isOrcidIdentifier = array_key_exists('identifier_type', $identifier) && $identifier['identifier_type'] == 'orcid';
                    if ($title == "" && $type == "party" && $isOrcidIdentifier) {
                        if ($orcid = ORCIDRecordsRepository::obtain($identifier['identifier_value'])) {
                            $title = $orcid->full_name;
                        }
                    }

                    $result[] = array(
                        'type' => $type,
                        'title' =>  $title,
                        'identifier' => $identifier,
                        'relation' => [
                            'relation_type'=>(string) $relatedInfo->relation['type'],
                            'description'=>(string) $relatedInfo->relation->description,
                            'url'=>(string) $relatedInfo->relation->url
                        ],
                        'notes' => (string) $relatedInfo->notes
                     );
                }
            }
        }
        return $result;
	}
}


