<?php


namespace ANDS\Commands\Script;


use ANDS\DOI\Model\Client;
use ANDS\DOI\Model\Doi;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\Util\Config;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;

class UpdateDataciteClient extends GenericScript implements GenericScriptRunnable
{
    protected $availableParams = ["all", ":id", "mint-report"];

    /** @var ClientRepository */
    private $clientRepository;

    public function run()
    {
        $database = Config::get('database.dois');
        $this->clientRepository = new ClientRepository(
            $database['hostname'],
            $database['database'],
            $database['username'],
            $database['password'],
            $database['port']
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
            case "mint-report-all":
                $this->log("Mint report.");
                $this->mintReport('all');
                break;
            case "mint-report-non-test":
                $this->log("Mint report.");
                $this->mintReport('non-test');
                break;
            default:
                $this->processAClient($params);
                break;
        }
    }

    private function mintReport($clientFilter)
    {
        initEloquent();

        $clientModel = new Client();
        $clientModel->setConnection('dois');

        $doiModel = new Doi();
        $doiModel->setConnection('dois');

        switch($clientFilter) {
            case "non-test":
                $clients = $clientModel->where('client_name', 'not like', 'Test%')->get();
                break;
            default:
                $clients = $clientModel->all();
        }

        $result = [];
        foreach ($clients as $client) {
            $testMinted = $doiModel
                ->where('client_id', $client->client_id)
                ->where('doi_id', 'LIKE', '%10.5072%')
                ->where('status', 'ACTIVE')
                ->count();
            $productionMinted = $doiModel
                ->where('client_id', $client->client_id)
                ->where('doi_id', 'NOT LIKE', '%10.5072%')
                ->where('status', 'ACTIVE')
                ->count();
            $result[] = [$client->client_name . " ($client->client_id)", $productionMinted, $testMinted];
        }

        $table = new Table($this->getOutput());

        $table->setHeaders(
            [ new TableCell("name"), new TableCell('production'), new TableCell('test') ]
        )
            ->setRows($result)
            ->render();
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