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

        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='fullName']");

        foreach($physical_contact as $contact){

            $contacts[] =Array(
                'contact_type' => 'fullName',
                'contact_value' => $contact->nodeValue
            );
        }

        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='organizationName']");

        foreach($physical_contact as $contact){

            $contacts[] =Array(
                'contact_type' => 'organizationName',
                'contact_value' => $contact->nodeValue
            );
        }

        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='buildingOrPropertyName']");

        foreach($physical_contact as $contact){

            $contacts[] =Array(
                'contact_type' => 'buildingOrPropertyName',
                'contact_value' => $contact->nodeValue
            );
        }

        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='flatOrUnitNumber']");
        $contact_value ='';
        foreach($physical_contact as $contact){

                $contact_value = $contact->nodeValue;
        }
        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='floorOrLevelNumber']");
        foreach($physical_contact as $contact){

            $contact_value .= " ".$contact->nodeValue;
        }
        if($contact_value!=''){
            $contacts[] =Array(
                'contact_type' => 'flatOrUnitNumber floorOrLevelNumber',
                'contact_value' => $contact_value
            );
        }



        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='LotNumber']");
        $contact_value ='';
        foreach($physical_contact as $contact){

            $contact_value = $contact->nodeValue;
        }
        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='houseNumber']");
        foreach($physical_contact as $contact){

            $contact_value .= " ".$contact->nodeValue;
        }
        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='streetName']");
        foreach($physical_contact as $contact){

            $contact_value .= " ".$contact->nodeValue;
        }
        if($contact_value!=''){
            $contacts[] =Array(
                'contact_type' => 'LotNumber houseNumber streetName',
                'contact_value' => $contact_value
            );
        }


        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='postalDeliveryNumberPrefix']");
        $contact_value ='';
        foreach($physical_contact as $contact){

            $contact_value = $contact->nodeValue;
        }
        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='postalDeliveryNumberValue']");
        foreach($physical_contact as $contact){

            $contact_value .= " ".$contact->nodeValue;
        }
        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='postalDeliveryNumberSuffix']");
        foreach($physical_contact as $contact){

            $contact_value .= " ".$contact->nodeValue;
        }
        if($contact_value!=''){
            $contacts[] =Array(
                'contact_type' => 'postalDeliveryNumberPrefix postalDeliveryNumberValue postalDeliveryNumberSuffix',
                'contact_value' => $contact_value
            );
        }



        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='addressLine']");

        foreach($physical_contact as $contact){

            $contacts[] =Array(
                'contact_type' => 'addressLine',
                'contact_value' => $contact->nodeValue
            );
        }


        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='suburbOrPlaceOrLocality']");
        $contact_value ='';
        foreach($physical_contact as $contact){

            $contact_value = $contact->nodeValue;
        }
        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='stateOrTerritory']");
        foreach($physical_contact as $contact){

            $contact_value .= " ".$contact->nodeValue;
        }
        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='postCode']");
        foreach($physical_contact as $contact){

            $contact_value .= " ".$contact->nodeValue;
        }
        if($contact_value!=''){
            $contacts[] =Array(
                'contact_type' => 'suburbOrPlaceOrLocality stateOrTerritory postCode',
                'contact_value' => $contact_value
            );
        }



        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='country']");
        foreach($physical_contact as $contact){

            $contacts[] =Array(
                'contact_type' => 'country',
                'contact_value' => $contact->nodeValue
            );
        }
        

        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='locationDescriptor']");
        $contact_value ='';
        foreach($physical_contact as $contact){

            $contact_value = $contact->nodeValue;
        }
        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='deliveryPointIdentifier']");
        foreach($physical_contact as $contact){

            $contact_value .= " ".$contact->nodeValue;
        }
        if($contact_value!=''){
            $contacts[] =Array(
                'contact_type' => 'locationDescriptor deliveryPointIdentifier',
                'contact_value' => $contact_value
            );
        }





        $physical_contact = $this->gXPath->query("//ro:location/ro:address/ro:physical/ro:addressPart[@type='text']");
        foreach($physical_contact as $contact){

            $contacts[] =Array(
                'contact_type' => 'text',
                'contact_value' => $contact->nodeValue
            );
        }

        return $contacts;
	}
}