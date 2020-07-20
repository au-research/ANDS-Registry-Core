<?php

namespace ANDS\DOI;

use ANDS\DOI\Model\Client;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Repository\DoiRepository;
use ANDS\DOI\Validator\URLValidator;
use ANDS\DOI\Validator\XMLValidator;

/**
 * ANDS DOI Service Provider
 *
 * Class DOIServiceProvider
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ANDS\DOI\Repository
 */
class DOIServiceProvider
{

    private $clientRepo = null;
    private $doiRepo = null;
    private $dataciteClient = null;
    private $authenticatedClient = null;
    private $response = null;

    /**
     * @var string
     */
    protected static $globalTestPrefix = "10.5072";

    /**
     * DOIServiceProvider constructor.
     * @param ClientRepository $clientRespository
     * @param DoiRepository $doiRespository
     * @param DataCiteClient $dataciteClient
     */
    public function __construct(
        ClientRepository $clientRespository,
        DoiRepository $doiRespository,
        DataCiteClient $dataciteClient
    ) {
        $this->clientRepo = $clientRespository;
        $this->doiRepo = $doiRespository;
        $this->dataciteClient = $dataciteClient;
    }

    /**
     * Authenticate a client
     *
     * @param $appID
     * @param null $sharedSecret
     * @param null $ipAddress
     * @param bool $manual
     * @return bool
     */
    public function authenticate(
        $appID,
        $sharedSecret = null,
        $ipAddress = null,
        $manual = false
    ) {

        // set app_id before trying to authenticate for logging and report purpose
        $this->setResponse('app_id', $appID);

        // attempt authentication
        $client = $this->clientRepo->authenticate($appID, $sharedSecret,
            $ipAddress, $manual);

        // client is authenticated
        if ($client) {
            $this->setAuthenticatedClient($client);
            return true;
        }

        // client is not authenticated
        $this->setResponse('responsecode', 'MT009');
        $this->setResponse('verbosemessage', $this->clientRepo->getMessage());
        $this->clientRepo->setMessage(null);
        return false;
    }

    /**
     * @return Client
     */
    public function getAuthenticatedClient()
    {
        return $this->authenticatedClient;
    }

    /**
     * Setting the current authenticated client for this object
     *
     * @param $client
     * @return $this
     */
    public function setAuthenticatedClient($client)
    {
        $this->authenticatedClient = $client;
        return $this;
    }

    /**
     * Returns if a client is authenticated
     *
     * @return bool
     */
    public function isClientAuthenticated()
    {
        return $this->getAuthenticatedClient() === null ? false : true;
    }

    /**
     * Returns if a client is authenticated
     *
     * @param $doiValue
     * @param null $client_id
     * @return bool
     */
    public function isDoiAuthenticatedClients($doiValue, $client_id = null)
    {
        if ($client_id === null) {
            return false;
        }

        $client = $this->getAuthenticatedClient();

        if ($client->client_id != $client_id) {
            return false;
        }

        // everyone has access to test prefix
        if (strpos($doiValue, DOIServiceProvider::$globalTestPrefix) === 0) {
            return true;
        }

        // check if the client owns the prefix
        foreach ($client->prefixes as $clientPrefix) {
            if (strpos($doiValue, $clientPrefix->prefix->prefix_value) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mint a new DOI
     *
     * @param $url
     * @param $xml
     * @return bool
     */
    public function mint($url, $xml, $manual = false)
    {

        // @todo event handler, message
        if (!$this->isClientAuthenticated()) {
            $this->setResponse("responsecode", "MT009");
            return false;
        }

        // Validate URL and URL Domain
        $this->setResponse('url', $url);
        $validDomain = URLValidator::validDomains(
            $url, $this->getAuthenticatedClient()->domains
        );
        if (!$validDomain) {
            $this->setResponse("responsecode", "MT014");
            return false;
        }

        // construct DOI 

        if($manual===true){
            $doiValue = XMLValidator::getDOIValue($xml);
        }else{
            $doiValue = $this->getNewDOI();
            // replaced doiValue
            $xml = XMLValidator::replaceDOIValue($doiValue, $xml);
        }

        $this->setResponse('doi', $doiValue);

        // validation on the DOIValue
        // Validate xml
        if($this->validateXML($xml) === false){
            $this->setResponse('responsecode', 'MT006');
            return false;
        }

        //update the database DOIRepository

        $doi = $this->insertNewDOI($doiValue,$xml,$url);

        // mint using dataciteClient

        $result = $this->dataciteClient->mint($doiValue, $url, $xml);

        if ($result === true) {
            $this->setResponse('responsecode', 'MT001');
            $this->doiRepo->doiUpdate($doi, array('status'=>'ACTIVE'));
        } else {
            $this->setResponse('responsecode', 'MT005');
            $this->setResponse('verbosemessage', array_first($this->dataciteClient->getErrors()));
        }

        return $result;
    }

    /**
     * Returns true if xml is datacite valid else false and sets error
     *
     * @param $xml
     * @return bool
     */
    public function validateXML($xml)
    {
        $xmlValidator = new XMLValidator();
        $result = $xmlValidator->validateSchemaVersion($xml);
        if ($result === false) {
            $this->setResponse("verbosemessage", $xmlValidator->getValidationMessage());
            return false;
        }
        return true;
    }

    /**
     * Returns a new DOI for the currently existing authenticated client
     * if client has no active prefix it probably means it's a test client
     *
     * @return string
     */
    public function getNewDOI()
    {
        // get the first active prefix for this authenticated client
        $prefix = "";
        $testStr = "";

        $client = $this->getAuthenticatedClient();


        //if this client is not in test mode grab their production prefix
        if(sizeof($client->prefixes) > 0 && $client->mode !== 'test'){
            foreach ($client->prefixes as $clientPrefix) {
                if($clientPrefix->active && $clientPrefix->is_test == 0) {
                    $prefix = $clientPrefix->prefix->prefix_value;
                    break;
                }
            }
        }
        if(sizeof($client->prefixes) > 0 && $client->mode == 'test'){
            foreach ($client->prefixes as $clientPrefix) {
                if($clientPrefix->active && $clientPrefix->is_test == 1) {
                    $prefix = $clientPrefix->prefix->prefix_value;
                    $testStr =  "TEST_DOI_";
                    break;
                }
            }
        }
        $prefix = ends_with($prefix, '/') ? $prefix : $prefix .'/';

        $doiValue = uniqid();

        return $prefix . $testStr . $doiValue;
    }

    public function insertNewDOI($doiValue,$xml,$url){
        $doiXML = new \DOMDocument();
        $doiXML->loadXML($xml);
        $doiattributes = array();

        $publisher = $doiXML->getElementsByTagName('publisher');
        $publication_year = $doiXML->getElementsByTagName('publicationYear');
        $doiattributes['doi_id'] = $doiValue;
        $doiattributes['publisher'] = $publisher->item(0)->nodeValue;
        $doiattributes['publication_year'] = $publication_year->item(0)->nodeValue;
        $doiattributes['status'] = 'REQUESTED';
        $doiattributes['url'] = $url;
        $doiattributes['identifier_type'] = 'DOI';
        $doiattributes['client_id'] = $this->getAuthenticatedClient()->client_id;
        $doiattributes['created_who'] = 'SYSTEM';
        $doiattributes['datacite_xml'] = $xml;

        $this->doiRepo->doiCreate($doiattributes);

        $doi = $this->doiRepo->getByID($doiValue);

        return $doi;

    }

    /**
     * Update a DOI
     *
     * @param $doiValue
     * @param null $url
     * @param null $xml
     * @return bool
     */
    public function update($doiValue, $url=NULL, $xml=NULL)
    {

        if (!$this->isClientAuthenticated()) {
            $this->setResponse("responsecode", "MT009");
            return false;
        }

        $doi = $this->doiRepo->getByID($doiValue);
        $this->setResponse('doi', $doiValue);

        if ($doi === null) {
            $this->setResponse('responsecode', 'MT011');
            return true;
        }

        // check if this client owns this doi
        if (!$this->isDoiAuthenticatedClients($doiValue, $doi->client_id)) {
            $this->setResponse('responsecode', 'MT008');
            $this->setResponse('verbosemessage', $doiValue . " is not owned by " . $this->getAuthenticatedClient()->client_name);
            return false;
        }

        // Validate URL and URL Domain
        if (isset($url) && $url != "") {
            $this->setResponse('url', $url);
            $validDomain = URLValidator::validDomains(
                $url, $this->getAuthenticatedClient()->domains
            );
            if (!$validDomain) {
                $this->setResponse("responsecode", "MT014");
                return false;
            }
        }

        if (isset($xml) && $xml != "") {
            // need to check that doi provided in xml matches doi
            $xml = XMLValidator::replaceDOIValue($doiValue, $xml);
        }else{
            $xml = $doi->datacite_xml;
            $xml = XMLValidator::replaceDOIValue($doiValue, $xml);
        }

        if ($this->validateXML($xml) === false) {
            $this->setResponse('responsecode', 'MT007');
            return false;
        }

        if(isset($xml) && $xml!="") {
            $result = $this->dataciteClient->update($xml);
            if ($result === true) {
                $this->setResponse('responsecode', 'MT002');
                //update the database DOIRepository
                $this->doiRepo->doiUpdate($doi, array('datacite_xml'=>$xml,'status'=>'ACTIVE'));
            } else {
                $this->setResponse('responsecode', 'MT010');
                $this->setResponse('verbosemessage', array_first($this->dataciteClient->getErrors()));
                return false;
            }
        }

        if (isset($url) && $url!="") {
            $result = $this->dataciteClient->updateURL($doiValue, $url);
            if ($result === true) {
                $this->setResponse('responsecode', 'MT002');
                //update the database DOIRepository
                $this->doiRepo->doiUpdate($doi, array('url'=>$url));
            } else {
                $this->setResponse('responsecode', 'MT010');
                $this->setResponse('verbosemessage', array_first($this->dataciteClient->getErrors()));
                return false;
            }
        }
        return true;

    }

    /**
     * Activate a DOI
     *
     * @param $doiValue
     * @return bool|mixed
     */
    public function activate($doiValue)
    {
        // validate client
        if (!$this->isClientAuthenticated()) {
            $this->setResponse('responsecode', 'MT009');
            return false;
        }

        //get the doi info
        $doi = $this->doiRepo->getByID($doiValue);
        $this->setResponse('doi', $doiValue);

        if ($doi === null) {
            $this->setResponse('responsecode', 'MT011');
            return true;
        }

        // check if this client owns this doi
        if (!$this->isDoiAuthenticatedClients($doiValue, $doi->client_id)) {
            $this->setResponse('responsecode', 'MT008');
            $this->setResponse('verbosemessage',$doiValue." is not owned by ".$this->getAuthenticatedClient()->client_name);
            return false;
        }

        $doi_xml = $doi->datacite_xml;

        //check if the doi is inactive
        if ($doi->status != 'INACTIVE') {
            $this->setResponse('responsecode', 'MT010');
            $this->setResponse('verbosemessage',
                'DOI ' . $doiValue . " not set to INACTIVE so cannot activate it");
            return false;
        }

        // activate using dataciteClient update method;
        $result = $this->dataciteClient->update($doi_xml);

        if ($result === true) {
            $this->setResponse('responsecode', 'MT004');
            //update the database DOIRepository
            $this->doiRepo->doiUpdate($doi, array('status'=>'ACTIVE'));
        } else {
            $this->setResponse('responsecode', 'MT010');
        }

        return $result;
    }

    /**
     * get status of the DOI
     *
     * @param $doiValue
     * @return bool
     */
    public function getStatus($doiValue)
    {
        // validate client
        if (!$this->isClientAuthenticated()) {
            $this->setResponse('responsecode', 'MT009');
            return false;
        }

        //get the doi info
        $doi = $this->doiRepo->getByID($doiValue);
        $this->setResponse('doi', $doiValue);

        if ($doi === null) {
            $this->setResponse('responsecode', 'MT011');
            return true;
        }

        // check if this client owns this doi
        if (!$this->isDoiAuthenticatedClients($doiValue, $doi->client_id)) {
            $this->setResponse('responsecode', 'MT008');
            $this->setResponse('verbosemessage', $doiValue . " is not owned by " . $this->getAuthenticatedClient()->client_name);
            return false;
        }

        $this->setResponse('responsecode', 'MT019');
        $this->setResponse('verbosemessage', $doi->status);

        return true;

    }


    /**
     * Deactivate a DOI
     *
     * @param $doiValue
     * @return bool
     */
    public function deactivate($doiValue)
    {

        // validate client
        if (!$this->isClientAuthenticated()) {
            $this->setResponse('responsecode', 'MT009');
            return false;
        }

        //get the doi info
        $doi = $this->doiRepo->getByID($doiValue);
        $this->setResponse('doi', $doiValue);

        if ($doi === null) {
            $this->setResponse('responsecode', 'MT011');
            return true;
        }

        // check if this client owns this doi
        if (!$this->isDoiAuthenticatedClients($doiValue, $doi->client_id)) {
            $this->setResponse('responsecode', 'MT008');
            $this->setResponse('verbosemessage',$doiValue." is not owned by ".$this->getAuthenticatedClient()->client_name);
            return false;
        }

        //check if the doi is inactive
        if ($doi->status != 'ACTIVE') {
            $this->setResponse('responsecode', 'MT010');
            $this->setResponse('verbosemessage',
                'DOI ' . $doiValue . " not set to ACTIVE so cannot deactivate it");
            return false;
        }

        $result = $this->dataciteClient->deActivate($doiValue);

        if ($result === true) {
            $this->setResponse('responsecode', 'MT003');
            //update the database DOIRepository
            $this->doiRepo->doiUpdate($doi, array('status'=>'INACTIVE'));
        } else {
            $this->setResponse('responsecode', 'MT010');
        }

        return $result;

    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setResponse($key, $value)
    {
        $this->response[$key] = $value;
        return $this;
    }

    /**
     * @return null
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function getDataCiteResponse()
    {
        return $this->dataciteClient->getResponse() ?: [];
    }

}