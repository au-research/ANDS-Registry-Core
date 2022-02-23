<?php use ANDS\Repository\RegistryObjectsRepository;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Identifier matching handler
* @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
* @return array
*/

class Identifiermatch extends ROHandler {
	function handle() {
	    $identifiermatch = array();
        $myceliumServiceClient = new ANDS\Mycelium\MyceliumServiceClient(ANDS\Util\Config::get('mycelium.url'));
        $duplicates =   json_decode($myceliumServiceClient->getDuplicateRecords($this->ro->id));

        $result = $myceliumServiceClient->getDuplicateRecords($this->ro->id);
        if ($result->getStatusCode() != 200) {
            // todo warning
            return [];
        }

        $duplicates = json_decode($result->getBody());

        // the MyceliumService will also return the original record in the list so remove it
        foreach ($duplicates as $duplicate){
           if($duplicate->identifier!=$this->ro->id){
               $identifiermatch[] = $duplicate;
           };
         }
        // return the duplicate records sort alphabetically on title
        usort($identifiermatch, function($a, $b) {return strcmp($a->title, $b->title);});
        return  $identifiermatch;
	}
}