<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Download Direct Access handler
* @author Liz Woods <liz.woods@ands.org.au>
* @return array
*/
class Directaccess extends ROHandler {
	function handle() {
		$download = array();
        if ($this->xml && $this->xml->{$this->ro->class}->location && $this->xml->{$this->ro->class}->location->address) {
            foreach($this->xml->{$this->ro->class}->location as $location){
                $directaccess=$location->address->electronic;
                if($directaccess && $directaccess['type']=='url'&& ($directaccess['target']=='directDownload' || $directaccess['target']=='landingPage')){
                    if((string)$directaccess->title=='')$directaccess->title=(string)$directaccess->value;
                    $download[] = Array(
                        'access_type' => (string)$directaccess['target'],
                        'contact_type' => 'url',
                        'access_value' => (string)$directaccess->value,
                        'title'=>(string)$directaccess->title,
                        'mediaType'=>(string)$directaccess->mediaType,
                        'byteSize'=>(string)$directaccess->byteSize,
                        'notes'=>(string)$directaccess->notes
                    );
                }
            }
            foreach($this->xml->{$this->ro->class}->relatedInfo as $relatedInfo){
                $type = (string) $relatedInfo['type'];
                $identifier_resolved = identifierResolution((string) $relatedInfo->identifier, (string) $relatedInfo->identifier['type']);
                if($type == 'service'&& $identifier_resolved!=''){
                    $download[] = Array(
                        'access_type' => 'viaService',
                        'contact_type' => 'url',
                        'access_value' => $identifier_resolved,
                        'title'=> (string) $relatedInfo->title,
                        'mediaType'=>'',
                        'byteSize'=>'',
                        'notes' => (string) $relatedInfo->notes
                    );
                }
            }

        }
        $relationshipTypeArray = ['isPresentedBy','supports'];
        $classArray = ['service'];
        $services = $this->ro->getRelatedObjectsByClassAndRelationshipType($classArray ,$relationshipTypeArray);
        foreach($services as $service){
            if($service['relation_url']!='' && $service['status']==PUBLISHED){
                if($service['relation_description']=='')$service['relation_description']=$service['relation_url'];
                $download[] = Array(
                    'access_type' => 'viaService',
                    'contact_type' => 'url',
                    'access_value' => $service['relation_url'],
                    'title'=> (string) $service['relation_description'],
                    'mediaType'=>'',
                    'byteSize'=>'',
                    'notes' =>'Visit Service'
                );
            }


        }
        foreach($this->xml->{$this->ro->class}->location as $location){
          $directaccess=$location->address->electronic;
              if($directaccess && $directaccess['type']=='url' && $directaccess['target']!='directDownload' && $directaccess['target']!='landingPage'){
                     if((string)$directaccess->title==''||!$directaccess->title){$directaccess->title=(string)$directaccess->value;}
                     $download[] = Array(
                          'access_type' => 'url',
                          'contact_type' => 'url',
                          'access_value' => (string)$directaccess->value,
                          'title'=>(string)$directaccess->title,
                          'mediaType'=>(string)$directaccess->mediaType,
                          'byteSize'=>(string)$directaccess->byteSize,
                          'notes' =>''
                     );
                }
          }
        return $download;
	}
}