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
	private $DATACITE_CONTACT_NAME = null;
	private $DATACITE_CONTACT_EMAIL = null;
	private $DATACITE_FABRICA_URL = null;
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
		$this->DATACITE_CONTACT_NAME = $config['contact-name'];
		$this->DATACITE_CONTACT_EMAIL = $config['contact-email'];


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
		return $this->clientRepository->getAll();
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

		$fabricaConfig = \ANDS\Util\Config::get('datacite.fabrica');

	
		$dataCiteClient = new \ANDS\DOI\FabricaClient($fabricaConfig['username'],$fabricaConfig['password']);

		$response = $dataCiteClient->addClient($client);
		
		$client_id = $client->client_id;

        foreach (explode(",",$domainList) as $aDomain) {
            $domainData = array(
                'client_id' => $client_id,
                'client_domain' => $aDomain
            );
            $this->doi_db->insert('doi_client_domains', $domainData);
        }

		if($client_id<10){$client_id = "-".$client_id;}

		$response =  $this->doFabricaDatacentreAPICall($client_name, $domainList, $datacite_prefix, $client_id , "create");
		if(isset($response['errorMessages'])){
			$client->delete();
		}

		return $response;

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

		$response =  $this->doFabricaDatacentreAPICall($client_name, $domainList, $datacite_prefix, $client_id, "update");
		if(!isset($response['errorMessages'])){
			$this->doi_db->where('client_id', $client_id);
			$this->doi_db->update('doi_client', $clientdata);
		}

		return $response;
	}

	function doFabricaDatacentreAPICall($client_name, $domainList, $datacite_prefix, $client_id, $transaction)
	{


		$client = $this->clientRepository->getByID($client_id);
		if($transaction != "delete"){
			$attributes = array("name" => $client_name,
				"symbol" => $client->datacite_symbol,
				"domains" => $domainList,
				"is-active" => true,
				"contact-name" => $this->DATACITE_CONTACT_NAME,
				"contact-email" => $this->DATACITE_CONTACT_EMAIL);
			$provider = array("data" => array("type" => "providers",
				"id" => "ands"));
			$prefixes = array("data" => array("id" => trim($datacite_prefix,"/"),
				"type" => "prefix"));
			$relationships = array("provider" => $provider, "prefixes" => $prefixes);
			$clientInfo = array("data" => array("attributes" => $attributes, "relationships" => $relationships, "type" => "client"));
		}

		$fabricaConfig = \ANDS\Util\Config::get('datacite.fabrica');

		$authstr =  $fabricaConfig['username'].":".$fabricaConfig['password'];

		$context = [
            'Content-Type: application/json',
            'Authorization: Basic '.base64_encode($authstr)
        ];

		$newch = curl_init();
		$requestURI = $fabricaConfig['api_url'];

		switch ($transaction){
			case "update":
				$requestURI = $requestURI.$client->datacite_symbol;
				$method = "PATCH";
				curl_setopt($newch, CURLOPT_POSTFIELDS,json_encode($clientInfo));
				break;
			case "create":
				$method = "POST";
				curl_setopt($newch, CURLOPT_POSTFIELDS,json_encode($clientInfo));
				break;
			case "delete":
				$requestURI = $requestURI.$client->datacite_symbol;
				$method = "DELETE";
				break;
		}


		curl_setopt($newch, CURLOPT_URL, $requestURI);
		curl_setopt($newch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($newch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($newch, CURLOPT_HTTPHEADER,$context);

		$result = curl_exec($newch);
		$outputINFO = curl_getinfo($newch);
		curl_close($newch);
		$result_array = array();
		if($outputINFO['http_code'] > 199 && $outputINFO['http_code'] < 400)
		{
			$result_array = $result;
		}else{
			$result_array['errorMessages'] = "Error whilst attempting to put to URI: " . $this->DATACITE_FABRICA_URL . "<br/><em>".
			$client_name ."</em>has not been ".$transaction."d response:".$result;
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

		$this->doFabricaDatacentreAPICall("", "", "", $client_id, "delete");
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
