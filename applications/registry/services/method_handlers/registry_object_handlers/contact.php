<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Contacts handler
* @author Liz Woods <liz.woods@ands.org.au>
* @return array
*/
class Contact extends ROHandler {
	function handle() {
		$contacts = array();
        if ($this->xml && $this->xml->{$this->ro->class}->location && $this->xml->{$this->ro->class}->location->address) {
            foreach($this->xml->{$this->ro->class}->location->address->electronic as $contact) {
                if($contact['type']=='url'){
                    $contacts[] = Array(
                        'contact_type' => 'url',
                        'contact_value' => (string)$contact
                    );
                }
            }
            foreach($this->xml->{$this->ro->class}->location->address->physical as $contact){
                if($contact['type']=='physical'){
                    $contacts[] = Array(
                        'contact_type' => 'telephoneNumber',
                        'contact_value' => (string)$contact
                    );

                }
                if($contact->addressPart['type']=='telephoneNumber'){
                    $contacts[] = Array(
                        'contact_type' => 'telephoneNumber',
                        'contact_value' => (string)$contact
                    );

                }
                if($contact->addressPart['type']=='faxNumber'){
                    $contacts[] = Array(
                        'contact_type' => 'faxNumber',
                        'contact_value' => (string)$contact
                    );

                }
            }
        }
        return $contacts;
	}
}