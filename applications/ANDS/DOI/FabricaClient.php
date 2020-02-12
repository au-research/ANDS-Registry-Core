<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 4/6/18
 * Time: 11:22 AM
 */

namespace ANDS\DOI;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Model\Prefix as Prefix;
use ANDS\DOI\Model\Client as TrustedClient;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;


class FabricaClient implements DataCiteClient
{

    private $username;
    private $password;
    private $testPassword;
    private $dataciteUrl = 'https://api.datacite.org/';

    private $errors = array();
    private $messages = array();
    public $responseCode;
    /** @var  ClientRepository */
    private $clientRepository;

    /** @var GuzzleClient */
    private $http;

    /**
     * DataCiteClient constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password, $testPassword)
    {
        $this->username = $username;
        $this->password = $password;
        $this->testPassword = $testPassword;
    }

    /**
     * get the URL content of a DOI by ID
     * @param $doiId
     * @return mixed
     */
    public function get($doiId)
    {
        return "not implemented yet";
    }


    /**
     * get list of a client's dois
     * @param client_name
     * @return mixed
     */
    public function getXML($doiId, $client)
    {
        $headers = [
            'Content-type' => 'application/xhtml+xml; charset=utf-8',
            'Accept' => 'application/xhtml+xml',
            'Authorization' => 'Basic ' . base64_encode($client->username . ":" . $client->password),
        ];
        $response = "";
        $ret = "we have an issue";
        try {
            $response = $this->http->get($doiId, $headers)->send();
            $this->responseCode = $response->getStatusCode();
            $ret = $response->getBody();
            dd($ret);
        } catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        } catch (ServerErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        dd($response);
        return $ret;
    }

    /**
     * delete a DOI (cannot delete a DOI that has been minted in the handle system
     * @param $doiId
     * @return null
     */
    public function deleteDOI($doiId,$client)
    {
        $headers = [
            'Content-type' => 'application/json; charset=utf-8',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($client->username .":". $client->password),
        ];
        $response = "";
        try {
            $response = $this->http->delete("dois/".$doiId, $headers)->send();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
    }

    /**
     * get list of a client's dois
     * @param client_name
     * @return mixed
     */


    public function getDOIs($mode = 'prod')
    {
        $response = "";
        $ret = "";
        $return = '';
        if($mode == 'test'){
            $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));
        }
        try{
            $response = $this->http->get("/dois?client-id=".strtolower($this->username)."&page[cursor]=1&page[size]=1000")->send();
            $this->responseCode = $response->getStatusCode();
            $ret = $response->json();
            foreach($ret['data'] as $doi){
                $return[] = strtoupper($doi['id']);
            }
            $page= 2;
            $last_page = $ret['meta']['totalPages'] + 1;

            for($i = $page;$i < $last_page;$i++){
                $response = $this->http->get($ret['links']['next'])->send();
                $ret = $response->json();
                foreach($ret['data'] as $doi){
                    $return[] = strtoupper($doi['id']);
                }
            }
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;

        if(is_array($return)) {
            $return = array_unique($return);
        }

        if(gettype($ret)!='string'){
            return $return;
        }else{
            return "Client not found";
        }
    }
    /**
     * get list of a client's dois
     * @param client_name
     * @return mixed
     */


    public function getDOIURLs()
    {
        $response = "";
        $ret = "";
        $return = '';
        try{
            $response = $this->http->get("/dois?client-id=".strtolower($this->username)."&page[size]=1000&page[cursor]=1")->send();

            $this->responseCode = $response->getStatusCode();
            $ret = $response->json();

            foreach($ret['data'] as $doi){
                if($doi['id'] == "10.4225/08/5ab330902ddb8"){
                    dd($doi);

                }
                $response2 = $this->http->get("/dois/".$doi['id'])->send();
                $ret2 = $response2->json();
                $return[strtoupper($doi['id'])] = Array('url' => $ret2["data"]["attributes"]["url"], 'xml' => $ret2["data"]["attributes"]["xml"]);
            }
            $page= 2;
            $last_page = $ret['meta']['totalPages'] + 1;
            for($i = $page;$i < $last_page;$i++){
                $response = $this->http->get($ret['links']['next'])->send();
                $ret = $response->json();
                foreach($ret['data'] as $doi){
                    $response2 = $this->http->get("/dois/".$doi['id'])->send();
                    $ret2 = $response2->json();
                    if(isset($ret2["data"]["attributes"]["xml"])) {
                        $return[strtoupper($doi['id'])] = Array('url' => $ret2["data"]["attributes"]["url"], 'xml' => $ret2["data"]["attributes"]["xml"]);
                    }
                }
            }
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        if(gettype($ret)!='string'){
            return $return;
        }else{
            return "Client not found";
        }
    }

    /**
     * get the Metadata of a DOI by ID
     * @param $doiId
     * @return mixed
     */
    public function getMetadata($doiId)
    {
        return "not implemented yet";
    }


    public function mint($doiId, $doiUrl, $xmlBody = false)
    {
        return "not implemented yet";
    }

    /**
     * Update XML
     * @param bool|false $xmlBody
     * @return mixed
     */
    public function update($xmlBody = false)
    {
        return "not implemented yet";
    }

    /**
     * UpdateURL
     * @param string $doiUrl, string $doiId
     * @return bool
     */

    public function updateURL($doiId,$doiUrl)
    {
        return "not implemented yet";
    }


    //Don't have an activate function...updating the xml activates a deactivated doi...
    public function activate($xmlBody = false)
    {
        return "not implemented yet";
    }

    public function deActivate($doiId)
    {
        return "not implemented yet";
    }

    /**
     * @return string
     */
    public function getDataciteUrl()
    {
        return $this->dataciteUrl;
    }

    /**
     * @param string $dataciteUrl
     * @return $this
     */
    public function setDataciteUrl($dataciteUrl)
    {
        $this->dataciteUrl = $dataciteUrl;
        if(strpos($dataciteUrl,'test')){
            $this->http = new GuzzleClient($this->dataciteUrl, [
                'auth' => [ $this->username, $this->testPassword ]
            ]);
        }else {
            $this->http = new GuzzleClient($this->dataciteUrl, [
                'auth' => [$this->username, $this->password]
            ]);
        }
        return $this;
    }


    /**
     * @param ClientRepository $clientRepository
     */
    public function setClientRepository($clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * @return mixed
     */
    public function getClientRepository()
    {
        return $this->clientRepository;
    }


    private function log($content, $context = "info")
    {
        if ($content === "" || !$content) {
            return;
        }
        if ($context == "error") {
            $this->errors[] = $content;
        } else {
            if ($context == "info") {
                $this->messages[] = $content;
            }
        }
    }

    public function getResponse()
    {
        return [
            'errors' => $this->getErrors(),
            'messages' => $this->getMessages()
        ];
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return String
     */
    public function getErrorMessage()
    {
        $errorMsg = "";
        if(sizeof($this->errors) > 0){
            foreach ($this->errors as $e){
                if(is_array($e)){
                    foreach ($e as $message)
                    {
                        $errorMsg .= isset($message[0]['source']) ? $message[0]['source']." : " : "";
                        $errorMsg .= isset($message[0]['status']) ? $message[0]['status']." : " : "";
                        $errorMsg .= isset($message[0]['title']) ? $message[0]['title'] : " ";
                    }
                }else{
                    $errorMsg = $e;
                }

            }
        }
        return $errorMsg;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return count($this->getErrors()) > 0 ? true : false;
    }

    /**
     * clears messages
     * this function should be called after each request to avoid messages being combined
     */
    public function clearMessages()
    {
        $this->responseCode = 0;
        $this->errors = [];
        $this->messages = [];
    }

    /**
     * @param TrustedClient $client
     * adds a new client to DataCite using a POST request
     */
    public function addClient(TrustedClient $client, $mode='prod')
    {
        // clientinfo is fabrica's JSON representation of a client metadata

        if($mode == 'test'){
            $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));
            $headers = [
                'Content-type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->testPassword),
            ];
        } else{
            $headers = [
                'Content-type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->password),
            ];
        }

        $clientInfo = $this->getClientInfo($client,$mode);
        $response = "";
        $request = $this->http->post('/clients', $headers, $clientInfo);

        try {
            $response = $request->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors = $e->getResponse()->json();
            $this->responseCode = $e->getResponse()->getStatusCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getResponse()->json();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
    }

    /*
     * @param Trustedclient
     * same as addclient but PATCH request to url containing the datacite_symbol of the client
     *
     */
    public function updateClient(TrustedClient $client,$mode='prod')
    {
        $clientInfo = $this->getClientInfo($client,$mode);
        if($mode == 'test'){
            $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));
            $headers = [
                'Content-type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->testPassword),
            ];
        } else{
            $headers = [
                'Content-type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->password),
            ];
        }
        $response = "";
        $request = $this->http->patch('/clients/'.$client->datacite_symbol, $headers, $clientInfo);
        try {
            $response = $request->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
    }

    /**
     * @param TrustedClient $client
     * client prefixes added in a separate request
     * make sure the request is not called if prefix already given to the client at datacite
     * or it will result a 500 error response
     */
    public function updateClientPrefixes(TrustedClient $client, $mode = 'prod')
    {

        // a JSON representation of the client's prefix relationship
        $clientInfo = $this->getClientPrefixInfo($client, $mode);

        if(!$clientInfo){
            $this->messages[] = "No Active Prefix assigned!";
            return;
        }

        if($mode == 'test'){
            $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));
            $headers = [
                'Content-type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->testPassword),
            ];
        }else{
            $headers = [
                'Content-type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->password),
            ];
        }

        $response = "";

        $request = $this->http->post('/client-prefixes', $headers, $clientInfo);

        try {
            $response = $request->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
    }

    /**
     * @param TrustedClient $client
     * a simple DELETE request containing the datacite-symbol of the client
     * it was tested and it works but we shouldn't delete a client unless it was created in error
     * datacite keeps client symbols (datacite's client's primary key) even after deletion.
     */
    public function deleteClient(TrustedClient $client)
    {
        $response= "";
        $headers = [
            'Content-type' => 'application/json; charset=utf-8',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->password),
        ];
        try {
            $response = $this->http->delete('/clients/'.$client->datacite_symbol, $headers)->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
    }

    /**
     * @param $datacite_symbol
     * @return array|bool|float|int|string
     * we rely on our Database for this data
     * this endpint is not used but tested and can be used to sync datacite information
     */
    public function getClientByDataCiteSymbol($datacite_symbol,$mode = 'prod')
    {
        $response = "";
        if($mode == 'test'){
            $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));
        }
        try{
            $response = $this->http->get("/clients/$datacite_symbol")->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();
    }

    /**
     * @param $datacite_symbol
     * @return array|bool|float|int|string
     * we rely on our Database for this data
     * not used but can return the prefixes a trusted client is assigned to at datacite
     */
    public function getClientPrefixesByDataciteSymbol($datacite_symbol,$mode='prod'){
        $response = "";
        if($mode == 'test'){
            $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));
        }
        try{
            $response = $this->http->get("/client-prefixes?client-id=".$datacite_symbol)->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();
    }

    /**
     * @return array|bool|float|int|string
     * return all of our clients and their details from datacite
     * also not used
     * we rely on our Database for this data
     */
    public function getClients($mode = 'prod')
    {
        $response = "";
        if($mode == 'test') {
            $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));
        }
        try{
            $response = $this->http->get('/clients', [], ["query" => ['provider-id'=>'ands']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();
    }

    /**
     * @return array|bool|float|int|string
     * return all of our clients and their details from datacite
     * also not used
     * we rely on our Database for this data
     */
    public function syncProdTestClients()
    {
        //get the list of current prod clients
        $response = "";
        $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_URL"));
        try{
            $response = $this->http->get('/clients', [], ["query" => ['provider-id'=>'ands','page[size]'=>1000]])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $centres = $response->json();

        $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));

        //check if the prod client is an old test client or if it was a manually minted services account
        foreach($centres['data'] as $centre){
           if(strpos($centre['attributes']['name'],'Test:') !== 0
               && $centre['attributes']['symbol'] != 'ANDS.C190'
               && $centre['attributes']['symbol'] != 'ANDS.C189'
               && $centre['attributes']['symbol'] != 'ANDS.C201'
               && $centre['attributes']['symbol'] != 'ANDS.TESTC163'
           ) {
               //see if the client definition currently exists on fabrica test
               try {
                    $response = $this->http->get('/clients/' . $centre['attributes']['symbol'])->send();
                    $this->responseCode = $response->getStatusCode();
                } catch (ClientErrorResponseException $e) {
                    $this->errors[] = $e->getMessage();
                    $this->responseCode = $e->getResponse()->getStatusCode();
                } catch (ServerErrorResponseException $e) {
                    $this->errors[] = $e->getMessage();
                    $this->responseCode = $e->getCode();
                }
                if ($this->responseCode == 404) {
                    // If the client doesn't exist then add it to the test fabrica'
                    $trustedClient = $this->clientRepository->getBySymbol($centre['attributes']['symbol']);
                    if($trustedClient) {
                        $this->addClient($trustedClient, 'test');
                    }
                }else{
                    // If the client does exist then update it on the test fabrica'
                    $trustedClient = $this->clientRepository->getBySymbol($centre['attributes']['symbol']);
                    if($trustedClient) {
                        $this->updateClient($trustedClient, 'test');
                    }
                }
           }
        }
        $this->messages[] = $response;
        return $response->json();
    }

    /**
     * @return array
     * return prefixes assigned to ANDS that is not allocated to any clients
     * is used in loading the available prefixes in our Database
     */
    public function syncUnallocatedPrefixes($mode = 'prod'){
        $newPrefixes = [];
        if($mode == 'test'){
            $is_test = 1;
        }else{
            $is_test = 0;
        }
        $result = $this->getUnalocatedPrefixes($mode);
        foreach($result['data'] as $data){

            $pValue = $data['relationships']['prefix']['data']['id'];
            $newPrefix = array("prefix_value" => $pValue,
                "datacite_id" => $data['id'],
                "created" => $data['attributes']['created'],
                "is_test" => $is_test);
            $this->clientRepository->addOrUpdatePrefix($newPrefix);
            $newPrefixes[] = $pValue;
        }
        return $newPrefixes;
    }

    /**
     * @return array
     *
     * also not used
     */
    public function syncProviderPrefixes($mode='prod'){
        $newPrefixes = [];
        if($mode == 'test'){
            $is_test = 1;
        }else{
            $is_test = 0;
        }
        $result = $this->getProviderPrefixes($mode);
        foreach($result['data'] as $data){

            $pValue = $data['relationships']['prefix']['data']['id'];
            $newPrefix = array("prefix_value" => $pValue,
                "datacite_id" => $data['id'],
                "created" => $data['attributes']['created'],
                "is_test"=>$is_test);
            $this->clientRepository->addOrUpdatePrefix($newPrefix);
            $newPrefixes[] = $pValue;
        }
        return $newPrefixes;
    }

    /**
     * @return array|bool|float|int|string
     *
     * NOT used but can have future usage if syncing prefixes from datacite ever gets implemented
     * return ALL prefixes ANDS owns
     */
    public function getProviderPrefixes($mode='prod')
    {
        $response = "";
        if($mode == 'test'){
            $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));
        }
        try {
            $response = $this->http->get('/provider-prefixes',[], ["query" => ['provider-id'=>'ands']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();

    }

    /**
     * @param TrustedClient $client
     * @return array|bool|float|int|string
     * also not used currently
     * return ALL prefixes a client is assigned to
     */
    public function getClientPrefixes(TrustedClient $client)
    {
        try {
            $response = $this->http->get('/provider-prefixes',[], ["query" =>
                ['client_id'=>$client->datacite_symbol,
                    'provider-id'=>'ands']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();
    }


    /**
     * @param $prefix_value
     * @return mixed
     * claim ownership of prefixes for future usage
     */
    private function claimUnassignedPrefix($prefix_value,$mode = 'prod'){

        $prefixInfo = $this->getPrefixInfo($prefix_value);

        if($mode == 'test'){
            $is_test = 1;
            $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));
            $headers = [
                'Content-type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->testPassword),
            ];
        } else{
            $is_test = 0;
            $headers = [
                'Content-type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->username .":". $this->password),
            ];
        }
        $response = "";
        try {
            $response = $this->http->post('/provider-prefixes', $headers, $prefixInfo)->send();
            $result = $response->json();
            $this->responseCode = $response->getStatusCode();
            if($this->responseCode == 201){
                $newPrefix = array("prefix_value" => $prefix_value,
                    "datacite_id" => $result['data']['id'],
                    "created" => $result['data']['attributes']['created'],
                    "is_test" => $is_test);
                // add the prefix to our registry if successfully claimed
                $this->clientRepository->addOrUpdatePrefix($newPrefix);
            }
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $prefix_value;
    }


    /**
     * @param int $count
     * @return array
     * used to claim prefixes for new trusted clients if we are low or have none
     *
     */
    public function claimNumberOfUnassignedPrefixes($count = 3, $mode = 'prod'){
        // finds all unassigned prefixes on Fabrica
        $unallocatedPrefixes = $this->getUnAssignedPrefixes($mode);
        $newPrefixes = [];

        foreach($unallocatedPrefixes['data'] as $prefix)
        {
            // claim only the required number of prefixes
            $newPrefixes[] = $this->claimUnassignedPrefix($prefix['id'],$mode);
            if(--$count == 0)
                break;
        }
        return $newPrefixes;
    }
    /*
     *
     Unassigned Prefix means that a Prefix is not given to any Allocator (eg ANDS) on DataCite
     *
     */

    /**
     * @return array|bool|float|int|string
     * get information for all unassigned prefixes from Fabrica
     * that can be claimed by allocators such as ANDS
     */
    public function getUnAssignedPrefixes($mode = 'prod')
    {

        $response = "";
        if($mode == 'test'){
            $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));
        }
        try {
            $response = $this->http->get('/prefixes',[], ["query" => ['state'=>'unassigned']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();
    }

    /*
     *
     UnAllocated Prefix means that a Prefix is taken by ANDS but not assigned to datacenters eg one of our trusted client
     *
     */

    /**
     * @return array|bool|float|int|string
     *
     * get the information of all claimed but unallocated prefixes from Fabrica
     * this is used to store the prefix metadata in our database
     * the prefixes then picked up by the registry to populate the drop down of prefixes
     * when new client is created or existing ones are modified
     *
     */
    public function getUnalocatedPrefixes($mode = 'prod')
    {
        if($mode == 'test'){
            $this->setDataciteUrl(getenv("DATACITE_FABRICA_API_TEST_URL"));
        }
        $response = "";
        try {
            $response =  $this->http->get('/provider-prefixes',[], ["query" => ['provider-id'=>'ands','state'=>'without-client']])->send();
            $this->responseCode = $response->getStatusCode();
        }
        catch (ClientErrorResponseException $e) {
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        catch (ServerErrorResponseException $e){
            $this->errors[] = $e->getMessage();
            $this->responseCode = $e->getCode();
        }
        $this->messages[] = $response;
        return $response->json();
    }


    /*
     *
     *
     * The following functions are used to generate client information that is sent to datacite
     *
     *
     */




    /**
     * @param TrustedClient $client
     * generates a JSON representation of a trusted client
     * @return string
     */
    public function getClientInfo(TrustedClient $client , $mode='prod')
    {
        if($mode == 'test'){
            $prefixes = $this->getTestPrefixes($client);
            $passwordInput = $client->test_shared_secret;
        }else {
            $prefixes = $this->getPrefixes($client);
            $passwordInput = $client->shared_secret;
        }
        $attributes = [
            "name" => $client->client_name,
            "symbol" => $client->datacite_symbol,
            "domains" => $this->getClientDomains($client),
            "isActive" => true,
            "contactName" => $client->client_contact_name,
            "contactEmail" => getenv("DATACITE_CONTACT_EMAIL"),
            "passwordInput" => $passwordInput
        ];
        $provider = ["data" => ["type" => "providers",
            "id" => "ands"]];

        $relationships = ["provider" => $provider, "prefixes" => $prefixes];
        $clientInfo = ["data" => ["attributes" => $attributes, "relationships" => $relationships, "type" => "client"]];
        //var_dump($clientInfo);
        return json_encode($clientInfo);
    }

    /**
     * @param TrustedClient $client
     * generates a JSON representation of a client and it's active prefix
     * note: only active prefix since datacite
     * rejects adding prefixes with a 500 response if prefix already given to the client
     * @return string
     */
    public function getClientPrefixInfo(TrustedClient $tClient, $mode = 'prod')
    {
        $attributes = ["created" => null];
        $client = ["data" => ["type" => "clients",
            "id" => strtolower($tClient->datacite_symbol)]];
        $prefix = $this->getActivePrefix($tClient,$mode);
        if(!$prefix){
            return false;
        }
        $relationships = ["client" => $client, "prefix" => $prefix];
        $clientInfo = ["data" => ["attributes" => $attributes, "relationships" => $relationships, "type" => "client-prefixes"]];
        return json_encode($clientInfo);
    }


    public function getPrefixInfo($prefix_value){
        $attributes = ["created" => null,];
        $provider = ["data" => ["type" => "providers", "id" => "ands"]];
        $prefix = ["data" => ["type" => "prefixes", "id" => $prefix_value]];
        $relationships = ["provider" => $provider, "prefix" => $prefix];
        $prefixInfo = ["data" => ["attributes" => $attributes, "relationships" => $relationships, "type" => "provider-prefixes"]];
        return json_encode($prefixInfo);
    }


    /**
     * @param TrustedClient $client
     * @return string
     * returns a comma separated string of the client's domains
     *
     */
    public function getClientDomains(TrustedClient $client){
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
     * @param TrustedClient $client
     * @return array returns all prefixes of the given client
     */
    public function getPrefixes(TrustedClient $client){
        $prefixes = array();
        foreach ($client->prefixes as $clientPrefix) {
            if($clientPrefix->prefix->is_test == 0) {
                $prefixes[] = array("id" => trim($clientPrefix->prefix->prefix_value, "/"),
                    "type" => "prefixes");
            }
        }
        return array("data" => $prefixes);
    }

    /**
     * @param TrustedClient $client
     * @return array returns all test prefixes of the given client
     */
    public function getTestPrefixes(TrustedClient $client){
       $prefixes = array();
        foreach ($client->prefixes as $clientPrefix) {
            if($clientPrefix->is_test == 1 && $clientPrefix->active == 1) {
                $prefixes[] = array("id" => trim($clientPrefix->prefix->prefix_value, "/"),
                    "type" => "prefixes");
            }
        }
        return array("data" => $prefixes);
    }

    /**
     * @param TrustedClient $client
     * @return array returns the active prefix of the given client
     */
    public function getActivePrefix(TrustedClient $client, $mode='prod'){
        if($mode == 'test'){
            $is_test = 1;
        }else{
            $is_test = 0;
        }
        foreach ($client->prefixes as $clientPrefix) {
            if($clientPrefix->active & $clientPrefix->is_test == $is_test)
                return array("data" => array("type" => "prefixes","id" => $clientPrefix->prefix->prefix_value));
        }
    }


}