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

        if($this->xml->{$this->ro->class}->relatedInfo){
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

        if ($this->xml && $this->xml->{$this->ro->class}->location->address->electronic) {
            if($this->gXPath->evaluate("count(//ro:location/ro:address/ro:electronic[@type='url'])")>0) {
                $query = "//ro:location/ro:address/ro:electronic[@type='url']";
            }

            if($query!=''){
                $locations = $this->gXPath->query($query);

                foreach($locations as $directaccess) {
                    $target = $directaccess->getAttribute('target');
                    if($target!='directDownload' && $target!='landingPage') $target='url';
                    $title='';
                    foreach($directaccess->getElementsByTagName('title') as $title){
                        $title = $title->nodeValue;
                    }
                    if($title=='') $title = $directaccess->nodeValue;
                    $mediaType = '';
                    foreach($directaccess->getElementsByTagName('mediaType') as $mediaType){
                        $mediaType = $mediaType->nodeValue;
                    }
                    $byteSize = '';
                    foreach($directaccess->getElementsByTagName('byteSize') as $byteSize){
                          $byteSize = $byteSize->nodeValue;
                    }
                    $notes = '';
                    foreach($directaccess->getElementsByTagName('notes') as $notes){
                           $notes = $notes->nodeValue;
                    }
                     $download[] = Array(
                            'access_type' => $target,
                            'contact_type' => 'url',
                            'access_value' => $directaccess->nodeValue,
                            'title'=>$title ,
                            'mediaType'=>$mediaType,
                            'byteSize'=>$byteSize,
                            'notes'=>$notes
                    );

               }
            }

        }

        return $download;
	}
}