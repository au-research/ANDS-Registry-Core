<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class _mydois extends CI_Model
{

	private $_CI; 
	private $doi_db = null;
	private $DOI_SERVICE_BASE_URI = null;
	private $DOIS_DATACENTRE_NAME_PREFIX = null;
	private $DOIS_DATACENTRE_NAME_MIDDLE = null;

	function __construct(){
		parent::__construct();
		$this->_CI =& get_instance();
		$this->doi_db = $this->load->database('dois', TRUE);
		$this->DOI_SERVICE_BASE_URI = $this->_CI->config->item('gDOIS_SERVICE_BASE_URI');	
		$this->DOIS_DATACENTRE_NAME_PREFIX = $this->_CI->config->item('gDOIS_DATACENTRE_NAME_PREFIX');
		$this->DOIS_DATACENTRE_NAME_MIDDLE = $this->_CI->config->item('gDOIS_DATACENTRE_NAME_MIDDLE');
		$this->DOIS_DATACENTRE_PREFIXS = $this->_CI->config->item('gDOIS_DATACENTRE_PREFIXS');
		$this->gDefaultBaseUrl = $this->_CI->config->item('default_base_url');
	}

	function getTrustedClients(){
		$query = $this->doi_db->query("SELECT * FROM doi_client");
		return $query->result_array();
	}

	function buildPrefixOptions()
	{

		$optionStr = '';
		foreach($this->DOIS_DATACENTRE_PREFIXS as $aPrefix)
		{
			$optionStr .= '<option value="'.$aPrefix.'">'.trim($aPrefix,'//').'</option>';
		}
		return $optionStr;
	}

	function addTrustedClient($ip, $client_name, $client_contact_name, $client_contact_email, $domainList, $datacite_prefix, $shared_secret){
		
		$resultXML = '';
		$result = '';
		$mode='';
		$app_id = sha1($shared_secret.$client_name);

		$client_name = urldecode($client_name);
		$client_contact_name = urldecode($client_contact_name);
		$client_contact_email = urldecode($client_contact_email);
		$domainList = urldecode($domainList);
		$datacite_prefix = urldecode($datacite_prefix);


		//need to add the client to our db and then obtain their client-id:
		$clientdata = array(
               'ip_address' =>  $ip,
               'app_id' => $app_id,
               'client_name'  => $client_name, 
               'client_contact_name'    => $client_contact_name,  
               'client_contact_email'    => $client_contact_email,
               'datacite_prefix'    => $datacite_prefix,
               'shared_secret' => $shared_secret                                     
            	);
		$this->doi_db->insert('doi_client', $clientdata); 

		$query = $this->doi_db->query("SELECT MAX(client_id) as client_id FROM doi_client");

		$client_id = $query->result_array();
		$client_id = $client_id[0]['client_id'];
		$clientDomains= explode(",",$domainList);

		foreach($clientDomains as $aDomain){
			$domainData = array(
				'client_id' => $client_id,
				'client_domain' => $aDomain);
			$this->doi_db->insert('doi_client_domains', $domainData); 
		}


		if($client_id<10){$client_id = "-".$client_id;}

		return $this->mdsDatacentreUpdate($client_name, $client_contact_name, $client_contact_email, $domainList, $datacite_prefix,$client_id);
	
	}

	function editTrustedClient($ip, $client_id, $client_name, $client_contact_name, $client_contact_email, $domainList, $datacite_prefix, $shared_secret){	
		$resultXML = '';
		$result = '';
		$mode='';

		$client_name = urldecode($client_name);
		$client_contact_name = urldecode($client_contact_name);
		$client_contact_email = urldecode($client_contact_email);
		$domainList = urldecode($domainList);
		$datacite_prefix = urldecode($datacite_prefix);
		$ip = urldecode($ip);
		
		$this->doi_db->delete('doi_client_domains', array('client_id' => $client_id)); 

		$clientDomains= explode(",",$domainList);

		foreach($clientDomains as $aDomain){
			$domainData = array(
				'client_id' => $client_id,
				'client_domain' => $aDomain);
			$this->doi_db->insert('doi_client_domains', $domainData); 
		}

		$clientdata = array(
               'ip_address' =>  $ip,
               'client_name'  => $client_name, 
               'client_contact_name'    => $client_contact_name,  
               'client_contact_email'    => $client_contact_email,
               'datacite_prefix'    => $datacite_prefix, 
               'shared_secret' => $shared_secret                     
            	);

		$this->doi_db->where('client_id', $client_id); 
		$this->doi_db->update('doi_client', $clientdata); 

		
		if($client_id<10){$client_id = "-".$client_id;}

		return $this->mdsDatacentreUpdate($client_name, $client_contact_name, $client_contact_email, $domainList, $datacite_prefix,$client_id);
	}

	function mdsDatacentreUpdate($client_name, $client_contact_name, $client_contact_email, $domainList, $datacite_prefix,$client_id)
	{
		if($this->gDefaultBaseUrl!="https://researchdata.ands.org.au/")
		{
			$symbol= $this->DOIS_DATACENTRE_NAME_PREFIX.".TEST"; //make sure we only hit the test datacenter config for non production domains
		}else{
			$symbol= $this->DOIS_DATACENTRE_NAME_PREFIX.".".$this->DOIS_DATACENTRE_NAME_MIDDLE.$client_id;
		}

		//create the datacite datacentre xml
		$outxml = '<?xml version="1.0" encoding="UTF-8"?>
		<datacentre><name>'.$client_name.'</name>
		<symbol>'.$symbol.'</symbol>
		<domains>'.$domainList.'</domains>
		<isActive>true</isActive>
		<prefixes><prefix>'.trim($datacite_prefix,"/").'</prefix></prefixes>
		<contactName>'.$client_contact_name.'</contactName>
		<contactEmail>'.$client_contact_email.'</contactEmail>
		</datacentre>';

		$authstr =  $this->_CI->config->item('gDOIS_DATACENTRE_NAME_PREFIX').":".$this->_CI->config->item('gDOIS_DATACITE_PASSWORD');
		$context  = array('Content-Type: application/xml;charset=UTF-8','Authorization: Basic '.base64_encode($authstr));		
		$requestURI = $this->DOI_SERVICE_BASE_URI."datacentre";	

	
		$newch = curl_init();
		curl_setopt($newch, CURLOPT_URL, $requestURI);
		curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($newch, CURLOPT_CUSTOMREQUEST, "PUT");			
		curl_setopt($newch, CURLOPT_HTTPHEADER,$context);
		curl_setopt($newch, CURLOPT_POSTFIELDS,$outxml);
		$result = curl_exec($newch);
		$curlinfo = curl_getinfo($newch);
		curl_close($newch);

		$result_array = array();
		if( $result )
		{
			$resultXML = $result;
		}else{
			$result_array['errorMessages'] = "Error whilst attempting to fetch from URI: " . $this->DOI_SERVICE_BASE_URI;
		}
		return $result_array;		
	}

	function getAllDoiAppID(){
		$result = array();
		$query = $this->doi_db->select('app_id')->distinct()->from('doi_client')->get();
		if($query->num_rows()==0) return array();
		foreach($query->result_array() as $r){
			$result[] = $r['app_id'];
		}
		return $result;
	}

	function removeTrustedClient($client_id){

		$tables = array('doi_client_domains', 'doi_client');
		$this->doi_db->where('client_id', $client_id);
		$this->doi_db->delete($tables);
		return $client_id;
	}

	
	function getTrustedClient($client_id)
	{
		$query = $this->doi_db->query("SELECT * FROM doi_client WHERE client_id = ".$client_id);
		return $query->result_array();		
	}

	function getTrustedClientDomains($client_id)
	{
		$domainList ='';

		$query = $this->doi_db->query("SELECT * FROM doi_client_domains WHERE client_id = ".$client_id);

		foreach($query->result_array() as $adomain)
		{
			$domainList .= $adomain['client_domain'].",";
		}
		$domainList = trim($domainList,",");
		return $domainList;		
	}

}
