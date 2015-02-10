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
            foreach($this->xml->{$this->ro->class}->location->address->electronic as $directaccess){
                if($directaccess['type']=='url'&& $directaccess['target']=='directDownload'){
                    if((string)$directaccess->title=='')$directaccess->title=(string)$directaccess->value;
                    $download[] = Array(
                        'access_type' => 'direct',
                        'contact_type' => 'url',
                        'contact_value' => (string)$directaccess->value,
                        'title'=>(string)$directaccess->title,
                        'mediaType'=>(string)$directaccess->mediaType,
                        'byteSize'=>(string)$directaccess->byteSize,
                    );
                }
            }
            if(!$download){
                foreach($this->xml->{$this->ro->class}->location->address->electronic as $directaccess){
                    if($directaccess['type']=='url'&& $directaccess['target']=='landingPage'){
                        if((string)$directaccess->title=='')$directaccess->title=(string)$directaccess->value;
                        $download[] = Array(
                            'access_type' => 'url',
                            'contact_type' => 'url',
                            'contact_value' => (string)$directaccess->value,
                            'title'=>(string)$directaccess->title,
                            'mediaType'=>(string)$directaccess->mediaType,
                            'byteSize'=>(string)$directaccess->byteSize,
                        );
                    }
                }
            }
            if(!$download){
                foreach($this->xml->{$this->ro->class}->location->address->electronic as $directaccess){
                    if($directaccess['type']=='url'&& $directaccess['target']!='directDownload' && $directaccess['target']!='landingPage'){
                        if((string)$directaccess->title=='')$directaccess->title=(string)$directaccess->value;
                        $download[] = Array(
                            'access_type' => 'url',
                            'contact_type' => 'url',
                            'contact_value' => (string)$directaccess->value,
                            'title'=>(string)$directaccess->title,
                            'mediaType'=>(string)$directaccess->mediaType,
                            'byteSize'=>(string)$directaccess->byteSize,
                        );
                    }
                }

            }
        }
        return $download;
	}
}