<?php

namespace ANDS\API;

use ANDS\API\DOI\Bulk;
use ANDS\API\DOI\BulkRequest;
use ANDS\API\Task\TaskManager;
use ANDS\DOI\MdsClient;
use ANDS\DOI\FabricaClient;
use ANDS\DOI\DOIServiceProvider;
use ANDS\DOI\Formatter\ArrayFormatter;
use ANDS\DOI\Formatter\XMLFormatter;
use ANDS\DOI\Formatter\JSONFormatter;
use ANDS\DOI\Formatter\StringFormatter;
use ANDS\DOI\Model\Client;
use ANDS\DOI\Model\Doi;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Repository\DoiRepository;
use ANDS\DOI\Transformer\XMLTransformer;
use ANDS\DOI\Validator\URLValidator;
use ANDS\Util\Config;
use \Exception as Exception;

class Doi_api
{

    protected $providesOwnResponse = false;
    public $outputFormat = "xml";

    /* @var Client */
    private $client = null;

    private $testPrefix = "10.5072";

    /* @var ClientRepository */
    private $clientRepository = null;

    /* @var DoiRepository */
    private $doiRepository = null;

    public function handle($method = array())
    {
        $this->ci = &get_instance();
        $this->dois_db = $this->ci->load->database('dois', true);

        $this->params = array(
            'submodule' => isset($method[1]) ? $method[1] : 'list',
            'identifier' => isset($method[2]) ? $method[2] : false,
            'object_module' => isset($method[3]) ? $method[3] : false,
        );

        if ($this->params['submodule'] === "datacite") {
            $potential_doi = '';
            if ($this->params['object_module']) {
                array_shift($method);
                array_shift($method);
                array_shift($method);
                $potential_doi = join('/', $method);
            }
            return $this->handleDataciteReroute($potential_doi);
        }

        // common DOI API
        if (strpos($this->params['submodule'],
                '.') > 0 && strpos($this->params['submodule'], '10.') === false
        ) {
            return $this->handleDOIRequest();
        }

        // check for DOI Request protocol, if set, default the format type to string and pass along
        $validDOIRequests = [
            'mint',
            'update',
            'activate',
            'deactivate',
            'status',
            'xml',
            'doistatus'
        ];

        if (in_array($this->params['submodule'], $validDOIRequests)) {
            $this->params['submodule'] .= ".string";
            return $this->handleDOIRequest();
        }

        //everything under here requires a client, app_id
        $this->getClient();

        //get a potential DOI
        if (strpos($this->params['submodule'], '10.') === 0 && $this->params['identifier']) {
            array_shift($method);
            $potential_doi = join('/', $method);
            if ($doi = $this->getDOI($potential_doi)) {
                $doi->title = $this->getDoiTitle($doi->datacite_xml);

                // transform to kernel-4 for update form
                if ($this->ci->input->get('request_version') == '4') {
                    $doi->datacite_xml = XMLTransformer::migrateToKernel4($doi->datacite_xml);
                }

                return $doi;
            }
        }
        // extended DOI API
        try {
            if ($this->params['submodule'] == 'list') {
                return $this->listDois();
            } elseif ($this->params['submodule'] == 'log') {
                return $this->activitiesLog();
            } elseif ($this->params['submodule'] == 'client') {
                return $this->clientDetail();
            } elseif ($this->params['submodule'] == 'bulk') {
                return $this->handleBulkOperation();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }

    /**
     * Handles all DOI related request
     */
    private function handleDOIRequest()
    {
        $this->providesOwnResponse = true;
        $split = explode('.', $this->params['submodule']);
        $method = $split[0];
        $format = array_key_exists(1, $split) ? $split[1] : 'string';
        // setting up the formatter, defaults to string if none is specified
        if ($format == "xml") {
            $this->outputFormat = "text/xml";
            $formater = new XMLFormatter();
        } else {
            if ($format == 'json') {
                $this->outputFormat = "application/json";
                $formater = new JSONFormatter();
            } else {
                $this->outputFormat = "text";
                $formater = new StringFormatter();
            }
        }

        // getting the values from GET
        $appID = $this->ci->input->get('app_id');
        $sharedSecret = $this->ci->input->get('shared_secret');

        //determine if this call has been created by a mydois user interface call
        session_start();
        if (isset($_SESSION['token']) && $_SESSION['token'] == $this->ci->input->get('token')) {
            $manual = true;
        } else {
            $manual = false;
        }

        if (!$appID && isset($_SERVER['PHP_AUTH_USER'])) {
            $appID = $_SERVER['PHP_AUTH_USER'];
        }

        if (!$sharedSecret && isset($_SERVER['PHP_AUTH_USER'])) {
            $sharedSecret = $_SERVER["PHP_AUTH_PW"];
        }


        // handles xml.xml
        if ($method == 'xml') {
            if ($doi = $this->ci->input->get('doi')) {
                $doiObject = $this->doiRepository->getByID($doi);

                if ($doiObject == null) {
                    $response = [
                        'responsecode' => 'MT011',
                        'doi' => $doi
                    ];
                    $this->doilog($response, 'doi_xml');
                    return $formater->format($response);
                }

                if ($format == "json") {
                    $response = [
                        'responsecode' => 'MT013',
                        'doi' => $doi,
                        'verbosemessage' => $doiObject->datacite_xml
                    ];
                    $this->doilog($response, 'doi_xml');
                    return $formater->format($response);
                }

                return $doiObject->datacite_xml;

            } else {
                throw new Exception ("DOI must be provided");
            }
        }

        // handles status method
        if ($method == 'status') {

            $response_status = true;

            // Check the local DOI database
            if (!$this->doiRepository) {
                $response_status = false;
            }

            // Check DataCite DOI HTTPS service
            if (!$response_time = $this->_isDataCiteAlive()) {
                $response_status = false;
            }

            if ($response_status) {
                $response = [
                    'responsecode' => 'MT090',
                    'verbosemessage' => "(took " . $response_time . "ms)"
                ];
                $this->doilog($response, 'doi_status');
                return $formater->format($response);
            } else {
                $response = [
                    'responsecode' => 'MT091',
                    'verbosemessage' => "(took " . $response_time . "ms)"
                ];
                $this->doilog($response, 'doi_status');
                return $formater->format($response);
            }
        }

        // past this point, an app ID must be provided to continue
        if (!$appID) {
            $response = [
                'responsecode' => 'MT010',
                'verbosemessage' => 'You must provide an app id'
            ];
            $this->doilog($response, 'doi_' . $method);
            return $formater->format($response);
        }

        // constructing the client and checking if the client exists and authorised
        $this->client = $this->clientRepository->getByAppID($appID);

        // try to get a client based on TEST
        if (!$this->client) {
            $this->client = $this->clientRepository->getByAppID(str_replace("TEST", "", $appID));
        }

        if(!$this->client){
            $response = [
                'responsecode' => 'MT009',
                'verbosemessage' => 'You are not authorised to use this service. No client found with AppID: ' . $appID
            ];
            $this->doilog($response, 'doi_' . $method);
            return $formater->format($response);
        }

        if($appID == $this->client->test_app_id)
        {
            $this->client->mode = 'test';
        }else{
            $this->client->mode = 'prod';
        }

        $dataciteClient = $this->getDataciteClientForClient($this->client, $this->client->mode);

        // construct the DOIServiceProvider to handle DOI requests
        $doiService = new DOIServiceProvider($this->clientRepository, $this->doiRepository,
            $dataciteClient);

        // authenticate the client
        $result = $doiService->authenticate(
            $appID,
            $sharedSecret,
            $this->getIPAddress(),
            $manual
        );



        if ($result === false) {
            $this->doilog($doiService->getResponse(), 'doi_' . $method,
                $this->client);
            return $formater->format($doiService->getResponse());
        }

        // handles mint, update, activate and deactivate
        switch ($method) {
            case "mint":
                $doiService->mint(
                    $this->ci->input->get('url'),
                    $this->getPostedXML(),
                    $manual
                );
                break;
            case "update":
                $doiService->update(
                    $this->ci->input->get('doi'),
                    $this->ci->input->get('url'),
                    $this->getPostedXML()
                );
                break;
            case "activate":
                $doiService->activate(
                    $this->ci->input->get('doi')
                );
                break;
            case "deactivate":
                $doiService->deactivate(
                    $this->ci->input->get('doi')
                );
                break;
            case "doistatus":
                $doiService->getStatus(
                    $this->ci->input->get('doi')
                );
                break;
        }

        // log is done using ArrayFormatter
        $arrayFormater = new ArrayFormatter();

        // do the logging

        $ANDSDOIResponse = $arrayFormater->format($doiService->getResponse());

        $DataciteResponses = $doiService->getDataCiteResponse();

        $DataciteResponse = array();
        if(isset($DataciteResponses['messages'])) {
            foreach ($DataciteResponses['messages'] as $amessage) {
                if (isset($amessage['endpoint'])) {
                    $DataciteResponse['datacite.'.$amessage['endpoint'].'.httpcode'] = $amessage['httpcode'];
                    $DataciteResponse['datacite.'.$amessage['endpoint'].'.output'] = $amessage['output'];
                    $DataciteResponse['datacite.'.$amessage['endpoint'].'.url'] = $amessage['url'];
                }

            }
        }
        if(isset($DataciteResponses['errors'])) {
            foreach ($DataciteResponses['errors'] as $error) {
                if (isset($amessage['endpoint'])) {
                    $DataciteResponse['datacite.error'] = $error;
                }

            }
        }

        $logResponse = array_merge($ANDSDOIResponse, $DataciteResponse);

        $this->doilog(
            $logResponse,
            'doi_' . ($manual ? 'm_' : '') . $method,
            $this->client
        );

        // return the formatted response
        switch ($format) {
            case "xml":
            case "json":
            case "string":
                return $formater->format($doiService->getResponse());
                break;
            default:
                return $doiService->getResponse();
                break;
        }
    }

    private function getDataciteClientForClient(Client $client,$mode = 'prod')
    {
        // constructing the dataciteclient to talk with datacite services
        $config = Config::get('datacite');


        // no need to construct the name if it's provided by datacite_symbol
        $clientUsername = $client->datacite_symbol;
        if (!$clientUsername) {
            $clientUsername = $config['name_prefix'] . "." . $config['name_middle'] . str_pad($client->client_id,
                    2, '-', STR_PAD_LEFT);
        }

        $dataciteClient = new MdsClient(
            $clientUsername, $config['password'], $config['testPassword']
        );

        // set to the default DOI Service in global config
        if($mode == 'test') {
            $dataciteClient->setDataciteUrl($config['base_test_url']);
        } else{
            $dataciteClient->setDataciteUrl($config['base_url']);
        }

        return $dataciteClient;
    }

    /**
     * Handles all Datacite client  related request
     */
    private function handleDataciteReroute($potential_doi)
    {
        $appID = '';
        $sharedSecret = '';
        $docall = false;
        $log = true;
        $customRequest = '';
        $response = '';
        $requestBody = '';
        $responselog = array();
        $this->providesOwnResponse = true;
        $arrayFormater = new ArrayFormatter();

        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $appID = $_SERVER['PHP_AUTH_USER'];
        }

        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $sharedSecret = $_SERVER["PHP_AUTH_PW"];
        }

        //If the client has not provided their appid or shared secret - or has provided incorrect ones - then  set up what logging we can and return the datacite error message
        if (!$appID || !$sharedSecret) {
            $response = "An Authentication object was not found in the SecurityContext";
            $result = "An Authentication object was not found in the SecurityContext";
            $responselog = [
                'responsecode' => 'MT009',
                'doi' => '',
                'activity' => 'authenticate',
                'result' => $result,
                'dataCiteHTTPCode' => "401",
                'message' => json_encode($response, true)
            ];
            $this->doilog($arrayFormater->format($responselog),
                'doi_' . $responselog['activity'], $this->client);
            http_response_code("401");
            header('WWW-Authenticate: Basic realm="ands"');
            header('HTTP/1.0 401 Unauthorized');
            exit;
            return $result;
        }

        $this->client = $this->clientRepository->getByAppID($appID);

        if (!$this->client) {
            $response = "Bad credentials";
            $result = "Bad credentials";
            $responselog = [
                'responsecode' => 'MT009',
                'doi' => '',
                'activity' => 'authenticate',
                'result' => $result,
                'dataCiteHTTPCode' => "401",
                'message' => json_encode($response, true)
            ];
            $this->doilog($arrayFormater->format($responselog),
                'doi_' . $responselog['activity']);
            http_response_code("401");
            return $result;
        }

        $call = $this->params['identifier'];
        $responselog['activity'] = $this->params['identifier'];

        $dataciteClient = $this->getDataciteClientForClient($this->client);

        // construct the DOIServiceProvider to ensure this client is registered to use the service
        $doiService = new DOIServiceProvider($this->clientRepository, $this->doiRepository,
            $dataciteClient);

        // authenticate the client
        $result = $doiService->authenticate(
            $appID,
            $sharedSecret,
            $this->getIPAddress(),
            $manual = false
        );

        if (!$result) {
            $response = "Bad credentials";
            $result = "Bad credentials";
            $responselog = [
                'responsecode' => 'MT009',
                'doi' => '',
                'activity' => 'authenticate',
                'result' => $result,
                'dataCiteHTTPCode' => "401",
                'message' => json_encode($response, true)
            ];
            $this->doilog($arrayFormater->format($responselog),
                'doi_' . ($manual ? 'm_' : '') . $responselog['activity'],
                $this->client);
            http_response_code("401");
            return $result;
        }

        if ($potential_doi != '') {
            $doi = $potential_doi;
        } else {
            $doi = $this->getDoiValue();
        }

        $validDoi = true;
        $doiObject = '';
        $clientDoi = true;
        if ($doi) {
            //lets check if this is a valid doi for this client
            $doiObject = $this->doiRepository->getByID($doi);
            $validDoi = $this->checkDoi($doi);
            if ($doiObject) {
                $clientDoi = ($doiObject->client_id == $this->client->client_id) ? true : false;
            }
        }

        $validXml = true;

        if ($this->getPostedXML() && $call == 'metadata') {
            if ($this->wellFormed($this->getPostedXML())) {
                $validXml = $doiService->validateXML($this->getPostedXML());
            } else {
                $validXml = false;
            }
        }

        $validDomain = true;
        if ($this->getPostedUrl() != '') {
            $validDomain = URLValidator::validDomains($this->getPostedUrl(),
                $this->client->domains);
        }

        if ($call == 'metadata' && $validDoi) {
            if ($_SERVER['REQUEST_METHOD'] == 'DELETE' && $doiObject && $clientDoi) {
                if ($doiObject->status == 'RESERVED' || $doiObject->status == 'RESERVED_INACTIVE') {
                    $responselog = [
                        'responsecode' => 'MT003',
                        'activity' => strtoupper("DEACTIVATE_RESERVE")
                    ];
                    $this->doiRepository->doiUpdate($doiObject,
                        array('status' => 'RESERVED_INACTIVE'));
                } else {
                    $responselog = [
                        'responsecode' => 'MT003',
                        'activity' => strtoupper("DEACTIVATE")
                    ];
                    $this->doiRepository->doiUpdate($doiObject,
                        array('status' => 'INACTIVE'));
                }
                $call .= '/' . $doi;
                $requestBody = '';
                $customRequest = 'DELETE';
                $docall = true;
            } elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE' && !$doiObject && $clientDoi) {
                $responselog = [
                    'responsecode' => 'MT011',
                    'activity' => strtoupper("DEACTIVATE")
                ];
                $call .= '/' . $doi;
                $response = "DOI doesn't exist";
                $dataCiteResponseCode = "404";

            } elseif ((!$doiObject || $doiObject->status == 'RESERVED_INACTIVE') && $this->getPostedXML() != '') {
                if (!$validXml) {
                    $responselog = [
                        'responsecode' => 'MT006',
                        'activity' => strtoupper("RESERVE")
                    ];
                } else {
                    $responselog = [
                        'responsecode' => 'MT015',
                        'activity' => strtoupper("RESERVE")
                    ];
                    if (!$doiObject) {
                        $doiObject = $doiService->insertNewDOI($doi,
                            $this->getPostedXML(), '');
                    }
                    $this->doiRepository->doiUpdate($doiObject,
                        array('status' => 'RESERVED'));
                }
                $requestBody = $this->getPostedXML();
                $docall = true;
            } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $call .= '/' . $doi;
                $docall = true;
                $log = false;
            } elseif ($this->getPostedXML() && $doiObject->status == 'INACTIVE' && $clientDoi) {
                if (!$validXml) {
                    $responselog['responsecode'] = 'MT007';
                } else {
                    $responselog = [
                        'responsecode' => 'MT004',
                        'activity' => strtoupper("ACTIVATE")
                    ];
                    $this->doiRepository->doiUpdate($doiObject, array(
                        'status' => 'ACTIVE',
                        'datacite_xml' => $this->getPostedXML()
                    ));
                }
                $requestBody = $this->getPostedXML();
                $docall = true;
            } elseif ($this->getPostedXML() && ($doiObject->status == 'ACTIVE' || $doiObject->status == 'RESERVED') && $clientDoi) {
                if (!$validXml) {
                    $responselog['responsecode'] = 'MT007';
                } else {
                    $responselog = [
                        'responsecode' => 'MT002',
                        'activity' => strtoupper("UPDATE")
                    ];
                    $this->doiRepository->doiUpdate($doiObject,
                        array('datacite_xml' => $this->getPostedXML()));
                }
                $requestBody = $this->getPostedXML();
                $docall = true;
            }
        } elseif ($call == 'doi' && $validDoi) {
            if ($call == 'doi' && isset($doiObject) && $_SERVER['REQUEST_METHOD'] == 'POST' && $clientDoi) {
                //we are either minting an already reserved doi or we are updating the url of a doi
                if ($doiObject->status == 'RESERVED' && $validDomain) {
                    if ($validDomain) {
                        $responselog = [
                            'responsecode' => 'MT016',
                            'activity' => strtoupper("MINT_RESERVED")
                        ];
                    }
                } else {
                    if ($validDomain) {
                        $responselog = [
                            'responsecode' => 'MT002',
                            'activity' => strtoupper("UPDATE")
                        ];
                    }
                }

                $requestBody = "doi=" . $doi . "\nurl=" . $this->getPostedUrl();
                //if the posted url is not valid for this client we will not update the database

                if ($validDomain) {
                    $this->doiRepository->doiUpdate($doiObject, array(
                        'status' => 'ACTIVE',
                        'url' => $this->getPostedUrl()
                    ));
                } else {
                    $responselog = [
                        'responsecode' => 'MT014',
                        'activity' => strtoupper("UPDATE")
                    ];
                }
                $docall = true;
            } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $call .= '/' . $doi;
                $docall = true;
                $log = false;
            } else {
                $docall = true;
                $requestBody = "doi=" . $doi . "\nurl=" . $this->getPostedUrl();
                $responselog = [
                    'responsecode' => 'MT018',
                    'activity' => strtoupper("MINT")
                ];
            }
        }

        if (!$validDoi) {
            $responselog['responsecode'] = 'MT017';
            $dataCiteResponseCode = "404";
            $response = "[doi] DOI prefix is not allowed";
        }
        if (!$clientDoi && !$docall) {
            $responselog['responsecode'] = 'MT008';
            $dataCiteResponseCode = "403";
            $response = "cannot access dataset which belongs to another party";
        }

        if ($docall) {
            $response = $dataciteClient->request($dataciteClient->getDataciteUrl() . $call,
                $requestBody, $customRequest);
        }


        if (!isset($responselog['responsecode'])) {
            $responselog['responsecode'] = 'MT000';
        }


        $DataciteResponses = $dataciteClient->getMessages() ? $dataciteClient->getMessages() : array();

         if(isset($DataciteResponses)) {
            foreach ($DataciteResponses as $amessage) {
                if (isset($amessage['endpoint'])) {
                    $responselog['datacite.'.$amessage['endpoint'].'.httpcode'] = $amessage['httpcode'];
                    $responselog['datacite.'.$amessage['endpoint'].'.output'] = $amessage['output'];
                    $responselog['datacite.'.$amessage['endpoint'].'.url'] = $amessage['url'];
                }

            }
        }

        $responselog['doi'] = $doi;
        $responselog['result'] = $result;
        $responselog['client_id'] = $this->client->client_id;
        $responselog['app_id'] = $appID;
        $responselog['message'] = json_encode($response, true);


        // do the logging
        if ($log) {
            $this->doilog(
                $arrayFormater->format($responselog),
                'doi_' . ($manual ? 'm_' : '') . $responselog['activity'],
                $this->client
            );
        }

        //set the http response code to what has been returned from DataCite
        $responsehttp = '';
        foreach($DataciteResponses as $dataCiteMessage){
            if(isset($dataCiteMessage['httpcode'])) $responsehttp = $dataCiteMessage['httpcode'];
        }

        http_response_code($responsehttp);

        return $response;

    }

    private function getIPAddress()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                return $_SERVER["HTTP_CLIENT_IP"];
            } else {
                if (isset($_SERVER["REMOTE_ADDR"])) {
                    return $_SERVER["REMOTE_ADDR"];
                } else {
                    // Run by command line??
                    return "127.0.0.1";
                }
            }
        }
    }

    private function getDoiValue()
    {
        //get the doi from the request body or the xml

        $doi = '';

        if ($this->getPostedDoi() != '') {
            $doi = $this->getPostedDoi();
        } else {
            $doi = $this->getXmlDoi();
        }
        return $doi;
    }

    private function checkDoi($doi)
    {
        $doiComponents = (explode("/", $doi));
        if($doiComponents[0] == $this->testPrefix){
            return true;
        }
        return $this->client->hasPrefix($doiComponents[0]);
    }

    private function wellFormed($xml)
    {
        $doiXML = new \DOMDocument();
        libxml_use_internal_errors(true);
        if ($wellFormed = $doiXML->loadXML($xml)) {
            return true;
        } else {
            return false;
        }
    }

    private function getPostedXML()
    {
        $output = '';
        $data = file_get_contents("php://input");
        parse_str(htmlentities($data), $output);
        if (isset($output['xml'])) {
            return trim($output['xml']);
        } elseif (count($output) > 1) {
            //hotfix to return XML that is not empty,
            //implode($output) returns empty for no reason
            //todo verify and check
            return trim($data);
            //return trim(implode($output));
        } else {
            return trim($data);
        }
    }

    private function getPostedDoi()
    {
        $output = '';
        $data = file_get_contents("php://input");
        $data = str_replace(PHP_EOL, "&", $data);
        parse_str($data, $output);
        if (isset($output['doi'])) {
            return trim($output['doi']);
        } else {
            return false;
        }
    }

    private function getPostedUrl()
    {
        $output = '';
        $data = file_get_contents("php://input");
        $data = str_replace(PHP_EOL, "&", $data);
        parse_str($data, $output);
        if (isset($output['url'])) {
            return trim($output['url']);
        } else {
            return false;
        }
    }

    private function getXmlDoi()
    {
        if ($this->getPostedXML() != '') {

            $doiXML = new \DOMDocument();
            if ($this->wellFormed($this->getPostedXML())) {
                $doiXML->loadXML($this->getPostedXML());
                $xmlDoi = $doiXML->getElementsByTagName('identifier');
                return $xmlDoi->item(0)->nodeValue;
            }
        }
        return '';

    }

    /**
     * Handles bulk operation
     * /api/doi/bulk/
     * @return array
     * @throws Exception
     */
    private function handleBulkOperation()
    {
        $app_id = $this->ci->input->get('app_id') ? $this->ci->input->get('app_id') : false;
        if (!$app_id) {
            throw new Exception('App ID required');
        }

        $this->getClientModel($this->ci->input->get('app_id'));

        // api/doi/bulk/:identifier
        if ($this->params['identifier'] !== false) {

            // api/doi/bulk/:identifier/:object_module
            if ($this->params['object_module'] !== false) {
                // get all bulk by ID
                $bulkRequest = BulkRequest::find((int)$this->params['object_module']);

                // api/doi/bulk/:identfiier/:object_module?status=:status&limit=:limit
                if ($status = $this->ci->input->get('status')) {
                    $limit = $this->ci->input->get('limit') ?: 30;
                    $bulkRequest->$status = $bulkRequest->getBulkByStatus($status)->take($limit)->get();
                }

                return $bulkRequest;
            } else {
                // get all bulk by clientID


                // api/doi/bulk/:identifier
                $bulkRequests = BulkRequest::where('client_id',
                    $this->params['identifier'])
                    ->orderBy('date_created', 'DESC')->get()->all();

                $limit = $this->ci->input->get('limit') ?: 30;
                foreach ($bulkRequests as &$bulkRequest) {
                    $defaultStatuses = ['PENDING', 'COMPLETED', 'ERROR'];
                    foreach ($defaultStatuses as $status) {
                        $bulkRequest->$status = $bulkRequest->getBulkByStatus($status)->take($limit)->get();
                    }
                    $bulkRequest = $bulkRequest->toArray();
                }

                return $bulkRequests;
            }
        }

        // api/doi/bulk/?delete=:bulkRequestID
        if ($deleteID = $this->ci->input->get('delete')) {
            BulkRequest::destroy($deleteID);
            Bulk::where('bulk_id', $deleteID)->delete();
            return true;
        }

        // Otherwise do bulk operation

        $type = $this->ci->input->get('type') ?: false;
        $from = $this->ci->input->get('from') ?: false;
        $to = $this->ci->input->get('to') ?: false;
        $preview = $this->ci->input->get('preview') ?: false;
        $offset = $this->ci->input->get('offset') ?: 0;
        $limit = $this->ci->input->get('limit') ?: 30;

        // TODO: verify appID

        // get DOI that can be bulked
        $matchingDOIs = $this->getMatchingDOIs($type, $from, $offset, $limit);

        $bulkRequest = [];
        foreach ($matchingDOIs['result'] as $doi) {
            $bulkRequest[] = [
                'doi' => $doi->doi_id,
                'type' => $type,
                'from' => $doi->url,
                'to' => str_replace($from, $to, $doi->url)
            ];
        }

        // Return preview
        if ($preview) {
            return [
                'total' => $matchingDOIs['total'],
                'result' => $bulkRequest
            ];
        }

        // Generate new BulkRequest
        $bulkRequest = new BulkRequest;
        $bulkRequest->client_id = $this->client->client_id;
        $bulkRequest->status = "PENDING";
        $bulkRequest->params = json_encode([
            'type' => $type,
            'from' => $from,
            'to' => $to
        ]);
        $bulkRequest->save();

        // Generate new task do process the BulkRequest
        $taskManager = new TaskManager($this->ci->db, $this->ci);
        $task = $taskManager->addTask([
            'name' => 'DOI Bulk Request: ' . $this->client->client_name,
            'params' => http_build_query([
                'class' => 'doiBulk',
                'bulkID' => $bulkRequest->id
            ]),
            'type' => 'PHPSHELL'
        ]);

        // log to ELK
        monolog(
            [
                'event' => 'doi_bulk_request',
                'client' => [
                    'name' => $this->client->client_name,
                    'id' => $this->client->client_id
                ],
                'request' => [
                    'params' => [
                        'type' => $type,
                        'from' => $from,
                        'to' => $to
                    ],
                    'result' => [
                        'bulk_id' => $bulkRequest->id,
                        'task_id' => $task['id']
                    ],
                    'bulk' => true
                ]
            ],
            "doi_api", "info", true
        );

        // log to activity_log table
        $this->dois_db->insert('activity_log',
            [
                'activity' => 'DOI_BULK_REQUEST',
                'doi_id' => null,
                'result' => 'SUCCESS',
                'client_id' => $this->client->client_id,
                'message' => 'DOI Bulk Request Generated. Type: ' . $type . ' From: ' . $from . ' To: ' . $to . ' Affecting ' . $matchingDOIs['total'] . ' DOI(s)'
            ]
        );

        return [
            'message' => 'Bulk Request Created!',
            'bulk_id' => $bulkRequest->id,
            'task_id' => $task['id']
        ];
    }

    /**
     * Return a set of result, with total value
     * for all DOI that matches the current client
     * Matches a `type` and `from` value
     *
     * @param $type
     * @param $from
     * @param $offset
     * @param $limit
     * @return array
     */
    private function getMatchingDOIs($type, $from, $offset, $limit)
    {
        if ($type == 'url') {
            // get DOIs belongs to this APPID that has a URL matching FROM

            $this->getClientModel($this->ci->input->get('app_id'));

            $query = Doi::query();
            $query->where('client_id', $this->client->client_id)
                ->whereRaw('`url` LIKE BINARY ?', ['%' . $from . '%']);

            return [
                'total' => $query->count(),
                'result' => $query->take($limit)->skip($offset)->get()
            ];
        }

        return [];
    }

    private function getClientModel($app_id)
    {
        $this->client = $this->clientRepository->getByAppID($app_id);
        if($this->client->app_id == $app_id) {
            $this->client->mode = "prod";
            $this->client->datacite_prefix = $this->getTrustedClientActivePrefix();
        }else{
            $this->client->mode = "test";
            $this->client->datacite_prefix = $this->getTrustedClientActiveTestPrefix();
            $this->client->datacite_test_prefix = $this->getTrustedClientActiveTestPrefix();
        }
    }


    private function getAssociateAppID($role_id)
    {
        if (!$role_id) {
            throw new Exception('role id required');
        }
        $result = array();
        $roles_db = $this->ci->load->database('roles', true);
        $user_affiliations = array('1');
        $roles_db->distinct()->select('*')
            // ->where_in('child_role_id', $user_affiliations)
            ->where('role_type_id', 'ROLE_DOI_APPID      ', 'after')
            ->join('roles', 'role_id = parent_role_id')
            ->from('role_relations');
        $query = $roles_db->get();


        if ($query->num_rows() > 0) {
            foreach ($query->result() AS $r) {
                $result[] = $r->parent_role_id;
            }
        }
        return $result;
    }

    private function getDOI($doi)
    {
        $query = $this->dois_db
            ->where('doi_id', $doi)
            ->get('doi_objects');
        if ($query->num_rows() > 0) {
            $result = $query->first_row();
            return $result;
        } else {
            return false;
        }
    }

    private function getClient()
    {
        $app_id = $this->ci->input->get('app_id') ? $this->ci->input->get('app_id') : false;

        if (!$app_id) {
            throw new Exception('App ID required');
        }
        $this->client = $this->clientRepository->getByAppID($app_id);
        if (!$this->client) {
            throw new Exception('Invalid App ID');
        }

        if($this->client->app_id == $app_id) {

            $this->client->mode = "prod";
            $this->client->datacite_prefix = $this->getTrustedClientActivePrefix();
        }else{
            $this->client->mode = "test";
            $this->client->datacite_prefix = $this->getTrustedClientActiveTestPrefix();
            $this->client->datacite_test_prefix = $this->getTrustedClientActiveTestPrefix();
        }



        $this->client->datacite_test_prefix = $this->getTrustedClientActiveTestPrefix();
    }



    private function getTrustedClientActivePrefix()
    {
        if (is_array_empty($this->client->prefixes))
            return $this->testPrefix;
        foreach ($this->client->prefixes as $clientPrefix) {
            if ($clientPrefix->active  && $clientPrefix->is_test == 0)
                return $clientPrefix->prefix->prefix_value;
        }
        return $this->testPrefix;
    }
    private function getTrustedClientActiveTestPrefix()
    {
        if (is_array_empty($this->client->prefixes))
            return $this->testPrefix;
        foreach ($this->client->prefixes as $clientPrefix) {
            if ($clientPrefix->active && $clientPrefix->is_test == 1)
                return $clientPrefix->prefix->prefix_value;
        }
        return $this->testPrefix;
    }

    private function getTrustedClients()
    {
        return $this->clientRepository->getAll();
    }
    
    
    private function clientDetail()
    {
        $domains_str = "";
        foreach ($this->client->domains as $domain) {
            $domains_str .= $domain->client_domain. ", ";
        }

        $this->client['permitted_url_domains'] = trim($domains_str, ", ");
        return array(
            'client' => $this->client
        );
    }

    public function isProvidingOwnResponse()
    {
        return $this->providesOwnResponse;
    }

    private function listDois()
    {
        $limit = $this->ci->input->get('limit') ?: 50;
        $offset = $this->ci->input->get('offset') ?: 0;
        $search = $this->ci->input->get('search') ?: '';
        $mode = $this->ci->input->get('mode') ?: $this->client->mode;
        //dd($this->client->datacite_test_prefix);

        $query = $this->dois_db
            ->order_by('updated_when', 'desc')
            ->order_by('created_when', 'desc')
            ->where('client_id', $this->client->client_id)
            ->limit($limit, $offset)
            ->where('status !=', 'REQUESTED')
            ->select('*');

        if($mode == "prod") {
            $query = $this->dois_db->where("doi_id NOT LIKE '10.5072%' AND doi_id NOT LIKE '".$this->client->datacite_test_prefix."%'")->where('status !=', 'REQUESTED');
        }else{
            $query = $this->dois_db->where("doi_id LIKE '10.5072/%' OR doi_id LIKE '".$this->client->datacite_test_prefix."%'")->where('status !=', 'REQUESTED');
        }
        if ($search) {
            $query = $this->dois_db->where("doi_id LIKE '%{$search}%'");
        }

        $query = $this->dois_db
            ->get('doi_objects');

        $data['dois'] = array();
        foreach ($query->result() as $doi) {
            $obj = $doi;
            $obj->title = $this->getDoiTitle($doi->datacite_xml);
            $data['dois'][] = $obj;
        }


        $query2 = $this->dois_db
        ->where('client_id', $this->client->client_id)
        ->where('status !=', 'REQUESTED')
        ->where("doi_id LIKE '%{$search}%'");
        if($mode == "prod") {
            $query2 = $this->dois_db->where("doi_id NOT LIKE '%10.5072%'");
        }else{
            $query2 = $this->dois_db->where("doi_id LIKE '%10.5072%'");
        }

        $data['total'] =
            $this->dois_db->count_all_results('doi_objects');

        return $data;
    }

    private function getDoiTitle($doiXml)
    {
        $doiObjects = new \DOMDocument();
        $titleFragment = 'No Title';
        if (strpos($doiXml, '<') === 0) {
            $result = $doiObjects->loadXML(trim($doiXml));
            $titles = $doiObjects->getElementsByTagName('title');

            if ($titles->length > 0) {
                $titleFragment = '';
                for ($j = 0; $j < $titles->length; $j++) {
                    if ($titles->item($j)->getAttribute("titleType")) {
                        $titleType = $titles->item($j)->getAttribute("titleType");
                        $title = $titles->item($j)->nodeValue;
                        $titleFragment .= $title . " (" . $titleType . ") ";
                    } else {
                        $titleFragment .= $titles->item($j)->nodeValue;
                    }
                }
            }
        } else {
            $titleFragment = $doiXml;
        }

        return $titleFragment;

    }

    private function activitiesLog()
    {
        $offset = $this->ci->input->get('start') ? $this->ci->input->get('start') : 0;
        $limit = $this->ci->input->get('limit') ? $this->ci->input->get('limit') : 50;
        $query = $this->dois_db
            ->order_by('timestamp', 'desc')
            ->where('client_id', $this->client->client_id)
            ->select('*')
            ->limit($limit)->offset($offset)
            ->get('activity_log');
        $data['activities'] = $query->result();
        return $data;
    }


    /**
     * Perform a logging operation on this new end point api/doi
     * Logs using monolog functionality
     *
     * @param $log_response
     * @param string $event
     * @param null $client
     */
    private function doilog($log_response, $event = "doi_xml", $client = null)
    {

        $arrayformater = new ArrayFormatter();
        $log_response = $arrayformater->format($log_response);

        // set up logging message
        $message = [
            'event' => strtolower($event),
            'response' => $log_response,
            'doi' => [
                'id' => isset($log_response["doi"]) ? $log_response["doi"] : "",
                'production' => true
            ],
            'client' => [
                'id' => null,
                'name' => null
            ],
            'api_key' => isset($log_response["app_id"]) ? $log_response["app_id"] : ""
        ];

        // Copy the responsecode to messagecode for logging purpose
        $message['response']['messagecode'] = $message['response']['responsecode'];

        //determine client
        if ($client) {
            $message['client'] = [
                'id' => $client->client_id,
                'name' => $client->client_name
            ];
        }

        //determine if event is manual or m2m
        if (strtolower(substr($event, 0, 6)) == 'doi_m_') {
            $message['request']['manual'] = true;
            $message["event"] = str_replace("_m_", "_", $message["event"]);
        } else {
            $message['request']['manual'] = false;
        }

        //determine if doi is a test doi
        $test_check = strpos($message["doi"]["id"], '10.5072');
        if ($test_check || $test_check === 0) {
            $message["doi"]["production"] = false;
        }

        monolog($message, "doi_api", "info", true);

        // Insert log entry to the activity log in the database
        if ($client) {
            $this->dois_db->insert('activity_log',
                [
                    'activity' => strtoupper(str_replace("doi_", "", $event)),
                    'doi_id' => isset($log_response["doi"]) ? $log_response["doi"] : "",
                    'result' => strtoupper($log_response["type"]),
                    'client_id' => $client->client_id,
                    'message' => json_encode($log_response, true)
                ]
            );
        }
    }

    private function _isDataCiteAlive($timeout = 5)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, (Config::get('datacite.base_url')));
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_exec($curl);

        return !(curl_errno($curl) || curl_getinfo($curl,
                CURLINFO_HTTP_CODE) != "200");
    }

    public function __construct()
    {
        $this->ci = &get_instance();
        require_once BASE . 'vendor/autoload.php';
        $database = Config::get('database.dois');

        $this->clientRepository = new ClientRepository(
            $database['hostname'], $database['database'], $database['username'],
            $database['password']
        );
        $this->doiRepository = new DoiRepository(
            $database['hostname'], $database['database'], $database['username'],
            $database['password']
        );

    }
}
