<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Existence Dates handler
* @author Liz Woods <liz.woods@ands.org.au>
* @return array
*/

class ExistenceDates extends ROHandler {
	function handle() {
		$result = array();
        if ($this->xml) {
            foreach($this->xml->{$this->ro->class}->existenceDates as $dates){
                $dateStr = '';
                if($dates->startDate)
                {
                    $dateStr = (string)$dates->startDate;

                }
                if($dates->endDate)
                {
                    $dateStr .= " - ".(string)$dates->endDate;

                }
                $result[] = $dateStr;
            }
        }
        return $result;
	}
}