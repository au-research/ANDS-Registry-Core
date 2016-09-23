<?php


namespace ANDS\API\Task;


use ANDS\API\DOI\Bulk;
use ANDS\API\DOI\BulkRepository;
use ANDS\API\DOI\BulkRequest;
use ANDS\DOI\DataCiteClient;
use ANDS\DOI\DOIServiceProvider;
use ANDS\DOI\Formatter\ArrayFormatter;
use ANDS\DOI\Formatter\JSONFormatter;
use ANDS\DOI\Formatter\StringFormatter;
use ANDS\DOI\Model\Doi;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Repository\DoiRepository;

/**
 * Class DoiBulkTask
 * Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ANDS\API\Task
 */
class DoiBulkTask extends Task
{
    private $bulkID = null;
    private $limit = 10;
    private $noMore = false;
    private $doiService = null;

    public function run_task()
    {
        $this->loadParams();
        $this->initializeTask();

        $bulkRequest = BulkRequest::find($this->bulkID);

        // find bulk object that belongs to this bulkRequest
        if (!BulkRepository::hasBulkRequestID($bulkRequest->id)) {
            $this->generateBulk($bulkRequest);
        }

        $bulks = Bulk::where('bulk_id', $bulkRequest->id)
            ->where('status', 'PENDING')->take($this->limit)->get();

        $totalPending = Bulk::where('bulk_id', $bulkRequest->id)
            ->where('status', 'PENDING')->count();

        if (count($bulks) == 0) {
            $this->log('Nothing to do. There is no PENDING request match this Bulk Request ID: '. $bulkRequest->id);
            $this->noMore = true;
            $bulkRequest->status = 'COMPLETED';
            $bulkRequest->save();
            $this->log('Bulk Request ID: '. $bulkRequest->id. ' is set to COMPLETED');
            return;
        }

        // set the BulkRequest to RUNNING status
        $bulkRequest->status = 'RUNNING';
        $bulkRequest->save();

        foreach ($bulks as $bulk) {
            $this->executeBulk($bulk);
        }

        // check if there is more to do
        if (count($bulks) >= $totalPending) {
            $this->noMore = true;
            $this->log('Last request!');
            $this->logCompletion($bulkRequest);
            $bulkRequest->status = 'COMPLETED';
            $bulkRequest->save();
        } else {
            $this->log('There are '. ($totalPending - count($bulks)). ' requests remaining to be executed');
        }
    }

    public function logCompletion($bulkRequest)
    {
        $parameters = json_decode($bulkRequest->params, true);

        // log DOI_BULK_COMPLETED to activity_log
        $this->logToActivityLogTable(
            "DOI Bulk Operation completed ID(".$bulkRequest->id.") Type: ". $parameters['type'] . " From: ". $parameters['from']. " To: ".$parameters['to'].". COMPLETED: ".$bulkRequest->counts['COMPLETED']. ", ERROR: ".$bulkRequest->counts['ERROR'],
            null,
            'SUCCESS',
            'DOI_BULK_COMPLETED'
        );

        // log DOI_BULK_COMPLETED to file
        monolog(
            [
                'event' => 'doi_bulk_request_completed',
                'client' => [
                    'name' => $this->doiService->getAuthenticatedClient()->client_name,
                    'id' => $this->doiService->getAuthenticatedClient()->client_id
                ],
                'request' => [
                    'params' => [
                        'type' => $parameters['type'],
                        'from' => $parameters['from'],
                        'to' => $parameters['to']
                    ],
                    'bulk' => true
                ],
                'result' => $bulkRequest->counts
            ],
            'doi_api', 'info', true
        );
    }

    /**
     * Place task back to PENDING state when there are more to do
     */
    public function hook_end()
    {
        if ($this->noMore === false) {
            $this->setStatus("PENDING")->save();
        }
    }

    /**
     * Generate DOIServiceProvider in the form of $this->doiService to be used
     */
    public function initializeTask()
    {
        $doisDB = $this->ci->load->database('dois', true);
        $clientRepository = new ClientRepository(
            $doisDB->hostname,
            'dbs_dois',
            $doisDB->username,
            $doisDB->password
        );

        $doiRepository = new DoiRepository(
            $doisDB->hostname,
            'dbs_dois',
            $doisDB->username,
            $doisDB->password
        );

        $bulkRequest = BulkRequest::find($this->bulkID);

        $client = $clientRepository->getByID($bulkRequest->client_id);
        $dataciteClient = new DataCiteClient(
            get_config_item("gDOIS_DATACENTRE_NAME_PREFIX").".".get_config_item("gDOIS_DATACENTRE_NAME_MIDDLE").str_pad($client->client_id,2,"-",STR_PAD_LEFT), get_config_item("gDOIS_DATACITE_PASSWORD")
        );
        $dataciteClient->setDataciteUrl(get_config_item("gDOIS_SERVICE_BASE_URI"));
        $doiService = new DOIServiceProvider($clientRepository, $doiRepository, $dataciteClient);
        $doiService->setAuthenticatedClient($client);

        $this->doiService = $doiService;
    }

    /**
     * Run the first time this task is executed
     * Generate all the bulk requests to be run in the background
     *
     * @param $request
     */
    private function generateBulk($request)
    {
        $this->log('Generating bulks for request('.$request->id.')');
        $parameters = json_decode($request->params, true);

        if ($parameters['type'] == 'url') {
            $count = 0;
            $dois = Doi::where('client_id', $request->client_id)
                ->where('url', 'LIKE', '%'.$parameters['from'].'%')
                ->get();
            foreach ($dois as $doi) {
                BulkRepository::addBulk([
                    'doi' => $doi->doi_id,
                    'target' => 'url',
                    'from' => $doi->url,
                    'to' => str_replace($parameters['from'], $parameters['to'], $doi->url),
                    'bulk_id' => $request->id
                ]);
                $count++;
            }
            $this->log('Added '.$count.' bulk item to be processed');
        }
    }

    /**
     * Execute a single bulk request
     *
     * @param $bulk
     */
    private function executeBulk($bulk)
    {
        $JSONFormater = new JSONFormatter();
        $stringFormater = new StringFormatter();
        $arrayFormater = new ArrayFormatter();

        $this->log('Executing bulk: '.$bulk->id .' Updating ('.$bulk->doi.') URL from '.$bulk->from.' to '.$bulk->to);

        if ($bulk->target == 'url') {
            $result = $this->doiService->update($bulk->doi, $bulk->to);
            $bulk->message = $JSONFormater->format($this->doiService->getResponse());
            if ($result) {
                $bulk->status = 'COMPLETED';

                // log to the task
                $this->log('Success('.$bulk->id.')');

                // log to the activity table
                $this->logToActivityLogTable(
                    $stringFormater->format($this->doiService->getResponse()),
                    $bulk->doi,
                    'SUCCESS'
                );

                $this->logToFile(
                    array_merge(
                        $arrayFormater->format($this->doiService->getResponse()),
                        [
                            'doi' => $bulk->doi,
                            'app_id' => $this->doiService->getAuthenticatedClient()->app_id
                        ]
                    )
                );

            } else {
                $bulk->status = 'ERROR';

                // log to the task
                $this->log('Error('.$bulk->id.'): '.$stringFormater->format($this->doiService->getResponse()));

                // log to the activity table
                $this->logToActivityLogTable(
                    $stringFormater->format($this->doiService->getResponse()),
                    $bulk->doi,
                    'FAILURE'
                );

                // log to file for ELK
                $this->logToFile(
                    array_merge(
                        $arrayFormater->format($this->doiService->getResponse()),
                        [
                            'doi' => $bulk->doi,
                            'app_id' => $this->doiService->getAuthenticatedClient()->app_id
                        ]
                    )
                );
            }
            $bulk->save();
        }
    }

    /**
     * Log to file using the global monolog() function
     *
     * @param $response
     */
    public function logToFile($response)
    {
        $message = array();
        $message["event"] = 'doi_update';
        $message["response"] = $response;
        $message["doi"]["id"] = (isset($response["doi"]) ? $response["doi"] : "");
        $message["client"]["id"] = null;
        $message["client"]["name"] = null;
        $message["api_key"] = (isset($response["app_id"]) ? $response["app_id"] : "");

        //determine client name
        $message['client']['name'] = $this->doiService->getAuthenticatedClient()->client_name;
        $message['client']['id'] = $this->doiService->getAuthenticatedClient()->client_id;

        $message['request']['manual'] = true;
        $message['request']['bulk'] = true;


        //determine if doi is a test doi
        $test_check = strpos($message["doi"]["id"], '10.5072');
        if ($test_check || $test_check === 0) {
            $message["doi"]["production"] = false;
        } else {
            $message["doi"]["production"] = true;
        }

        monolog($message, "doi_api", "info", true);

    }

    /**
     * Log to activity_log table
     * Using CI table database instead of Eloquent Model
     * Going to deprecate the activity_log table soon, so CI reference can stay
     *
     * @param $message
     * @param $doiValue
     * @param $result
     * @param string $activity
     */
    public function logToActivityLogTable($message, $doiValue, $result, $activity = 'UPDATE')
    {
        $data = [
            'activity' => $activity,
            'doi_id' => $doiValue,
            'result' => $result,
            'client_id' => $this->doiService->getAuthenticatedClient()->client_id,
            'message' => $message
        ];
        $db = $this->getCI()->load->database('dois', TRUE);
        $result = $db->insert('activity_log', $data);
        if (!$result) {
            $this->addError("Failed to write to activity log table");
        }
    }

    /**
     * Parse the bulkRequestID to $this->bulkID
     */
    public function loadParams()
    {
        parse_str($this->params, $params);
        $this->bulkID = $params['bulkID'];
    }
}