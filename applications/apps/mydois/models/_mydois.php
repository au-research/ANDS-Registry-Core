<?php
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\FabricaClient;
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
		$allClients =  $this->clientRepository->getAll();
		foreach($allClients as $client){
			$client['domain_list'] = $this->mydois->getTrustedClientDomains($client->client_id);
			$client['datacite_prefix'] = $this->mydois->getTrustedClientActivePrefix($client->client_id);
		}

		return $allClients;
	}

	function addTrustedClient($ip, $client_name, $client_contact_name, $client_contact_email, $domainList, $datacite_prefix, $shared_secret){
		

		$app_id = sha1($shared_secret.$client_name);

        $client = $this->clientRepository->create([
            'ip_address' => $ip,
            'app_id' => $app_id,
            'client_name' => urldecode($client_name),
            'client_contact_name' => urldecode($client_contact_name),
            'client_contact_email' => urldecode($client_contact_email),
            'shared_secret' => $shared_secret
        ]);

		/*
		 * 'fabrica' => [
        'api_url' => env('DATACITE_FABRICA_URL', 'https://app.datacite.org'),
        'username' => env('DATACITE_FABRICA_USERNAME', 'ands'),
        'password' => env('DATACITE_FABRICA_PASSWORD', null)
    ],
		 */

		$fabricaConfig = \ANDS\Util\Config::get('datacite.fabrica');

		$dataCiteClient = new FabricaClient($fabricaConfig['username'],$fabricaConfig['password']);
		$dataCiteClient->setDataciteUrl($fabricaConfig['api_url']);
		$dataCiteClient->addClient($client);
		$client->addDomains($domainList);
		$client->addClientPrefix($datacite_prefix, true);

		return $dataCiteClient->getResponse();

	}

	function editTrustedClient($ip, $client_id, $client_name, $client_contact_name, $client_contact_email, $domainList, $datacite_prefix, $shared_secret){	
		$client_name = urldecode($client_name);
		$client_contact_name = urldecode($client_contact_name);
		$client_contact_email = urldecode($client_contact_email);
		$domainList = urldecode($domainList);
		$datacite_prefix = urldecode($datacite_prefix);
		$ip = urldecode($ip);

		$clientdata = array(
			   'client_id' => $client_id,
               'ip_address' =>  $ip,
               'client_name'  => $client_name, 
               'client_contact_name'    => $client_contact_name,  
               'client_contact_email'    => $client_contact_email,
               'shared_secret' => $shared_secret                     
        );

		$this->clientRepository->updateClient($clientdata);
		$client = $this->clientRepository->getByID($client_id);
		$client->removeClientDomains();
		$client->addDomains($domainList);
		$client->addClientPrefix($datacite_prefix, true);

		$fabricaConfig = \ANDS\Util\Config::get('datacite.fabrica');

		$dataCiteClient = new FabricaClient($fabricaConfig['username'],$fabricaConfig['password']);
		$dataCiteClient->setDataciteUrl($fabricaConfig['api_url']);
		$dataCiteClient->updateClient($client);

		return $dataCiteClient->getResponse();
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
		$this->clientRepository->deleteClientById($client_id);
	}

	function getTrustedClient($client_id)
	{
		$client = $this->clientRepository->getByID($client_id);
		return $client;
	}

	function getTrustedClientDomains($client_id)
	{
		$client = $this->clientRepository->getByID($client_id);
		$domains_str = "";
		$first = true;
		foreach ($client->domains as $domain) {
			if(!$first)
				$domains_str .= ",";
			$domains_str .= $domain->client_domain;
			$first = false;
		}
		return $domains_str;
	}


	function getTrustedClientActivePrefix($client_id){
		$client = $this->clientRepository->getByID($client_id);
		foreach ($client->prefixes as $clientPrefix) {
			if($clientPrefix->active)
				return $clientPrefix->prefix->prefix_value;
		}
	}

	function getAvailablePrefixesForClient($client_id)
	{

		$unallocatedPrefixes = $this->clientRepository->getUnalocatedPrefixes();
		$prefixes = [];
		$prefixes[] = $this->getTrustedClientActivePrefix($client_id);

		foreach($unallocatedPrefixes as $aPrefix)
		{
			$prefixes[] = $aPrefix->prefix_value;
		}
		return json_encode($prefixes);
	}

}
