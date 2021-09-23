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

		$result = array();

        $record = RegistryObjectsRepository::getRecordByID($this->ro->id);
        $duplicates = $record->getDuplicateRecords();

        foreach ($duplicates as $duplicate) {
            $result[] = [
                'registry_object_id' => $duplicate->registry_object_id,
                'slug' => $duplicate->slug,
                'title' => $duplicate->title,
                'group' => $duplicate->group
            ];
        }

        return $result;
	}
}