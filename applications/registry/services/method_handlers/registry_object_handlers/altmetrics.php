<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Jsonld handler
 * Liz Woods <minh.nguyen@ands.org.au>
 * @return array
 */
class Altmetrics extends ROHandler {
    function handle() {
        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($this->ro->id);
        $altmetrics = \ANDS\Registry\Providers\RIFCS\AltmetricsProvider::get($record);
        $return[] = $altmetrics;
        return $altmetrics;
    }
}