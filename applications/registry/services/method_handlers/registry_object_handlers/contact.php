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


        $electronic_contact = $this->gXPath->query("//ro:location/ro:address/ro:electronic[@type='email']");

        foreach($electronic_contact as $contact){


          $contacts[] =Array(
                'contact_type' => 'email',
                'contact_value' => $contact->nodeValue
            );
        }

        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='telephoneNumber']");

        foreach($physical_contact as $contact){

            $contacts[] =Array(
                'contact_type' => 'telephoneNumber',
                'contact_value' => $contact->nodeValue
            );
        }
        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='faxNumber']");

        foreach($physical_contact as $contact){

            $contacts[] =Array(
                'contact_type' => 'faxNumber',
                'contact_value' => $contact->nodeValue
            );
        }
        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='addressLine']");

        foreach($physical_contact as $contact){

            $contacts[] =Array(
                'contact_type' => 'addressLine',
                'contact_value' => $contact->nodeValue
            );
        }

        return $contacts;
	}
}