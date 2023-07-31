<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 *  
 */
class Abs_sdmx_querytool extends MX_Controller {

	function index()
	{
		$data['js_lib'] = array('core','abs_sdmx_querytool');
		$data['scripts'] = array();
		$data['title'] = 'ABS SDMX Query Interface';
		$this->load->view('query_interface', $data);
	}
	
	function do_query()
	{	
		if($this->input->post('query') !== FALSE)
		{
			$this->input->set_cookie('last_used_sdmx_query',$this->input->post('query'), 9999999);
			
			$query = $this->wrap_soap_query($this->input->post('query'));
			$result = $this->send_abs_sdmx_soap($query);
			$result['query'] = htmlentities($query);
			
			if (strpos($result['content'],"An unknown error has occurred.")!==FALSE)
			{
				$result['content'] = '<div class="alert alert-error"><b>Service returned an error code</b><br/>We suggest waiting a few seconds before trying again...</div>';
			}
			else
			{
				$dom = new DOMDocument;
				$dom->preserveWhiteSpace = FALSE;
				$dom->loadXML(trim($result['content']));
				$dom->formatOutput = TRUE;
				$result['content'] = htmlspecialchars((string)$dom->saveXml());
				$result['content'] = '<textarea rows="20" style="width:480px; font-family:courier;">' . $result['content'] . '</textarea>';
			}
			//$result['headers'] = nl2br($result['headers']);
			
			echo json_encode($result);
			
		}
	}
	
	function send_abs_sdmx_soap($query)
	{
		$soap_do = curl_init();
		curl_setopt($soap_do, CURLOPT_URL,            "http://stat.abs.gov.au/sdmxws/sdmx.asmx" );
		curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($soap_do, CURLOPT_POST,           true );
		curl_setopt($soap_do, CURLOPT_POSTFIELDS,    $query);
		curl_setopt($soap_do, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($query), 'SOAPAction: "http://stats.oecd.org/OECDStatWS/SDMX/GetDataStructureDefinition"' , 'Accept: ' ));
		curl_setopt($soap_do, CURLINFO_HEADER_OUT, true);
		$result = curl_exec($soap_do);
		$err = curl_error($soap_do);
		
		return array('headers'=>curl_getinfo($soap_do,CURLINFO_HEADER_OUT), 'content'=>$result);
		
	}
	
	function wrap_soap_query($sdmx)
	{
		$xml = '
		<?xml version="1.0" encoding="utf-8"?>
		<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
		xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
		<soap:Body>
		<GetDataStructureDefinition xmlns="http://stats.oecd.org/OECDStatWS/SDMX/">
		<QueryMessage>
		
		
		<message:QueryMessage xmlns="http://www.SDMX.org/resources/SDMXML/schemas/v2_0/query"
		xmlns:message="http://www.SDMX.org/resources/SDMXML/schemas/v2_0/message"
		xsi:schemaLocation="http://www.SDMX.org/resources/SDMXML/schemas/v2_0/query
		http://www.sdmx.org/docs/2_0/SDMXQuery.xsd http://www.SDMX.org/resources/SDMXML/schemas/v2_0/message
		http://www.sdmx.org/docs/2_0/SDMXMessage.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
		<message:Header>
		<message:ID>none</message:ID>
		<message:Test>false</message:Test>
		<message:Prepared>2012-10-17T03:57:40</message:Prepared>
		<message:Sender id="ABS" />
		<message:Receiver id="ABS" />
		</message:Header>
		'.$sdmx.'
		</message:QueryMessage>
		</QueryMessage>
		</GetDataStructureDefinition>
		</soap:Body>
		</soap:Envelope>
		';
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = FALSE;
		$dom->loadXML(trim($xml));
		$dom->formatOutput = TRUE;
		return $dom->saveXml();
		
	}
		
}
	