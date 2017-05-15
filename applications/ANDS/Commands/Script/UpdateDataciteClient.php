<?php


namespace ANDS\Commands\Script;


use ANDS\DOI\Model\Client;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\Util\Config;

class UpdateDataciteClient extends GenericScript implements GenericScriptRunnable
{
    protected $availableParams = ["all", ":id"];

    /** @var ClientRepository */
    private $clientRepository;

    public function run()
    {
        $database = Config::get('database.dois');
        $this->clientRepository = new ClientRepository(
            $database['hostname'],
            $database['database'],
            $database['username'],
            $database['password']
        );

        $params = $this->getInput()->getOption('params');
        if ($params === null) {
            $this->log("You have to specify a param: available: ". implode('|', $this->availableParams), "info");
            return;
        }

        switch ($params) {
            case "all":
                $this->log("Processing all clients. (Not implemented)");
                $this->processAllClients();
                break;
            default:
                $this->processAClient($params);
                break;
        }
    }

    private function processAllClients()
    {
        $clients = $this->clientRepository->getAll();
        $total = count($clients);
        $this->log("Processing {$total} clients");
        foreach ($clients as $client) {
            $this->updateDataciteClient($client);
        }
    }

    private function processAClient($id)
    {
        $client = $this->clientRepository->getByID($id);
        if (!$client) {
            $this->log("No client with id $id found", "error");
        }
        $this->updateDataciteClient($client);
    }

    private function updateDataciteClient(Client $client)
    {
        $id = $client->client_id;
        $this->log("Processing {$client->client_name}({$id})");
        if ($client->datacite_login === null) {
            $this->log("datacite_symbol is not set. Setting...");
            $this->clientRepository->generateDataciteSymbol($client);
            $client = $this->clientRepository->getByID($id);
            $this->log("datacite_symbol set to {$client->datacite_symbol}");
        }
    }
}