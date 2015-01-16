<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Temporal handler
* @author Liz Woods <liz.woods@ands.org.au>
* @return array list of all temporal fields
*/
class Temporal extends ROHandler {
	function handle() {
		$result = array();
        if ($this->index && $this->xml->{$this->ro->class}->coverage) {
            if($this->xml->{$this->ro->class}->coverage->temporal){
                foreach($this->xml->{$this->ro->class}->coverage->temporal->date as $date){
                    $eachDate = Array();
                        $eachDate[] = Array(
                            'type'=>(string)$date['type'],
                            'dateFormat'=>(string)$date['dateFormat'],
                            'date'=>(string)($date)
                        );
                    $result[] = Array(

                        'type' => 'date',
                        'date' => $eachDate
                    );
                }
                foreach($this->xml->{$this->ro->class}->coverage->temporal->text as $temporal){
                    $result[] = Array(
                        'type' => 'text',
                        'date' => (string)$temporal
                    );
                }
            }
        }
        return $result;
	}
}