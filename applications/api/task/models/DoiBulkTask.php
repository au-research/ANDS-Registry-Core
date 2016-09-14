<?php


namespace ANDS\API\Task;


use ANDS\API\DOI\Bulk;
use ANDS\API\DOI\BulkRepository;
use ANDS\API\DOI\BulkRequest;
use ANDS\DOI\DataCiteClient;
use ANDS\DOI\DOIServiceProvider;
use ANDS\DOI\Model\Doi;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Repository\DoiRepository;

class DoiBulkTask extends Task
{
    private $bulkID = null;
    private $limit = 30;
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

        foreach ($bulks as $bulk) {
            $this->executeBulk($bulk);
        }

        // if there is none, generate some
    }

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

        $client = ClientRepository::getByID($bulkRequest->client_id);
        $dataciteClient = new DataCiteClient(
            get_config_item("gDOIS_DATACENTRE_NAME_PREFIX").".".get_config_item("gDOIS_DATACENTRE_NAME_MIDDLE").str_pad($client->client_id,2,"-",STR_PAD_LEFT), get_config_item("gDOIS_DATACITE_PASSWORD")
        );
        $dataciteClient->setDataciteUrl(get_config_item("gDOIS_SERVICE_BASE_URI"));
        $doiService = new DOIServiceProvider($clientRepository, $doiRepository, $dataciteClient);
        $doiService->setAuthenticatedClient($client);

        $this->doiService = $doiService;
    }

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

    private function executeBulk($bulk)
    {
        $this->log('Executing bulk: '.$bulk->id);
        if ($bulk->target == 'url') {
            $result = $this->doiService->update($bulk->doi, $bulk->to);
            $bulk->message = json_encode($this->doiService->getResponse(), true);
            if ($result) {
                $bulk->status = 'COMPLETED';
                $this->log('Executed('. $bulk->id.') Updated URL from '.$bulk->from.' to '.$bulk->to);
            } else {
                $bulk->status = 'ERROR';
                $this->log('Failed to execute('.$bulk->id.')');
            }
            $bulk->save();

        }

    }

    public function loadParams()
    {
        parse_str($this->params, $params);
        $this->bulkID = $params['bulkID'];
    }
}