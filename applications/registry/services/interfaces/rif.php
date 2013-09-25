<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'interfaces/_interface.php');

class RIFInterface extends FormatHandler
{
	var $params, $options, $formatter; 
	
	function display($payload)
	{
		echo wrapRegistryObjects(implode($payload,NL));
	}
    
	function error($message)
	{
		echo wrapRegistryObjects('');
	}
	
	function output_mimetype()
	{
		return 'application/xml';
	}
}