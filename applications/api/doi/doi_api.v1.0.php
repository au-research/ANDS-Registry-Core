<?php
namespace ANDS\API;

use ANDS\API\DOI\Bulk;
use ANDS\API\DOI\BulkRequest;
use ANDS\API\Task\TaskManager;
use ANDS\DOI\DataCiteClient;
use ANDS\DOI\DOIServiceProvider;
use ANDS\DOI\Formatter\ArrayFormatter;
use ANDS\DOI\Formatter\XMLFormatter;
use ANDS\DOI\Formatter\JSONFormatter;
use ANDS\DOI\Formatter\StringFormatter;
use ANDS\DOI\Model\Doi;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Repository\DoiRepository;
use ANDS\DOI\Transformer\XMLTransformer;
use \Exception as Exception;

class Doi_api
{

    protected $providesOwnResponse = false;
    public $outputFormat = "xml";

    private $client = null;

    public function handle($method = array())
    {
        $this->ci = &get_instance();
        $this->dois_db = $this->ci->load->database('dois', true);

        $this->params = array(
            'submodule' => isset($method[1]) ? $method[1] : 'list',
            'identifier' => isset($method[2]) ? $method[2] : false,
            'object_module' => isset($method[3]) ? $method[3] : false,
        );

        // common DOI API
        if (strpos($this->params['submodule'], '.' )  > 0 ) {
            if (strpos($this->params['submodule'],'10.') === false){
                return $this->handleDOIRequest();
            }
        }

        // check for DOI Request protocol, if set, default the format type to string and pass along
        $validDOIRequests = ['mint', 'update', 'activate', 'deactivate', 'status', 'xml'];
        if (in_array($this->params['submodule'], $validDOIRequests)) {
            $this->params['submodule'] .= ".string";
            return $this->handleDOIRequest();
        }

        //everything under here requires a client, app_id
        $this->getClient();

        //get a potential DOI
        if ($this->params['object_module']) {
            array_shift($method);
            $potential_doi = join('/',$method);
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
        } else if ($format == 'json'){
            $this->outputFormat = "application/json";
            $formater = new JSONFormatter();
        }else {
            $this->outputFormat = "text";
            $formater = new StringFormatter();
        }

        // getting the values from GET
        $appID = $this->ci->input->get('app_id');
        $sharedSecret = $this->ci->input->get('shared_secret');
        $manual = $this->ci->input->get('manual');

        if(!$appID && isset($_SERVER['PHP_AUTH_USER'])) {
            $appID = $_SERVER['PHP_AUTH_USER'];
        }

        if(!$sharedSecret && isset($_SERVER['PHP_AUTH_USER'])) {
            $sharedSecret = $_SERVER["PHP_AUTH_PW"];
        }

        $clientRepository = new ClientRepository(
            $this->dois_db->hostname, 'dbs_dois', $this->dois_db->username, $this->dois_db->password
        );

        $doiRepository = new DoiRepository(
            $this->dois_db->hostname, 'dbs_dois', $this->dois_db->username, $this->dois_db->password
        );

        // handles xml.xml
        if($method == 'xml'){
            if ($doi = $this->ci->input->get('doi')) {
                $doiObject = $doiRepository->getByID($doi);
                return $doiObject->datacite_xml;
            } else {
                throw new Exception ("DOI must be provided");
            }
        }

        // handles status method
        if ($method == 'status') {

            $response_status = true;

            // Check the local DOI database
            if (!$doiRepository) {
                $response_status = false;
            }

            // Check DataCite DOI HTTPS service
            if (!$response_time = $this->_isDataCiteAlive()) {
                $response_status = false;
            }

            if ($response_status) {
                return $formater->format([
                    'responsecode' => 'MT090',
                    'verbosemessage' => "(took " . $response_time . "ms)"
                ]);
            } else {
                return $formater->format([
                    'responsecode' => 'MT091',
                ]);
            }
        }

        // past this point, an app ID must be provided to continue
        if (!$appID) {
            return $formater->format([
                'responsecode' => 'MT010',
                'verbosemessage' => 'You must provide an app id to mint a doi'
            ]);
        }

        // constructing the client and checking if the client exists and authorised
        $client = $clientRepository->getByAppID($appID);

        if(!$client){
            return $formater->format([
                'responsecode' => 'MT009',
                'verbosemessage' => 'You are not authorised to use this service'
            ]);
        }

        // constructing the dataciteclient to talk with datacite services
        $dataciteClient = new DataCiteClient(
            get_config_item("gDOIS_DATACENTRE_NAME_PREFIX").".".get_config_item("gDOIS_DATACENTRE_NAME_MIDDLE").str_pad($client->client_id,2,"-",STR_PAD_LEFT), get_config_item("gDOIS_DATACITE_PASSWORD")
        );

        // set to the default DOI Service in global config
        $dataciteClient->setDataciteUrl(get_config_item("gDOIS_SERVICE_BASE_URI"));

        // construct the DOIServiceProvider to handle DOI requests
        $doiService = new DOIServiceProvider($clientRepository, $doiRepository, $dataciteClient);

        // authenticate the client
        // TODO: check authenticated client
        $doiService->authenticate(
            $appID,
            $sharedSecret,
            $this->getIPAddress(),
            $manual
        );

        // handles mint, update, activate and deactivate
        switch ($method) {
            case "mint":
                 $doiService->mint(
                    $this->ci->input->get('url'),
                    $this->getPostedXML()
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
        }

        // log is done using ArrayFormatter
        $arrayFormater = new ArrayFormatter();

        // do the logging
        $this->doilog(
            $arrayFormater->format($doiService->getResponse()),
            'doi_' . ($manual ? 'm_' : '') . $method,
            $client
        );

        // return the formatted response
        switch($format) {
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

    private function getIPAddress()
    {
        if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
            return $_SERVER["HTTP_CLIENT_IP"];
        } else if ( isset($_SERVER["REMOTE_ADDR"]) )    {
            return $_SERVER["REMOTE_ADDR"];
        } else {
            // Run by command line??
            return "127.0.0.1";
        }
    }

    private function getPostedXML()
    {
        $output= '';
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

        $client = $this->getClientModel($this->ci->input->get('app_id'));

        // api/doi/bulk/:identifier
        if ($this->params['identifier'] !== false) {

            // api/doi/bulk/:identifier/:object_module
            if ($this->params['object_module']!==false) {
                // get all bulk by ID
                $bulkRequest = BulkRequest::find((int) $this->params['object_module']);

                // api/doi/bulk/:identfiier/:object_module?status=:status&limit=:limit
                if ($status = $this->ci->input->get('status')) {
                    $limit = $this->ci->input->get('limit') ?: 30;
                    $bulkRequest->$status = $bulkRequest->getBulkByStatus($status)->take($limit)->get();
                }

                return $bulkRequest;
            } else {
                // get all bulk by clientID


                // api/doi/bulk/:identifier
                $bulkRequests = BulkRequest::where('client_id', $this->params['identifier'])
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
        $bulkRequest->client_id = $client->client_id;
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
            'name' => 'DOI Bulk Request: '.$client->client_name,
            'params' => http_build_query([
                'class' => 'doiBulk',
                'bulkID' => $bulkRequest->id
            ]),
            'type' => 'POKE'
        ]);

        // log to ELK
        monolog(
            [
                'event' => 'doi_bulk_request',
                'client' => [
                    'name' => $client->client_name,
                    'id' => $client->client_id
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
                'client_id' => $client->client_id,
                'message' => 'DOI Bulk Request Generated. Type: '.$type. ' From: '. $from. ' To: '.$to.' Affecting '.$matchingDOIs['total']. ' DOI(s)'
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

            $client = $this->getClientModel($this->ci->input->get('app_id'));

            $query = Doi::query();
            $query->where('client_id', $client->client_id)
                ->whereRaw('`url` LIKE BINARY ?', ['%'.$from.'%']);

            return [
                'total' => $query->count(),
                'result' => $query->take($limit)->skip($offset)->get()
            ];
        }

        return [];
    }

    private function getClientModel($app_id)
    {
        $clientRepository = new ClientRepository(
            $this->dois_db->hostname,
            'dbs_dois',
            $this->dois_db->username,
            $this->dois_db->password
        );
        $client = $clientRepository->getByAppID($this->ci->input->get('app_id'));
        return $client;
    }


    private function getAssociateAppID($role_id)
    {
        if (!$role_id) throw new Exception('role id required');
        $result = array();
        $roles_db = $this->ci->load->database('roles', true);
        $user_affiliations = array('1');
        $roles_db->distinct()->select('*')
                // ->where_in('child_role_id', $user_affiliations)
                ->where('role_type_id', 'ROLE_DOI_APPID      ', 'after')
                ->join('roles', 'role_id = parent_role_id')
                ->from('role_relations');
        $query = $roles_db->get();

        dd($query->result());

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
        } else return false;
    }

    private function getClient()
    {
        $app_id = $this->ci->input->get('app_id') ? $this->ci->input->get('app_id') : false;

        if (!$app_id) {
            throw new Exception('App ID required');
        }

        $query = $this->dois_db
            ->where('app_id', $app_id)
            ->select('*')
            ->get('doi_client');

        if (!$this->client = $query->result()) {
            throw new Exception('Invalid App ID');
        }

        //permitted_url_domains
        $this->client = array_pop($this->client);
        $query = $this->dois_db
            ->where('client_id',$this->client->client_id)
            ->select('client_domain')
            ->get('doi_client_domains');
        foreach ($query->result_array() AS $domain) {
            $this->client->permitted_url_domains[] =  $domain['client_domain'];
        }
    }

    private function clientDetail()
    {
        return array(
            'client' => $this->client,
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

        $query = $this->dois_db
            ->order_by('updated_when', 'desc')
            ->order_by('created_when', 'desc')
            ->where('client_id', $this->client->client_id)
            ->limit($limit, $offset)
            ->where('status !=', 'REQUESTED')
            ->select('*');

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

        $data['total'] = $this->dois_db
            ->where('client_id', $this->client->client_id)
            ->where('status !=', 'REQUESTED')
            ->where("doi_id LIKE '%{$search}%'")
            ->count_all_results('doi_objects');

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

        // set up logging message
        $message = [
            'event' => strtolower($event),
            'response' => $log_response,
            'messagecode' => $log_response['responsecode'],
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

    private function _isDataCiteAlive($timeout = 5)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, (get_config_item("gDOIS_SERVICE_BASE_URI")));
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_exec($curl);

        return !(curl_errno($curl) || curl_getinfo($curl, CURLINFO_HTTP_CODE) != "200");
    }
    public function __construct()
    {
        $this->ci = &get_instance();
        require_once APP_PATH . 'vendor/autoload.php';
    }
}
