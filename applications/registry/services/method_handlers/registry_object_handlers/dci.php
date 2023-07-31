<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
use ANDS\Registry\Providers\DCI\DataCitationIndexProvider;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * DCI Citation INDEX  handler
 * @author Liz Woods <liz.woods@ardc.edu.au>
 * @param  string type
 * @return array
 */
class DCI extends ROHandler {

	function handle()
    {
        $record = RegistryObjectsRepository::getRecordByID($this->ro->id);
        $dci = DataCitationIndexProvider::get($record);
        return $dci;
    }
}



