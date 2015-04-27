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
        $query ='';
        $relationshipTypeArray = ['isPresentedBy','supports'];
        $classArray = ['service'];
        $services = $this->ro->getRelatedObjectsByClassAndRelationshipType($classArray ,$relationshipTypeArray);

        foreach($services as $service){

            if($service['relation_url']!='' && $service['status']==PUBLISHED){
                if($service['relation_description']=='')$service['relation_description']=$service['title'];
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
                if($type == 'service' && in_array($relatedInfo->relation['type'], $relationshipTypeArray) && $relatedInfo->relation->url != ''){
                    $download[] = Array(
                        'access_type' => 'viaService',
                        'contact_type' => 'url',
                        'access_value' => (string) $relatedInfo->relation->url,
                        'title'=> (string) $relatedInfo->title,
                        'mediaType'=>'',
                        'byteSize'=>'',
                        'notes' => (string) $relatedInfo->relation->description
                    );
                }
            }
        }

        if ($this->xml && $this->gXPath->evaluate("count(//ro:location/ro:address/ro:electronic)") > 0) {
            if($this->gXPath->evaluate("count(//ro:location/ro:address/ro:electronic[@type='url'])")>0) {
                $query = "//ro:location/ro:address/ro:electronic[@type='url']";
            }

            if($query!=''){
                $locations = $this->gXPath->query($query);

                foreach($locations as $directaccess) {
                    $target = $directaccess->getAttribute('target');
                    $url_link = '';
                    foreach($directaccess->getElementsByTagName('value') as $url){
                        $url_link = trim($url->nodeValue);
                    }
                    if(strpos($url_link, 'http') === false)
                    {
                        $url_link = 'http://'.$url_link;
                    }
                    if($target!='directDownload' && $target!='landingPage') $target='url';
                    $title='';
                    foreach($directaccess->getElementsByTagName('title') as $title){
                        $title = $title->nodeValue;
                    }
                    if($title=='') $title = $directaccess->nodeValue;
                    $mediaTypeStr = '';
                    foreach($directaccess->getElementsByTagName('mediaType') as $mediaType){
                        $mediaTypeStr .= $mediaType->nodeValue.",";
                    }
                    $byteSizeStr = '';
                    foreach($directaccess->getElementsByTagName('byteSize') as $byteSize){
                        $byteSizeStr .= $byteSize->nodeValue.",";
                    }
                    $notes = '';
                    foreach($directaccess->getElementsByTagName('notes') as $notes){
                           $notes = $notes->nodeValue;
                    }
                     $download[] = Array(
                            'access_type' => $target,
                            'contact_type' => 'url',
                            'access_value' => $url_link,
                            'title'=>$title ,
                            'mediaType'=>trim($mediaTypeStr,","),
                            'byteSize'=>trim($byteSizeStr,","),
                            'notes'=>$notes
                    );

               }
            }

        }

        return $download;
	}
}