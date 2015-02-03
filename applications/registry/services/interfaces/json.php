<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'interfaces/_interface.php');

class JSONInterface extends FormatHandler
{
	var $params, $options, $formatter; 
	
	function display($payload, $benchmark = array())
	{
		echo json_encode(array("status"=>"success", "message"=>$payload, "benchmark"=>$benchmark));
		return true;
	}
    
	function error($message)
	{
		echo json_encode(array("status"=>"error", "message"=>$message));
		return false;
	}
	
	function output_mimetype()
	{
		return 'application/json';
	}
	
}