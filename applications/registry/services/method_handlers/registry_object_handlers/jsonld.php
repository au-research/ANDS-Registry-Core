<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Jsonld handler
 * Liz Woods <minh.nguyen@ands.org.au>
 * @return array
 */
class Jsonld extends ROHandler {
    function handle() {
        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($this->ro->id);
        $jsonld = \ANDS\Registry\Providers\RIFCS\JsonLDProvider::process($record);
        $return[] = $jsonld;
        return $return;
    }
}