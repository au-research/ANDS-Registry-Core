<?php use ANDS\DOI\DataCiteClient;
use ANDS\DOI\FabricaClient;
use ANDS\DOI\Repository\ClientRepository;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mydois extends MX_Controller {

    /** @var ClientRepository */
    private $clientRepository;

    /** @var FabricaClient */
    private $fabricaClient;

    private $fabricaUrl;

    private $unallocatedPrefixLimit = 5;

    private $testPrefix = "10.5072";
    private $old_prod_prefixes = ['10.4225','10.4226','10.4227'];
    /**
     * MyDOIS SPA
     */
	function index() {
        acl_enforce('DOI_USER');

        $this->load->view('doi_cms_app', [
            'title' => 'ANDS DOI Management App',
            'js_lib' => ['core', 'angular129', 'prettyprint', 'APIService', 'APIRoleService', 'APIDOIService', 'xmlToJson'],
            'scripts' => ['doi_cms_app', 'doi_cms_mainCtrl', 'angular_datacite_xml_builder']
        ]);
	}

    /**
     * TODO change it to logical delete
     * added checks to only test client
     */
    function remove_trusted_client(){
		acl_enforce('SUPERUSER');
        $client_id = $this->input->post('client_id');
        $this->clientRepository->deleteClientById($client_id);
        // TODO delete from datacite? (WE SHOULDN'T)
	}

    /**
     * mydois/list_trusted
     * List Trusted Client SPA
     * Front end for list trusted doi clients app
     */
    function list_trusted(){
		acl_enforce('SUPERUSER');
		$this->load->view('trusted_clients_index', [
		    'title' => 'List Trusted Clients',
            'scripts' => ['trusted_clients'],
            'js_lib' => ['core', 'dataTables']
        ]);
	}

    function get_available_prefixes(){
        $prefixes = [];
        $prefixes[] = $this->testPrefix;
        $this->fabricaClient->syncUnallocatedPrefixes();
        $unallocatedPrefixes = $this->clientRepository->getUnalocatedPrefixes();
        foreach($unallocatedPrefixes as $aPrefix) {
            if(sizeof($prefixes) >= $this->unallocatedPrefixLimit)
                break;
            if(!in_array($aPrefix->prefix_value, $this->old_prod_prefixes))
                $prefixes[] = $aPrefix->prefix_value;
        }
        echo json_encode($prefixes);
    }

    function sync_prefixes(){
        echo json_encode($this->fabricaClient->syncUnallocatedPrefixes());
    }

    public function fetch_unassigned_prefix()
    {
        $response = [];
        $unallocatedPrefixes = $this->clientRepository->getUnalocatedPrefixes();

        if(sizeof($unallocatedPrefixes) > $this->unallocatedPrefixLimit){
            $response['message'] = "Number of Unallocated Prefixes (".sizeof($unallocatedPrefixes).") is greater than the Prefix Limit of (".$this->unallocatedPrefixLimit.").";
        }elseif(sizeof($unallocatedPrefixes) == $this->unallocatedPrefixLimit){
            $response['message'] = "Number of Unallocated Prefixes (".sizeof($unallocatedPrefixes).") is equal to the Prefix Limit of (".$this->unallocatedPrefixLimit.").";
        }else{

            $numberofPrefixestoFetch = $this->unallocatedPrefixLimit - sizeof($unallocatedPrefixes);
//            // mock response
//            $response['newPrefixes'] = [];
//            for($i = 0 ; $i < $numberofPrefixestoFetch ; $i++){
//                $response['newPrefixes'][] = "10.999999".$i;
//            }
            $response['newPrefixes'] = $this->fabricaClient->claimNumberOfUnassignedPrefixes($numberofPrefixestoFetch);

            $response['message'] = "Fetched ".sizeof($response['newPrefixes']) . " new Prefixe(s)";
        }
        echo json_encode($response);
    }
    /**
     * AJAX entry for mydois/list_trusted
     */
    function list_trusted_clients(){
		echo json_encode($this->getTrustedClients());
	}

    /**
     * TODO refactor to ANDS-DOI-SERVICE functionality
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    private function getTrustedClients(){
        $allClients =  $this->clientRepository->getAll();
        foreach($allClients as $client){
            $client["url"] = $this->fabricaUrl  . "/clients/" . strtolower($client->datacite_symbol);
            $client['domain_list'] = str_replace(","," ",$this->getTrustedClientDomains($client->client_id));
            $client['datacite_prefix'] = $this->getTrustedClientActivePrefix($client->client_id);
            $client['not_active_prefixes'] = $this->getTrustedClientNonActivePrefixes($client->client_id);
        }

        return $allClients;
    }

    /**
     * TODO refactor to ANDS-DOI-SERVICE functionality
     *
     * @param $client_id
     * @return string
     */
    private function getTrustedClientDomains($client_id)
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

    /**
     * TODO refactor to ANDS-DOI-SERVICE functionality
     *
     * @param $client_id
     * @return mixed
     */
    private function getTrustedClientActivePrefix($client_id){
        $client = $this->clientRepository->getByID($client_id);
        if(is_array_empty($client->prefixes))
            return "";
        foreach ($client->prefixes as $clientPrefix) {
            if($clientPrefix->active && $clientPrefix->prefix != null)
                return $clientPrefix->prefix->prefix_value;
        }
        return "";
    }

    /**
     * TODO refactor to ANDS-DOI-SERVICE functionality
     *
     * @param $client_id
     * @return mixed
     */
    private function getTrustedClientNonActivePrefixes($client_id){
        $client = $this->clientRepository->getByID($client_id);
        $notActiveprefixes = "";
        if(is_array_empty($client->prefixes))
            return "";
        foreach ($client->prefixes as $clientPrefix) {
            if(!$clientPrefix->active)
                $notActiveprefixes .= $clientPrefix->prefix->prefix_value.", ";
        }
        return trim($notActiveprefixes, ', "');
    }

    /**
     * AJAX Entry for adding trusted client
     */
    function add_trusted_client() {
		acl_enforce('SUPERUSER');
        $response = [];
		$posted = $this->input->post('jsonData');
		$ip = trim(urlencode($posted['ip_address']));
		$client_name = trim(urlencode($posted['client_name']));
		$client_contact_name = trim(urlencode($posted['client_contact_name']));
		$client_contact_email = trim(urlencode($posted['client_contact_email']));
		$domainList = trim(urlencode($posted['domainList']));
		$datacite_prefix = 	trim(urlencode($posted['datacite_prefix']));
		$shared_secret = trim(urlencode($posted['shared_secret']))	;
        
        // add the client to the repository
        $client = $this->clientRepository->create([
            'ip_address' => $ip,
            'app_id' => sha1($shared_secret.$client_name),
            'client_name' => urldecode($client_name),
            'client_contact_name' => urldecode($client_contact_name),
            'client_contact_email' => urldecode($client_contact_email),
            'shared_secret' => $shared_secret
        ]);
        
        $client->addDomains($domainList);
        $client->addClientPrefix($datacite_prefix, true);

        $this->fabricaClient->addClient($client);

        if($this->fabricaClient->hasError()){
            $response['responseCode'] = $this->fabricaClient->responseCode;
            $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
            $response['Messages'] = $this->fabricaClient->getMessages();
            echo json_encode($response);
            exit();
        }

        if($datacite_prefix && $datacite_prefix != $this->testPrefix){

            $this->fabricaClient->updateClientPrefixes($client);
            if($this->fabricaClient->hasError()){
                $response['responseCode'] = $this->fabricaClient->responseCode;
                $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
                $response['Messages'] = $this->fabricaClient->getMessages();
                echo json_encode($response);
                exit();
            }
        }

        if($this->fabricaClient->responseCode == 200 || $this->fabricaClient->responseCode == 201)
            echo $this->fabricaClient->responseCode;
	}

    /**
     * AJAX entry
     * for editing a client
     */
    function get_trusted_client() {
		acl_enforce('SUPERUSER');
		$client_id = $this->input->post('id');
		$response = $this->clientRepository->getByID($client_id);
		$response['domain_list'] = $this->getTrustedClientDomains($client_id);
		$response['datacite_prefix'] = $this->getTrustedClientActivePrefix($client_id);
		$response['available_prefixes'] = $this->getAvailablePrefixesForClient($client_id);
		echo json_encode($response);
	}

    /**
     * TODO refactor to ANDS-DOI-SERVICE functionality
     *
     * @param $client_id
     * @return array
     */
    private function getAvailablePrefixesForClient($client_id)
    {

        $unallocatedPrefixes = $this->clientRepository->getUnalocatedPrefixes();
        $prefixes = [];

        if ($ownPrefix = $this->getTrustedClientActivePrefix($client_id)) {
            $prefixes[] = $ownPrefix;
        }

        foreach($unallocatedPrefixes as $aPrefix) {
            if(sizeof($prefixes) >= $this->unallocatedPrefixLimit)
                break;
            if(!in_array($aPrefix->prefix_value, $this->old_prod_prefixes))
                $prefixes[] = $aPrefix->prefix_value;
        }
        $prefixes[] = $this->testPrefix;
        return $prefixes;
    }

    /**
     * AJAX entry
     * for commiting a change to a client
     */
    function edit_trusted_client() {
		acl_enforce('SUPERUSER');

		$posted = $this->input->post('jsonData');
		$ip = trim($posted['ip_address']);
		$client_id = trim($posted['client_id']);
		$client_name = trim($posted['client_name']);
		$client_contact_name = trim($posted['client_contact_name']);
		$client_contact_email = trim($posted['client_contact_email']);
		$domainList = trim($posted['domainList']);
		$datacite_prefix = 	trim($posted['datacite_prefix']);
		$shared_secret = trim($posted['shared_secret']);

        $clientdata = [
            'client_id' => $client_id,
            'ip_address' =>  $ip,
            'client_name'  => $client_name,
            'client_contact_name' => $client_contact_name,
            'client_contact_email' => $client_contact_email,
            'shared_secret' => $shared_secret
        ];
        $this->clientRepository->updateClient($clientdata);
        $client = $this->clientRepository->getByID($client_id);
        $client->removeClientDomains();
        $client->addDomains($domainList);

        $hasPrefix = $client->hasPrefix($datacite_prefix);
        
        $client->addClientPrefix($datacite_prefix, true);

        $this->fabricaClient->updateClient($client);


        if($this->fabricaClient->hasError()){
            $response['responseCode'] = $this->fabricaClient->responseCode;
            $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
            $response['Messages'] = $this->fabricaClient->getMessages();
            echo json_encode($response);
            exit();
        }

        if($datacite_prefix && $datacite_prefix != $this->testPrefix && !$hasPrefix){
            $this->fabricaClient->updateClientPrefixes($client);
            if($this->fabricaClient->hasError()){
                $response['responseCode'] = $this->fabricaClient->responseCode;
                $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
                $response['Messages'] = $this->fabricaClient->getMessages();
                echo json_encode($response);
                exit();
            }
        }



        if($this->fabricaClient->responseCode == 200 || $this->fabricaClient->responseCode == 201)
            echo $this->fabricaClient->responseCode;
    }

    /**
     * TODO refactor LinkChecking API. Ask @Cel
     * @throws Exception
     */
    function runDoiLinkChecker()
	{
		header('Content-Type: application/json');
		acl_enforce('DOI_USER');
		$appId = $this->input->get_post('app_id');
		$doi_db = $this->load->database('dois', TRUE);
		if (!$appId) throw new Exception ('Invalid App ID');
		$query = $doi_db->where('app_id',$appId)->select('*')->get('doi_client');
		if (!$client_obj = $query->result()) throw new Exception ('Invalid App ID');
		$client_obj = array_pop($client_obj);
		$client_id = $client_obj->client_id;
		$pythonBin = $this->config->item('PYTHON_BIN');
		$doiLinkCheckerScript = $this->config->item('DOI_LINK_CHECKER_SCRIPT');
		$command = escapeshellcmd($pythonBin.' '.$doiLinkCheckerScript.' -c '.$client_id);
		$result = shell_exec($command);
		$message = '<div>'.$result.'</div>';
		$message .=  '<p class="alert">An Email was sent to: ('.$client_obj->client_contact_email.') and an activity was logged containing the result.</p>';
		$data['status'] = 'SUCCESS';
		$data['message'] = $message;
		echo json_encode($data);
	}

    public function allocate_prefixes_for_prod_client(){
        //http://devl.ands.org.au/leo/registry/apps//mydois/allocate_prefixes_for_prod_client
        $response = [];
        $clients_already_updated = [];
        $test_client_count = 0;
        $trusted_clients = $this->clientRepository->getAll();
        $response['trusted_client_count'] = sizeof($trusted_clients);
        $clients_to_update = [];
        foreach ($trusted_clients as $client) {
            $currentPrefix = $this->getTrustedClientActivePrefix($client->client_id);
            if($currentPrefix == null || ($currentPrefix != $this->testPrefix && in_array($currentPrefix, $this->old_prod_prefixes))) {
                $clients_to_update[] = $client->client_id;
            }
            elseif($currentPrefix == $this->testPrefix){
                $test_client_count++;
            }
            elseif(!in_array($currentPrefix, $this->old_prod_prefixes)){
                $clients_already_updated[] = $client->client_id;
            }
        }
        $response['trusted_client_count'] = sizeof($trusted_clients);
        $response['test_client_count'] = $test_client_count;
        $response['clients_already_updated'] = $clients_already_updated;
        $response['prod_client_count'] = sizeof($clients_to_update);

        $min_prefix_pool_size = sizeof($clients_to_update);

        $unallocatedPrefixes = $this->clientRepository->getUnalocatedPrefixes($this->old_prod_prefixes);

        $response['unallocated_prefixes_before'] = sizeof($unallocatedPrefixes);

        if(sizeof($unallocatedPrefixes) < $min_prefix_pool_size) {
            $response['newPrefixes']  = $this->fabricaClient->claimNumberOfUnassignedPrefixes($min_prefix_pool_size - sizeof($unallocatedPrefixes));
        }

        $unallocatedPrefixes = $this->clientRepository->getUnalocatedPrefixes($this->old_prod_prefixes);

        $response['unallocated_prefixes_after'] = sizeof($unallocatedPrefixes);

        if(sizeof($unallocatedPrefixes) < $min_prefix_pool_size) {
            $response['message'] = "Number of Prefixes: " .$unallocatedPrefixes. " is not enough to allocate to :" . $min_prefix_pool_size . " clients.";
        }
        else {

            foreach($clients_to_update as $client_id) {
                $response[$client_id] = [];
                $response[$client_id]['client_id'] = $client_id;
                $unAllocatedPrefix = $this->clientRepository->getOneUnallocatedPrefix($this->old_prod_prefixes);
                if(!in_array($unAllocatedPrefix->prefix_value, $this->old_prod_prefixes)) {
                    // save it in case update on datacite fails
                    $currentPrefix = $this->getTrustedClientActivePrefix($client_id);
                    $trusted_client = $this->clientRepository->getByID($client_id);
                    $response[$client_id]['client'] = $trusted_client;
                    $response[$client_id]['new_prefix'] = $unAllocatedPrefix->prefix_value;
                    $trusted_client->addClientPrefix($unAllocatedPrefix->prefix_value);
                    $this->fabricaClient->updateClientPrefixes($trusted_client);
                    $response[$client_id]['ResponseCodes'] = $this->fabricaClient->responseCode;
                    $response[$client_id]['ErrorMessages'] = $this->fabricaClient->getErrorMessage();
                    $response[$client_id]['Messages'] = $this->fabricaClient->getMessages();
                    if($this->fabricaClient->hasError()){
                        $trusted_client->addClientPrefix($currentPrefix);
                    }

                    $this->fabricaClient->clearMessages();
                }else{
                    $response[$client_id]['ErrorMessages'] = "system reassigned old prefix (".$unAllocatedPrefix.") to client ".$client_id ." UPDATE MANUALLY";
                    $trusted_client->addClientPrefix($unAllocatedPrefix->prefix_value);
                }
            }
        }
        echo json_encode($response);
    }

    function __construct()
    {
        parent::__construct();
        acl_enforce('DOI_USER');

        $database = \ANDS\Util\Config::get('database.dois');
        $this->clientRepository = new ClientRepository(
            $database['hostname'],
            $database['database'],
            $database['username'],
            $database['password']
        );

        $fabricaConfig = \ANDS\Util\Config::get('datacite.fabrica');

        $this->fabricaUrl = $fabricaConfig['url'];
        $this->fabricaClient = new FabricaClient($fabricaConfig['username'],$fabricaConfig['password']);
        $this->fabricaClient->setDataciteUrl($fabricaConfig['api_url']);
        $this->fabricaClient->setClientRepository($this->clientRepository);
    }

}
