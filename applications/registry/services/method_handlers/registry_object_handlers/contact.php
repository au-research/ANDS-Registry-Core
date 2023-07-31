<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Contacts handler
 * TODO Refactor to ContactProvider
* @author Liz Woods <liz.woods@ardc.edu.au>
* @return array
*/
class Contact extends ROHandler {
	function handle() {
		$contacts = Array();

		// REFACTOR to contact provider
        /**
         * Should come in the form
         * $contact = [
         *  'email' => $email,
         *  'address' => $address
         *  ...
         * ]
         */

       $streetAddresses = $this->gXPath->query("//ro:location/ro:address/ro:physical[@type='streetAddress']");
       if($streetAddresses->length> 0) {

            array_push($contacts, ...$this->getAddress($streetAddresses, 'streetAddress'));
       }

      $postalAddresses = $this->gXPath->query("//ro:location/ro:address/ro:physical[@type='postalAddress']");
        if($postalAddresses->length > 0) {

            array_push($contacts, ...$this->getAddress($postalAddresses, 'postalAddress'));
        }

        $addresses = $this->gXPath->query("//ro:location/ro:address");

        foreach($addresses as $address){


            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='fullName']", $address);

            foreach($physical_contact as $contact){

                $contacts[] =Array(
                    'contact_type' => 'fullName',
                    'contact_value' => $contact->nodeValue
                );
            }

            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='organizationName']", $address);

            foreach($physical_contact as $contact){

                $contacts[] =Array(
                    'contact_type' => 'organizationName',
                    'contact_value' => $contact->nodeValue
                );
            }

            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='buildingOrPropertyName']", $address);

            foreach($physical_contact as $contact){

                $contacts[] =Array(
                    'contact_type' => 'buildingOrPropertyName',
                    'contact_value' => $contact->nodeValue
                );
            }

            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='flatOrUnitNumber']", $address);
            $contact_value ='';
            foreach($physical_contact as $contact){

                $contact_value = $contact->nodeValue;
            }
            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='floorOrLevelNumber']", $address);
            foreach($physical_contact as $contact){

                $contact_value .= " ".$contact->nodeValue;
            }
            if($contact_value!=''){
                $contacts[] =Array(
                    'contact_type' => 'flatOrUnitNumber floorOrLevelNumber',
                    'contact_value' => $contact_value
                );
            }
            
            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='LotNumber']", $address);
            $contact_value ='';
            foreach($physical_contact as $contact){

                $contact_value = $contact->nodeValue;
            }
            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='houseNumber']", $address);
            foreach($physical_contact as $contact){

                $contact_value .= " ".$contact->nodeValue;
            }
            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='streetName']", $address);
            foreach($physical_contact as $contact){

                $contact_value .= " ".$contact->nodeValue;
            }
            if($contact_value!=''){
                $contacts[] =Array(
                    'contact_type' => 'LotNumber houseNumber streetName',
                    'contact_value' => $contact_value
                );
            }

            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='postalDeliveryNumberPrefix']", $address);
            $contact_value ='';
            foreach($physical_contact as $contact){

                $contact_value = $contact->nodeValue;
            }
            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='postalDeliveryNumberValue']", $address);
            foreach($physical_contact as $contact){

                $contact_value .= " ".$contact->nodeValue;
            }
            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='postalDeliveryNumberSuffix']", $address);
            foreach($physical_contact as $contact){

                $contact_value .= " ".$contact->nodeValue;
            }
            if($contact_value!=''){
                $contacts[] =Array(
                    'contact_type' => 'postalDeliveryNumberPrefix postalDeliveryNumberValue postalDeliveryNumberSuffix',
                    'contact_value' => $contact_value
                );
            }

            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='addressLine']", $address);

            foreach($physical_contact as $contact){

                $contacts[] =Array(
                    'contact_type' => 'addressLine',
                    'contact_value' => $contact->nodeValue
                );
            }

            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='suburbOrPlaceOrLocality']", $address);
            $contact_value ='';
            foreach($physical_contact as $contact){

                $contact_value = $contact->nodeValue;
            }
            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='stateOrTerritory']", $address);
            foreach($physical_contact as $contact){

                $contact_value .= " ".$contact->nodeValue;
            }
            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='postCode']", $address);
            foreach($physical_contact as $contact){

                $contact_value .= " ".$contact->nodeValue;
            }
            if($contact_value!=''){
                $contacts[] =Array(
                    'contact_type' => 'suburbOrPlaceOrLocality stateOrTerritory postCode',
                    'contact_value' => $contact_value
                );
            }

            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='country']", $address);
            foreach($physical_contact as $contact){

                $contacts[] =Array(
                    'contact_type' => 'country',
                    'contact_value' => $contact->nodeValue
                );
            }

            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='locationDescriptor']", $address);
            $contact_value ='';
            foreach($physical_contact as $contact){

                $contact_value = $contact->nodeValue;
            }
            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='deliveryPointIdentifier']", $address);
            foreach($physical_contact as $contact){

                $contact_value .= " ".$contact->nodeValue;
            }
            if($contact_value!=''){
                $contacts[] =Array(
                    'contact_type' => 'locationDescriptor deliveryPointIdentifier',
                    'contact_value' => $contact_value
                );
            }

            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='text']", $address);
            foreach($physical_contact as $contact){

                $contacts[] =Array(
                    'contact_type' => 'text',
                    'contact_value' => $contact->nodeValue
                );
            }

            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='telephoneNumber']", $address);

            foreach($physical_contact as $contact){

                $contacts[] =Array(
                    'contact_type' => 'telephoneNumber',
                    'contact_value' => $contact->nodeValue
                );
            }
            $physical_contact = $this->gXPath->query("ro:physical[not(@type='streetAddress') and not(@type='postalAddress')]/ro:addressPart[@type='faxNumber']", $address);

            foreach($physical_contact as $contact){

                $contacts[] =Array(
                    'contact_type' => 'faxNumber',
                    'contact_value' => $contact->nodeValue
                );
            }

            $electronic_contact = $this->gXPath->query("ro:electronic", $address);
            foreach($electronic_contact as $contact){
                if(($contact->getAttribute("type") != "url" && $contact->getAttribute("type") != "uri" ) || $this->ro->class == 'party'){
                    // Collection urls are processed by the directaccess handler
                    $contacts[] =Array(
                        'contact_type' => "electronic_".$contact->getAttribute("type"),
                        'contact_value' => trim($contact->nodeValue)
                    );
                }
            }

            // Fix API for HTML rendering
            $contacts[] = [
                'contact_type' => 'end',
                'contact_value' => ''
            ];

            
        }


        return $contacts;
	}


    private function getAddress($addresses, $type){
        $contacts = Array();
       // $address_num = $addresses->length;
        $count = 0;

        foreach($addresses as $address){
            $count++;

            $physical_contact = $this->gXPath->query("ro:addressPart[@type='fullName']", $address);

            foreach($physical_contact as $contact){

                  $contacts[] =Array(
                      'contact_type' => $type.'_'.$count.'_fullName',
                      'contact_value' => $contact->nodeValue
                  );
              }

              $physical_contact = $this->gXPath->query("ro:addressPart[@type='organizationName']", $address);

              foreach($physical_contact as $contact){

                  $contacts[] =Array(
                      'contact_type' => $type.'_'.$count.'_organizationName',
                      'contact_value' => $contact->nodeValue
                  );
              }

              $physical_contact = $this->gXPath->query("ro:addressPart[@type='buildingOrPropertyName']", $address);

              foreach($physical_contact as $contact){

                  $contacts[] =Array(
                      'contact_type' => $type.'_'.$count.'_buildingOrPropertyName',
                      'contact_value' => $contact->nodeValue
                  );
              }

              $physical_contact = $this->gXPath->query("ro:addressPart[@type='flatOrUnitNumber']", $address);
              $contact_value ='';
              foreach($physical_contact as $contact){

                  $contact_value = $contact->nodeValue;
              }
              $physical_contact = $this->gXPath->query("ro:addressPart[@type='floorOrLevelNumber']", $address);
              foreach($physical_contact as $contact){

                  $contact_value .= " ".$contact->nodeValue;
              }
              if($contact_value!=''){
                  $contacts[] =Array(
                      'contact_type' => $type.'_'.$count.'_flatOrUnitNumber floorOrLevelNumber',
                      'contact_value' => $contact_value
                  );
              }

              $physical_contact = $this->gXPath->query("ro:addressPart[@type='LotNumber']", $address);
              $contact_value ='';
              foreach($physical_contact as $contact){

                  $contact_value = $contact->nodeValue;
              }
              $physical_contact = $this->gXPath->query("ro:addressPart[@type='houseNumber']", $address);
              foreach($physical_contact as $contact){

                  $contact_value .= " ".$contact->nodeValue;
              }
              $physical_contact = $this->gXPath->query("ro:addressPart[@type='streetName']", $address);
              foreach($physical_contact as $contact){

                  $contact_value .= " ".$contact->nodeValue;
              }
              if($contact_value!=''){
                  $contacts[] =Array(
                      'contact_type' => $type.'_'.$count.'_LotNumber houseNumber streetName',
                      'contact_value' => $contact_value
                  );
              }

              $physical_contact = $this->gXPath->query("ro:addressPart[@type='postalDeliveryNumberPrefix']", $address);
              $contact_value ='';
              foreach($physical_contact as $contact){

                  $contact_value = $contact->nodeValue;
              }
              $physical_contact = $this->gXPath->query("ro:addressPart[@type='postalDeliveryNumberValue']", $address);
              foreach($physical_contact as $contact){

                  $contact_value .= " ".$contact->nodeValue;
              }
              $physical_contact = $this->gXPath->query("ro:addressPart[@type='postalDeliveryNumberSuffix']", $address);
              foreach($physical_contact as $contact){

                  $contact_value .= " ".$contact->nodeValue;
              }
              if($contact_value!=''){
                  $contacts[] =Array(
                      'contact_type' => $type.'_'.$count.'_postalDeliveryNumberPrefix postalDeliveryNumberValue postalDeliveryNumberSuffix',
                      'contact_value' => $contact_value
                  );
              }

              $physical_contact = $this->gXPath->query("ro:addressPart[@type='addressLine']", $address);

              foreach($physical_contact as $contact){

                  $contacts[] =Array(
                      'contact_type' => $type.'_'.$count.'_addressLine',
                      'contact_value' => $contact->nodeValue
                  );
              }

              $physical_contact = $this->gXPath->query("ro:addressPart[@type='suburbOrPlaceOrLocality']", $address);
              $contact_value ='';
              foreach($physical_contact as $contact){

                  $contact_value = $contact->nodeValue;
              }
              $physical_contact = $this->gXPath->query("ro:addressPart[@type='stateOrTerritory']", $address);
              foreach($physical_contact as $contact){

                  $contact_value .= " ".$contact->nodeValue;
              }
              $physical_contact = $this->gXPath->query("ro:addressPart[@type='postCode']", $address);
              foreach($physical_contact as $contact){

                  $contact_value .= " ".$contact->nodeValue;
              }
              if($contact_value!=''){
                  $contacts[] =Array(
                      'contact_type' => $type.'_'.$count.'_suburbOrPlaceOrLocality stateOrTerritory postCode',
                      'contact_value' => $contact_value
                  );
              }

              $physical_contact = $this->gXPath->query("ro:addressPart[@type='country']", $address);
              foreach($physical_contact as $contact){

                  $contacts[] =Array(
                      'contact_type' => $type.'_'.$count.'_country',
                      'contact_value' => $contact->nodeValue
                  );
              }

              $physical_contact = $this->gXPath->query("ro:addressPart[@type='locationDescriptor']", $address);
              $contact_value ='';
              foreach($physical_contact as $contact){

                  $contact_value = $contact->nodeValue;
              }
              $physical_contact = $this->gXPath->query("ro:addressPart[@type='deliveryPointIdentifier']", $address);
              foreach($physical_contact as $contact){

                  $contact_value .= " ".$contact->nodeValue;
              }
              if($contact_value!=''){
                  $contacts[] =Array(
                      'contact_type' => $type.'_'.$count.'_locationDescriptor deliveryPointIdentifier',
                      'contact_value' => $contact_value
                  );
              }

              $physical_contact = $this->gXPath->query("ro:addressPart[@type='text']", $address);
              foreach($physical_contact as $contact){

                  $contacts[] =Array(
                      'contact_type' => $type.'_'.$count.'_text',
                      'contact_value' => $contact->nodeValue
                  );
              }
            $physical_contact = $this->gXPath->query("ro:addressPart[@type='telephoneNumber']", $address);

            foreach($physical_contact as $contact){

                $contacts[] =Array(
                    'contact_type' => $type.'_'.$count.'_telephoneNumber',
                    'contact_value' => "Ph: ".$contact->nodeValue
                );
            }
            $physical_contact = $this->gXPath->query("ro:addressPart[@type='faxNumber']", $address);

            foreach($physical_contact as $contact){

                $contacts[] =Array(
                    'contact_type' => $type.'_'.$count.'_faxNumber',
                    'contact_value' => "Fax: ".$contact->nodeValue
                );
            }
            // Fix API for HTML rendering
            $contacts[] = [
                'contact_type' => 'end',
                'contact_value' => ''
            ];
        }
        return $contacts;
    }
}


