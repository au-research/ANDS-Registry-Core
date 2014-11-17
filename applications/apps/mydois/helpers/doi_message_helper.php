<?php 

function doisGetResponseType($response)
{
	$responses = explode(" ", $response);
	return $responses[0];
}							
function doisGetUserMessage($responseCode, $doi_id,$response_type="string",$app_id="",$verbosemessage="",$urlValue="")
{
	$message = '';
	$htmlHeader = '';
	global $api_version;
	switch($responseCode)
	{
		case "MT001":
			$message = "DOI ".$doi_id." was successfully minted.";
			$type = "success";
			break;
		case "MT002":
			$message = "DOI ".$doi_id." was successfully updated.";
			$type = "success";
			break;
		case "MT003":
			$message = "DOI ".$doi_id." was successfully inactivated.";
			$type = "success";			
			break;
		case "MT004":
			$message = "DOI ".$doi_id." was successfully activated.";
			$type = "success";					
			break;
		case "MT005":
			$message = "The ANDS Cite My Data service is currently unavailable. Please try again at a later time. If you continue to experience problems please contact services@ands.org.au.";	
			$type = "failure";		
			$htmlHeader	= "HTTP/1.0 500 Internal Server Error";	
			break;
		case "MT006":
			$message = "The metadata you have provided to mint a new DOI has failed the schema validation. 
			Metadata is validated against the latest version of the DataCite Metadata Schema. 
			For information about the schema and the latest version supported, 
			please visit the ANDS website http://ands.org.au. 
			Detailed information about the validation errors can be found below.";
			$type = "failure";	
			$htmlHeader	= "HTTP/1.0 500 Internal Server Error";		
			break;
		case "MT007":
			$message = "The metadata you have provided to update DOI ".$doi_id." has failed the schema validation. 
			Metadata is validated against the DataCite Metadata Schema.
			For information about the schema and the latest version supported, 
			please visit the ANDS website http://ands.org.au. 
			Detailed information about the validation errors can be found below.";
			$type = "failure";
			$htmlHeader	= "HTTP/1.0 500 Internal Server Error";			
			break;
		case "MT008":
			$message = "You do not appear to be the owner of DOI ".$doi_id.". If you believe this to be incorrect please contact services@ands.org.au.";
			$type = "failure";	
			$htmlHeader = "HTTP/1.0 415 Authentication Error";	
			break;								
		case "MT009":
			$message = "You are not authorised to use this service. For more information or to request access to the service please contact services@ands.org.au.";
			$type = "failure";
			$htmlHeader = "HTTP/1.0 415 Authentication Error";			
			break;
		case "MT010":
			$message = "There has been an unexpected error processing your doi request. For more information please contact services@ands.org.au.";	
			$type = "failure";	
			$htmlHeader	= "HTTP/1.0 500 Internal Server Error";				
			break;
		case "MT011":
			$message = "DOI ".$doi_id." does not exist in the ANDS Cite My Data service.";
			$type = "failure";					
			break;	
		case "MT012":
			$message = "No metadata exists in the Cite My Data service for DOI ".$doi_id;
			$type = "failure";					
			break;		
		case "MT013":
			$message = $verbosemessage;
			$verbosemessage = strlen($verbosemessage) . " bytes";
			$type = "success";					
			break;
        case "MT014":
            $message = "The provided URL does not belong to any of your registered top level domains. If you would like to add additional domains to your account please contact services@ands.org.au. ";
            $type = "failure";
            break;
        case "MT090":
			// Success response for status pings (verbose message should indicate ms turnaround time)
			$message = "The rocket is ready to blast off -- all systems are go!";
			$type = "success";
			break;		
		case "MT091":
			// Failure response for status pings
			$message = "Uh oh! DOI Service unavailable (unable to process upstream DOI request). Please try again in a few moments. ";
			$type = "failure";					
			break;		
		default:
			$message = "There has been an unidentified error processing your doi request. For more information please contact services@ands.org.au.";
			$type = "failure";
			break;									
	}
	
	if($api_version=='1.0'&& $htmlHeader != '')
	{
		header($htmlHeader);
	}else{
		header("HTTP/1.0 200 OK");
	}
	
	switch($response_type)
	{
		case "string":
			header('Content-type: text/html');
			return "[".$responseCode."] ".$message."<br />".$verbosemessage."<br/>".$urlValue;
			break;
			
		case "xml":
			header('Content-type: text/xml');
			$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
			$xml .= '<response type="'.$type.'">';
			$xml .='	<responsecode>'.$responseCode.'</responsecode>';
			$xml .='	<message>'.$message.'</message>';	
			$xml .='	<doi>'.$doi_id.'</doi>';
			$xml .='	<url>'.$urlValue.'</url>';
			$xml .='	<app_id>'.$app_id.'</app_id>';
			$xml .='	<verbosemessage>'.$verbosemessage.'</verbosemessage>';							
			$xml .= '</response>';
			return $xml;	
			break;	
						
		case "json":
			$response = array();
			$response['type'] = $type;
			$response['responsecode'] = $responseCode;
			$response['message'] = $message;
			$response['doi'] = $doi_id;			
			$response['url'] = $urlValue;
			$response['app_id'] = $app_id;
			$response['verbosemessage'] = $verbosemessage;
			return '{"response" :'.json_encode($response).'}';
			break;
	}
	
	
}  

function setResponseType()
{
	$CI =& get_instance();
	$suffix = substr($CI->input->server('REQUEST_URI'), strpos($CI->input->server('REQUEST_URI'), $CI->router->fetch_class()));
	$suffix = array_shift(explode("/", $suffix));
	$format="string";
	if (strpos($suffix, ".") !== FALSE)
	{
		$format = array_pop(explode(".",$suffix));
	}
	$_GET['response_type'] = $format;
}
setResponseType();
 ?>