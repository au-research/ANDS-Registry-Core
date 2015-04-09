<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'interfaces/_interface.php');

class DCIInterface extends FormatHandler
{
	var $params, $options, $formatter; 
	
	function display($payload)
	{
		
		echo "<?xml version=\"1.0\"?>".NL;
		echo '<DigitalContentData xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="DCI_schema_providers_V4.2.xsd">'.NL;
        echo implode($payload);
        echo '</DigitalContentData>';
        //echo $dciDoc;
	}
    
	function error($message)
	{
		$dciDoc = '<DigitalContentData xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="DCI_schema_providers_V4.2.xsd">'.NL;
        $dciDoc .= $message;
        $dciDoc .= '</DigitalContentData>';
        echo $dciDoc;
	}
	
	function output_mimetype()
	{
		return 'application/xml';
	}
}