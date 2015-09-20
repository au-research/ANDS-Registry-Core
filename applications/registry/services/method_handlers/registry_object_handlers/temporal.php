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
        if($this->gXPath->evaluate("count(//ro:coverage/ro:temporal)")>0) {
            $query = "//ro:coverage/ro:temporal";
            $dates = $this->gXPath->query($query);
            foreach($dates as $date){
                $eachDate = Array();
                foreach($date->getElementsByTagName('date') as $adate){
                    if($adate->nodeValue!=''){
                       $eachDate[] = Array(
                            'type'=>$adate->getAttribute('type'),
                            'dateFormat'=>$adate->getAttribute('dateFormat'),
                            'date'=>$adate->nodeValue
                        );
                    }
                }
                if(count($eachDate)>0){
                    $result[]= Array(
                        'type'=>'date',
                        'date'=> $eachDate
                    );
                }
            }
            foreach($dates as $date){
                $eachDate = Array();
                foreach($date->getElementsByTagName('text') as $adate){
                    if($adate->nodeValue!=''){
                        $eachDate[] = Array(
                            'type'=>$adate->getAttribute('type'),
                            'dateFormat'=>$adate->getAttribute('dateFormat'),
                            'date'=>$adate->nodeValue
                        );
                    }
                }
                if(count($eachDate)>0){
                    $result[]= Array(
                        'type'=>'text',
                        'date'=> $eachDate
                    );
                }
            }
        }

        
        return $result;
	}
}
