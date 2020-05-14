<?php
use ANDS\DOI\DataCiteClient;
use ANDS\DOI\FabricaClient;
use ANDS\DOI\Model\Client as TrustedClient;
use ANDS\DOI\Repository\ClientRepository;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mydois extends MX_Controller {

    /** @var ClientRepository */
    private $clientRepository;

    /** @var FabricaClient */
    private $fabricaClient;

    private $fabricaUrl;

    private $unallocatedPrefixLimit = 5;
// the test prefix every client can have
    private $testPrefix = "10.5072";
    // the old production prefixes as of R28 that shouldn't be assigned to clients
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


    /**
     * mydois/list_trusted
     * List Trusted Client SPA
     * Front end for list trusted doi clients app
     */
    function merge_trusted(){
        acl_enforce('SUPERUSER');
        $this->load->view('merge_clients_index', [
            'title' => 'Merge Trusted Clients',
            'scripts' => ['merge_clients'],
            'js_lib' => ['core', 'dataTables']
        ]);
    }
    /**
     * mydois/update_all_password
     * Update the datacite fabrica repository definition to use the clients shared_secret
     * Front end for update trusted doi clients app
     */
    function update_all_password(){
        acl_enforce('SUPERUSER');
        $this->load->view('update_all_clients_index', [
            'title' => 'Update Trusted Clients',
            'scripts' => ['update_clients'],
            'js_lib' => ['core', 'dataTables']
        ]);
    }
    /**
     *
     * to add or edit a client we allow to populate the drop down with unallocatedPrefixLimit of prefixes
     * starting with the test prefix
     *
     */
    function get_available_prefixes($mode = 'prod'){
        $prefixes = [];
        //we now need to determine if we are retrieving production or test prefixes

        if(isset($_GET['mode']) && $_GET['mode'] == 'test'){
            $mode = 'test';
            $this->fabricaClient->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));
        }else{
            $prefixes[] = $this->testPrefix;
        }

        $this->fabricaClient->syncUnallocatedPrefixes($mode);
        $unallocatedPrefixes = $this->clientRepository->getUnalocatedPrefixes($mode);
        foreach($unallocatedPrefixes as $aPrefix) {
            if(sizeof($prefixes) >= $this->unallocatedPrefixLimit)
                break;
            if(!in_array($aPrefix->prefix_value, $this->old_prod_prefixes))
                $prefixes[] = $aPrefix->prefix_value;
        }
        echo json_encode($prefixes);
    }


    /**
     * get unallocated prefixes from our registry
     * if we have less than unallocatedPrefixLimit then top it up from datacite
     *
     * using the fabrica client to allocate prefixes for future client assignment
     *
     *
     *
     */
    public function fetch_unassigned_prefix()
    {
        $response = [];

        $mode = $_GET["mode"];

        $unallocatedPrefixes = $this->clientRepository->getUnalocatedPrefixes($mode);

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
            $response['newPrefixes'] = $this->fabricaClient->claimNumberOfUnassignedPrefixes($numberofPrefixestoFetch,$mode);

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
     * AJAX entry for mydois/list_trusted
     */
    function update_trusted_clients(){
        echo json_encode($this->updateTrustedClients());
    }
    /**
     * AJAX entry for mydois/created_new_trusted
     */
    function create_clients(){

        $mode = $_GET["mode"];
        echo strtoupper($mode )." system:  </br>";
        echo json_encode($this->createNewClients($mode));
    }
    /**
     * AJAX entry for mydois/update_new_repositories
     */
    function update_new_repositories(){

        $mode = $_GET["mode"];
        echo strtoupper($mode )." system:  </br>";
        echo json_encode($this->updateNewClients($mode));
    }

    function assign_new_prefix(){
        $mode = $_GET["mode"];
        echo strtoupper($mode )." system:  </br>";
        echo json_encode($this->assignNewPrefix($mode));
    }
    /**
     * AJAX entry for mydois/merge_trusted
     * This function will be called once to merge prod and test datacite accounts as part of Release 29
     */
    function merge_trusted_clients(){
        echo json_encode($this->getMergeClients());
    }

    /**
     * TODO refactor to ANDS-DOI-SERVICE functionality
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    private function getTrustedClients(){
        $allClients =  $this->clientRepository->getAll();
        foreach($allClients as $key=>$client){
                $client["url"] = $this->fabricaUrl . "/clients/" . strtolower($client->datacite_symbol);
                $client['domain_list'] = str_replace(",", " ", $this->getTrustedClientDomains($client->client_id));
                $client['datacite_prefix'] = $this->getTrustedClientActivePrefix($client->client_id);
                $client['datacite_test_prefix'] = $this->getTrustedClientActiveTestPrefix($client->client_id);
                $client['not_active_prefixes'] = $this->getTrustedClientNonActivePrefixes($client->client_id);
                $client['not_active_text_prefixes'] = $this->getTrustedClientNonActiveTestPrefixes($client->client_id);
        }

        return $allClients;
    }


    private function updateTrustedClients(){
        $allClients =  $this->clientRepository->getAll();
        foreach($allClients as $key=>$client){
            $client["url"] = $this->fabricaUrl . "/clients/" . strtolower($client->datacite_symbol);
            $client['domain_list'] = str_replace(",", " ", $this->getTrustedClientDomains($client->client_id));
            $client['datacite_prefix'] = $this->getTrustedClientActivePrefix($client->client_id);
            $client['datacite_test_prefix'] = $this->getTrustedClientActiveTestPrefix($client->client_id);
            $client['not_active_prefixes'] = $this->getTrustedClientNonActivePrefixes($client->client_id);
            $client['not_active_text_prefixes'] = $this->getTrustedClientNonActiveTestPrefixes($client->client_id);

            //update the Fabrica repository with new password
            $clientToUpdate = $this->clientRepository->getByID($client->client_id);
            $this->fabricaClient->updateClient($clientToUpdate);
            if ($this->fabricaClient->hasError()) {
                // if error occurred return the result message to the user
                $response['responseCode'] = $this->fabricaClient->responseCode;
                $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
                $response['Messages'] = $this->fabricaClient->getMessages();
                echo json_encode($response);
                exit();
            }
            //update the Fabrica test repository with new password
            $this->fabricaClient->updateClient($clientToUpdate, "test");
            if ($this->fabricaClient->hasError()) {
                // if error occurred return the result message to the user
                $response['responseCode'] = $this->fabricaClient->responseCode;
                $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
                $response['Messages'] = $this->fabricaClient->getMessages();
                echo json_encode($response);
                exit();
            }
        }
        return $allClients;
    }

    private function createNewClients($mode="test"){
        $all_rows = array();
        $consortium_orgs= array();
        $csv_file = fopen("/opt/apps/registry/current/applications/apps/mydois/assets/Datacite_new_orgs_and_repositories.csv", "r");
        $data = fgetcsv($csv_file, 1000, ",");
        while (($data = fgetcsv($csv_file, 1000, ",")) !== FALSE)
        {
            $all_clients[] = $data;
            if(isset($data[2]) && $data[2] != 'FALSE' && $data[2]!='') {
                $consortium_orgs[] = $data[2]."||".$data[3];
            }
        }

       $consortium_orgs = array_unique($consortium_orgs);
        /* we need to create datacite members with the member-type of consortium_organisations for each member of the consortium_orgs list */
        foreach($consortium_orgs as $new_org){
            $new_org1 = explode("||",$new_org);
            $this->fabricaClient->createNewCustodianOrg($new_org1, $mode);
            print("Consortium_organisation " . $new_org1[0] . " created. </br>");
        }

        foreach($all_clients as $client){
            /* Now we create the repositories for the newly created consortium organisations */
            if(isset($client[2]) && $client[2] != 'FALSE' && $client[2]!='') {
                $newRepository = $this->clientRepository->getBySymbol($client[0]);
                $this->fabricaClient->createNewClient($newRepository, $client, $mode);
                print("Repository " . $client[4] . " created with provider " . $client[2] . " Old client symbol  was " . $client[0] . " </br>");
            }
        }

    }

    private function updateNewClients($mode="test"){
        $csv_file = fopen("/opt/apps/registry/current/applications/apps/mydois/assets/Datacite_new_orgs_and_repositories.csv", "r");
        $data = fgetcsv($csv_file, 1000, ",");
        while (($data = fgetcsv($csv_file, 1000, ",")) !== FALSE)
        {
            $all_clients[] = $data;
        }

        foreach($all_clients as $client){
            /* Now we create the repositories for the newly created consortium organisations */
            if(isset($client[2]) && $client[2] != 'FALSE' && $client[2]!='') {
                $newRepository = $this->clientRepository->getBySymbol($client[0]);
                $params['client_id'] = $newRepository->client_id;
                $params['repository_symbol'] = $client[4];
                $params['in_production'] = 1;
                $updatedClient = $this->clientRepository->updateClient($params);
                print("update db for ".$client[0]." by setting the repository symbol to ".$client[4]." and the production flag to true </br>");
            }
        }
    }

    private function assignNewPrefix($mode="test"){
        $csv_file = fopen("/opt/apps/registry/current/applications/apps/mydois/assets/Datacite_new_orgs_and_repositories.csv", "r");
        $data = fgetcsv($csv_file, 1000, ",");
        while (($data = fgetcsv($csv_file, 1000, ",")) !== FALSE)
        {
            $all_clients[] = $data;
        }

        foreach($all_clients as $client){
            /* determine who the consortium org is and what prefixes are available to it, if none than claim one */

            if(isset($client[2]) && $client[2] != 'FALSE' && $client[2]!='' && $client[0] != 'ANDS.C113') {

              /*  if($client[4] != "ARDCX.USQ" && $client[4] != "ARDCX.VICTORIA" && $client[4] != "ARDCX.ARDCTEST" ) {
                    $this->fabricaClient->assignTestPrefix($client);
                } */

                $newRepository = $this->clientRepository->getBySymbol($client[0]);
                $getThePrefix = file_get_contents('http://api.test.datacite.org/repositories/'.$client[4]) ;
                $PrefixArray = json_decode($getThePrefix, true);
                print_pre($PrefixArray["data"]["relationships"]["prefixes"]["data"][0]["id"]);
                $datacite_test_prefix = $PrefixArray["data"]["relationships"]["prefixes"]["data"][0]["id"];
                $newRepository->addClientPrefix($datacite_test_prefix, 'test',true);
                print("Added ".$datacite_test_prefix." to the client_prefixes db for ".$client[0]." , ".$newRepository->client_id);
             }
        }
    }
    private function getMergeClients(){


       // return $allClients with known prod and test matches;

        $doi_db = $this->load->database('dois', TRUE);
        $query = $doi_db->query('SELECT `prod_client`.`client_id`,
	`test_client`.`client_id` as test_client_id,
    `prod_client`.`client_name`,
    `test_client`.`client_name` AS test_client_name,
    `prod_client`.`ip_address`,
    `test_client`.`ip_address` as test_ip_address,
    `prod_client`.`app_id`,
	`test_client`.`app_id` AS test_app_id,
    `prod_client`.`shared_secret`,
    `test_client`.`shared_secret` AS test_shared_secret
FROM dbs_dois.doi_client prod_client, dbs_dois.doi_client test_client 
WHERE prod_client.client_name LIKE SUBSTR(test_client.client_name,7) ');

       if ($query->num_rows() > 0) {
            foreach ($query->result_array() AS $r) {

                //lets set up the new top level domain lists
                $prod_domains = explode(",",$this->getTrustedClientDomains($r['client_id']));
                $test_domains = explode(",",$this->getTrustedClientDomains($r['test_client_id']));
                //need to update client_id with new combined list
                $combined_domains = array_unique(array_merge($prod_domains,$test_domains));
                $r['combined_domain_list'] = trim(implode(", ",$combined_domains),",");
                $r['domain_list'] = str_replace(",", ", ", $this->getTrustedClientDomains($r['client_id']));
                $r['test_domain_list'] = str_replace(",", ", ", $this->getTrustedClientDomains($r['test_client_id']));

                if( trim($r['combined_domain_list']) != trim($r['domain_list'])){
                    //if we need to update datacite with a different top level domain list
                    $client = $this->clientRepository->getByID($r['client_id']);
                    $test_client = $this->clientRepository->getByID($r['test_client_id']);

                    $client->removeClientDomains();
                    $test_client->removeClientDomains();

                    $client->addDomains(str_replace(", ",",",$r['combined_domain_list']));

                    $this->fabricaClient->updateClient($client);
                     // updates the client on datacite
                    if($this->fabricaClient->hasError()){
                       // if error occurred return the result message to the user
                        $response['responseCode'] = $this->fabricaClient->responseCode;
                        $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
                        $response['Messages'] = $this->fabricaClient->getMessages();
                        echo json_encode($response);
                        exit();
                    }

                }

                //set the merged test account to inactive
                $deleted_client = $this->clientRepository->deleteClientById($r['test_client_id']);


                $combined_ip = array_unique(array_merge( explode(",",$r['ip_address']), explode(",",$r['test_ip_address'])));
                $r['ip_address'] = str_replace(",", ", ", $r['ip_address']);
                $r['test_ip_address'] = str_replace(",", ", ", $r['test_ip_address']);
                $r['combined_ip'] = trim(implode(",", $combined_ip),",");

                // need to update client_id with new combined ip list, test_app_id = $r['test_app_id]', test_shared_secret = $r['test_shared_secret]'

               $query2 = $doi_db->query('UPDATE  doi_client SET test_app_id = "'.$r["test_app_id"].'" ,
test_shared_secret = "'.$r["test_shared_secret"].'" ,
ip_address = "'.$r["combined_ip"].'"  WHERE app_id = "'.$r["app_id"].'"');

                //need to update all test dois to the production client_id

                $query3 = $doi_db->query('UPDATE  doi_objects SET client_id = "'.$r["client_id"].'"  WHERE client_id = "'.$r["test_client_id"].'"');
                $r['combined_ip'] = str_replace(",",", ", $r['combined_ip'] );
                $allClients[] = $r;
            }
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
            if($clientPrefix->active && $clientPrefix->prefix != null && $clientPrefix->is_test == 0)
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
    private function getTrustedClientActiveTestPrefix($client_id){
        $client = $this->clientRepository->getByID($client_id);
        if(is_array_empty($client->prefixes))
            return "";
        foreach ($client->prefixes as $clientPrefix) {
            if($clientPrefix->active && $clientPrefix->prefix != null  && $clientPrefix->is_test == 1)
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
            if(!$clientPrefix->active && $client->is_test == 0)
                $notActiveprefixes .= $clientPrefix->prefix->prefix_value.", ";
        }
        return trim($notActiveprefixes, ', "');
    }

    /**
     * TODO refactor to ANDS-DOI-SERVICE functionality
     *
     * @param $client_id
     * @return mixed
     */
    private function getTrustedClientNonActiveTestPrefixes($client_id){
        $client = $this->clientRepository->getByID($client_id);
        $notActiveprefixes = "";
        if(is_array_empty($client->prefixes))
            return "";
        foreach ($client->prefixes as $clientPrefix) {
            if(!$clientPrefix->active  && $client->is_test == 1)
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
		$domainList = trim($posted['domainList']);
        $datacite_prefix = 	trim(urlencode($posted['datacite_prefix']));
        $datacite_test_prefix = 	trim(urlencode($posted['datacite_test_prefix']));
		$shared_secret = trim(urlencode($posted['shared_secret']))	;
        $test_shared_secret = trim(urlencode($posted['test_shared_secret']))	;
        
        // add the client to the repository
        $client = $this->clientRepository->create([
            'ip_address' => $ip,
            'app_id' => sha1($shared_secret.$client_name),
            'test_app_id' => sha1($test_shared_secret.$client_name),
            'client_name' => urldecode($client_name),
            'client_contact_name' => urldecode($client_contact_name),
            'client_contact_email' => urldecode($client_contact_email),
            'shared_secret' => $shared_secret,
            'test_shared_secret' => $test_shared_secret
        ]);
        
        $client->addDomains($domainList);
        $client->addClientPrefix($datacite_prefix, 'prod',true);
        $client->addClientPrefix($datacite_test_prefix, 'test',true);

        //Add the client as a production client
        $this->fabricaClient->addClient($client, 'prod');

        if($this->fabricaClient->hasError()){
            //if error occurred return the result message to the user
            $response['responseCode'] = $this->fabricaClient->responseCode;
            $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
            $response['Messages'] = $this->fabricaClient->getMessages();
            echo json_encode($response);
            exit();
        }

        if($datacite_prefix && $datacite_prefix != $this->testPrefix){

            $this->fabricaClient->updateClientPrefixes($client, 'prod');
            if($this->fabricaClient->hasError()){
                //if error occurred return the result message to the user
                $response['responseCode'] = $this->fabricaClient->responseCode;
                $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
                $response['Messages'] = $this->fabricaClient->getMessages();
                echo json_encode($response);
                exit();
            }
        }


        //Add the client as a test client
        $this->fabricaClient->addClient($client, 'test');

        if($this->fabricaClient->hasError()){
            //if error occurred return the result message to the user
            $response['responseCode'] = $this->fabricaClient->responseCode;
            $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
            $response['Messages'] = $this->fabricaClient->getMessages();
            echo json_encode($response);
            exit();
        }

        if($datacite_test_prefix && $datacite_test_prefix != $this->testPrefix){

            $this->fabricaClient->updateClientPrefixes($client, 'test');
            if($this->fabricaClient->hasError()){
                //if error occurred return the result message to the user
                $response['responseCode'] = $this->fabricaClient->responseCode;
                $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
                $response['Messages'] = $this->fabricaClient->getMessages();
                echo json_encode($response);
                exit();
            }
        }


        // we should get here only if no error occurred during the update
        // return the code if 200 or 201
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
        $response['datacite_test_prefix'] = $this->getTrustedClientActiveTestPrefix($client_id);
		$response['available_prefixes'] = $this->getAvailablePrefixesForClient($client_id);
        $response['available_test_prefixes'] = $this->getAvailableTestPrefixesForClient($client_id);
		echo json_encode($response);
	}

    /**
     * TODO refactor to ANDS-DOI-SERVICE functionality
     *
     * @param $client_id
     * @return array
     * gets unallocatedPrefixLimit list of prefixes to give to clients
     * 
     */
    private function getAvailablePrefixesForClient($client_id)
    {

        $unallocatedPrefixes = $this->clientRepository->getUnalocatedPrefixes('prod');
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
     * TODO refactor to ANDS-DOI-SERVICE functionality
     *
     * @param $client_id
     * @return array
     * gets unallocatedPrefixLimit list of prefixes to give to clients
     *
     */
    private function getAvailableTestPrefixesForClient($client_id)
    {

        $unallocatedPrefixes = $this->clientRepository->getUnalocatedPrefixes('test');
        $prefixes = [];

        if ($ownPrefix = $this->getTrustedClientActiveTestPrefix($client_id)) {
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
     * for committing a change to a client
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
		$datacite_test_prefix = trim($posted['datacite_test_prefix']);
		$shared_secret = trim($posted['shared_secret']);
        $test_shared_secret = trim($posted['test_shared_secret']);

        $clientdata = [
            'client_id' => $client_id,
            'ip_address' =>  $ip,
            'client_name'  => $client_name,
            'client_contact_name' => $client_contact_name,
            'client_contact_email' => $client_contact_email,
            'shared_secret' => $shared_secret,
            'test_shared_secret' => $test_shared_secret
        ];
        // update the client metadata
        $this->clientRepository->updateClient($clientdata);
        $client = $this->clientRepository->getByID($client_id);
        
        $client->removeClientDomains();
        $client->addDomains($domainList);

        $hasPrefix = $client->hasPrefix($datacite_prefix);
        $hasTestPrefix = $client->hasPrefix($datacite_test_prefix);
        // adds or sets the given prefix to active
        $client->addClientPrefix($datacite_prefix, 'prod',true);
        $client->addClientPrefix($datacite_test_prefix, 'test',true);

            // updates the client on datacite prod if the client is a production client
        if($client->in_production == 1) {
            $this->fabricaClient->updateClient($client, 'prod');

            if($this->fabricaClient->hasError()){
                //if error occurred return the result message to the user
                $response['responseCode'] = $this->fabricaClient->responseCode;
                $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
                $response['Messages'] = $this->fabricaClient->getMessages();
                echo json_encode($this->fabricaClient);
                echo json_encode($response);
                exit();
            }
        }

        // if new production prefix is assigned to client
        // update client prefix on datacite

    /*    if($datacite_prefix && $datacite_prefix != $this->testPrefix && !$hasPrefix){
            $this->fabricaClient->updateClientPrefixes($client,'prod');
            if($this->fabricaClient->hasError()){
                //if error occurred return the result message to the user
                $response['responseCode'] = $this->fabricaClient->responseCode;
                $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
                $response['Messages'] = $this->fabricaClient->getMessages();
                exit();
            }
        } */
        // updates the client on datacite test
        $this->fabricaClient->updateClient($client,'test');


        if($this->fabricaClient->hasError()){
            //if error occurred return the result message to the user
            $response['responseCode'] = $this->fabricaClient->responseCode;
            $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
            $response['Messages'] = $this->fabricaClient->getMessages();
            echo json_encode($this->fabricaClient);
            echo json_encode($response);
            exit();
        }

        // if new test prefix is assigned to client
        // update client prefix on datacite test

        if($datacite_test_prefix && $datacite_test_prefix != $this->testPrefix && !$hasTestPrefix){
            $this->fabricaClient->updateClientPrefixes($client,'test');
            if($this->fabricaClient->hasError()){
                //if error occurred return the result message to the user
                $response['responseCode'] = $this->fabricaClient->responseCode;
                $response['errorMessages'] = $this->fabricaClient->getErrorMessage();
                $response['Messages'] = $this->fabricaClient->getMessages();
                var_dump($this->fabricaClient);
              //  echo json_encode($response);
                exit();
            }
        }
        // we should get here only if no error occurred during the update
        // return the code if 200 or 201
 
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
		$pythonBin =  ANDS\Util\Config::get('app.python_bin');
		$doiLinkCheckerScript = ANDS\Util\Config::get('app.doi_link_checker_script');
		$command = escapeshellcmd($pythonBin.' '.$doiLinkCheckerScript.' -c '.$client_id);
		$result = shell_exec($command);
		$message = '<div>'.$result.'</div>';
		$message .=  '<p class="alert">An Email was sent to: ('.$client_obj->client_contact_email.') and an activity was logged containing the result.</p>';
		$data['status'] = 'SUCCESS';
		$data['message'] = $message;
		echo json_encode($data);
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
            $database['password'],
            $database['port']
        );

        $fabricaConfig = \ANDS\Util\Config::get('datacite.fabrica');

        $this->fabricaUrl = $fabricaConfig['url'];
        $this->fabricaClient = new FabricaClient($fabricaConfig['username'],$fabricaConfig['password'],$fabricaConfig['testPassword']);
        $this->fabricaClient->setDataciteUrl($fabricaConfig['api_url']);
        $this->fabricaClient->setClientRepository($this->clientRepository);
    }

}
