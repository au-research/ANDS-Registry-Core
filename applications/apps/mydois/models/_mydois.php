<?php use ANDS\DOI\Repository\ClientRepository;

if (!defined('BASEPATH')) exit('No direct script access allowed');


class _mydois extends CI_Model
{

	private $_CI; 
	private $doi_db = null;
	private $DOI_SERVICE_BASE_URI = null;
	private $DOIS_DATACENTRE_NAME_PREFIX = null;
	private $DOIS_DATACENTRE_NAME_MIDDLE = null;
    private $DOIS_DATACENTRE_PASSWORD = null;

    /** @var ClientRepository */
    private $clientRepository;

	function __construct(){
		parent::__construct();
		$this->_CI =& get_instance();
		$this->doi_db = $this->load->database('dois', TRUE);
		$config = \ANDS\Util\Config::get('datacite');
		$this->DOI_SERVICE_BASE_URI = $config['base_url'];
		$this->DOIS_DATACENTRE_NAME_PREFIX = $config['name_prefix'];
		$this->DOIS_DATACENTRE_NAME_MIDDLE = $config['name_middle'];
		$this->DOIS_DATACENTRE_PREFIXS = $config['prefixs'];
        $this->DOIS_DATACENTRE_PASSWORD = $config['password'];
		$this->gDefaultBaseUrl = get_config_item('default_base_url');

        $database = \ANDS\Util\Config::get('database.dois');
        $this->clientRepository = new ClientRepository(
            $database['hostname'],
            $database['database'],
            $database['username'],
            $database['password']
        );
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

		//need to add the client to our db and then obtain their client-id:
        $client = $this->clientRepository->create([
            'ip_address' => $ip,
            'app_id' => $app_id,
            'client_name' => urldecode($client_name),
            'client_contact_name' => urldecode($client_contact_name),
            'client_contact_email' => urldecode($client_contact_email),
            'datacite_prefix' => urldecode($datacite_prefix),
            'shared_secret' => $shared_secret
        ]);

		$client_id = $client->client_id;

        foreach (explode(",",$domainList) as $aDomain) {
            $domainData = array(
                'client_id' => $client_id,
                'client_domain' => $aDomain
            );
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

		//if($client_id<10){$client_id = "-".$client_id;}

		return $this->mdsDatacentreUpdate($client_name, $client_contact_name, $client_contact_email, $domainList, $datacite_prefix,$client_id);
	}

	function mdsDatacentreUpdate($client_name, $client_contact_name, $client_contact_email, $domainList, $datacite_prefix,$client_id)
	{
		//Removed check on non production domains to cater for future use of demo by clients
	    //if($this->gDefaultBaseUrl!="https://researchdata.ands.org.au/")
		//{
		//	$symbol= $this->DOIS_DATACENTRE_NAME_PREFIX.".CENTRE-0"; //make sure we only hit the test datacenter config for non production domains
		//}else{

        $client = $this->clientRepository->getByID($client_id);

        $symbol= $client->datacite_symbol;

		//}

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

		$authstr =  $this->DOIS_DATACENTRE_NAME_PREFIX.":".$this->DOIS_DATACENTRE_PASSWORD;
		$context = [
            'Content-Type: application/xml;charset=UTF-8',
            'Authorization: Basic '.base64_encode($authstr)
        ];

		$requestURI = $this->DOI_SERVICE_BASE_URI."datacentre";

        $newch = curl_init();
		curl_setopt($newch, CURLOPT_URL, $requestURI);
		curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($newch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($newch, CURLOPT_HTTPHEADER,$context);
		curl_setopt($newch, CURLOPT_POSTFIELDS,$outxml);
		$result = curl_exec($newch);

        $outputINFO = curl_getinfo($newch);

		curl_close($newch);

		$result_array = array();
		if( $result )
		{
			$resultXML = $result;
		}else{
			$result_array['errorMessages'] = "Error whilst attempting to put to URI: " . $this->DOI_SERVICE_BASE_URI . "<br/><em> $client_name </em>has not been updated";
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
