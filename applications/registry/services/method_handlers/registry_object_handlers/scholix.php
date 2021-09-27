<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Scholix handler
 * Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @return array
 */
class Scholix extends ROHandler {
    function handle() {
        $record = \ANDS\Repository\RegistryObjectsRepository::getRecordByID($this->ro->id);
        $scholix = \ANDS\Registry\Providers\Scholix\ScholixProvider::get($record);

        $ci =& get_instance();
        $wt = $ci->input->get('wt') ?: 'array';

        switch ($wt) {
            case "json":
                return $scholix->toJson();
            case "xml":
                return $scholix->toXML();
            case "oai":
                return $scholix->toOAI();
            default:
                return $scholix->toArray();
        }
    }
}