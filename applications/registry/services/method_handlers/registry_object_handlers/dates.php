<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Dates handler
* @author Liz Woods <liz.woods@ardc.edu.au>
* @return array
*/
class Dates extends ROHandler {
	function handle() {
		$result = array();
        if ($this->xml) {
            foreach($this->xml->{$this->ro->class}->dates as $dates){
                $eachDate = Array();
                $displayType = titleCase(str_replace("dc.","",(string) $dates['type']));
                foreach($dates as $date) {
                  if((string)($date)!=''){
                   $eachDate[] = Array(
                       'type'=>(string)$date['type'],
                       'dateFormat'=>(string)$date['dateFormat'],
                       'date'=>(string)($date)

                   );
                  }
                }
                if($eachDate){
                    $result[] = Array(
                        'type' => (string) $dates['type'],
                        'displayType' => $displayType,
                        'date' => $eachDate
                    );
                }
            }
        }
        return $result;
	}
}