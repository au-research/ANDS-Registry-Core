<?php


namespace ANDS\Commands;


use ANDS\DOI\DataCiteClient;
use ANDS\DOI\MdsClient;
use ANDS\DOI\FabricaClient;
use ANDS\DOI\DOIServiceProvider;
use ANDS\DOI\Model\Client;
use ANDS\DOI\Model\Doi;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Repository\DoiRepository;
use ANDS\Util\Config;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DOISyncCommand extends Command
{
    private $output;
    protected function configure()
    {
        $this
            ->setName('doi:sync')
            ->setDescription('Sync DOI with Datacite')
            ->setHelp("This command allows you to sync local DOI with remote Datacite server")

            ->addArgument('what', InputArgument::REQUIRED, 'identify|process|checkWrongDoiInXML|processWrongDOI')
            ->addArgument('id', InputArgument::OPTIONAL, 'id of the record')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        initEloquent();
        ini_set('memory_limit','512M');

        $this->output = $output;

        $command = $input->getArgument("what");

        if ($command == "identify") {
            $this->identify($input, $output);
        }

        if ($command == "process") {
            $this->process($input, $output);
        }

        if ($command == "checkWrongDoiInXML") {
            $this->checkWrongDoiInXML($input, $output);
        }

        if ($command == "processWrongDOI") {
            $this->processWrongDOI($input, $output);
        }
        return;
    }

    private function identify(InputInterface $input, OutputInterface $output)
    {
        $clientModel = new Client;
        $clientModel->setConnection('dois');

        $clients = $clientModel->get();

        if ($id = $input->getArgument('id')) {
            $client = $clientModel->find($id);
            if (!$client) {
                $output->writeln("<error>Error client $id not found</error>");
                die();
            }
            $clients = [ $client ];
        }
        $data = [];
        $progressBar = new ProgressBar($output, count($clients));
        foreach ($clients as $client) {
            $stat = $this->getStat($client);
            $data[] = [
                'client' => $client,
                'stat' => $stat
            ];
            $progressBar->advance(1);
            $display = collect($stat)->filter(function($item, $key) {
                return strpos($key, 'count') !== false;
            })->toArray();
           $this->displayStat($client, $display, $output);
        }
        $progressBar->finish();

        // count_db_missing
        $hasMissingDB = collect($data)->filter(function($data) {
            return $data['stat']['count_db_missing'] > 0;
        })->toArray();

        if (count($hasMissingDB) > 0) {
            foreach ($hasMissingDB as $missing) {
                $client = $missing['client'];
                $stat = $missing['stat'];
                $this->log("$client->client_name ($client->client_id) has {$stat['count_db_missing']} missing in db");
            }
        }
    }

    private function process(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');

        if (!$id) {
            $output->writeln("<warning>Need a client ID to start processing</warning>");
            return;
        }

        $clientModel = new Client;
        $clientModel->setConnection('dois');

        $client = $clientModel->find($id);

        $output->writeln("Getting stats for $client->client_name ($client->client_id)");

        $stat = $this->getStat($client);
        $display = collect($stat)->filter(function($item, $key) {
            return strpos($key, 'count') !== false;
        })->toArray();

        $this->displayStat($client, $display, $output);

        $output->writeln("There are {$stat['count_datacite_missing']} missing DOI on datacite");
        $output->writeln("There are {$stat['count_db_missing']} missing DOI in database");
        if ($stat['count_datacite_missing'] == 0) {
            $output->writeln("There are no missing DOI on datacite");
        }
        if ($stat['count_db_missing'] == 0) {
            $output->writeln("There are no missing DOI on datacite");
        }
        if ($stat['count_datacite_missing'] == 0 && $stat['count_db_missing'] == 0) {
            $output->writeln("Aborting");
            return;
        }

        if ($stat['count_datacite_missing']) {
            $this->mintMissingDOIs($client, $stat, $input, $output);
        }

        if ($stat['count_db_missing']) {
            $this->fetchMissingDOIs($client, $stat, $input, $output);
        }

    }

    /**
     * Display the statistics for a client
     *
     * @param Client $client
     * @param $display
     * @param OutputInterface $output
     */
    private function displayStat(Client $client, $display, OutputInterface $output)
    {
        $table = new Table($output);
        $rows = collect($display)
            ->map(function($value, $key){
                return [$key, $value];
            })->toArray();
        $table->setHeaders(
            [ new TableCell("$client->client_name ($client->client_id)", ['colspan' => 2]) ]
        )
            ->setRows($rows)
            ->render();
    }

    /**
     * Get the statistics for a client
     *
     * @param Client $client
     * @return array
     */
    private function getStat(Client $client)
    {
        $doiModel = new Doi;
        $doiModel->setConnection('dois');

        $databaseDOIs = $doiModel
            ->where('client_id', $client->client_id)
            ->whereIn('status', ['ACTIVE', 'INACTIVE', 'REQUESTED'])
            ->lists('doi_id')
            ->map(function($item) {
                return strtoupper($item);
            })->filter(function($item) {
                return strpos($item, "10.5072") === false;
            })->toArray();


        $dataciteClient = $this->getDataciteClient($client);

        $dataciteClient->setDataciteUrl("https://app.datacite.org/");
        $allDataciteMintedDOIs = array();
        $allDataciteMintedClientDOIs = $dataciteClient->getDOIs("");

        if(isset($allDataciteMintedClientDOIs['data'])){
            foreach($allDataciteMintedClientDOIs['data'] as $doi){
                $allDataciteMintedDOIs[] = strtoupper($doi['id']);
            }
        }

        $allDataciteMintedDOIs = collect($allDataciteMintedDOIs)
            ->filter(function($item) {
                return strpos($item, "10.5072") === false;
            })->filter(function($item) {
                return trim($item) != "";
            })->toArray();
        $difference = array_unique(array_merge(
            array_diff($databaseDOIs, $allDataciteMintedDOIs),
            array_diff($allDataciteMintedDOIs, $databaseDOIs)
        ));



        $missingInDatabase = collect($difference)
            ->filter(function($item) use ($databaseDOIs) {
                return !in_array($item, $databaseDOIs);
            })->toArray();

        $missingInDatacite = collect($difference)
            ->filter(function($item) use ($allDataciteMintedDOIs) {
                return !in_array($item, $allDataciteMintedDOIs);
            })->toArray();

      //  var_dump(count($missingInDatabase));
      //  var_dump($missingInDatabase);

        return [
            'count_db' => count($databaseDOIs),
            'count_datacite' => count($allDataciteMintedDOIs),
            'count_diff' => count($difference),
            'diff' => $difference,
            'count_db_missing' => count($missingInDatabase),
            'count_datacite_missing' => count($missingInDatacite),
            'db_missing' => $missingInDatabase,
            'datacite_missing' => $missingInDatacite
        ];
    }

    /**
     * Get a DataCiteClient for a Client
     * to interact with DataCite
     *
     * @param Client $client
     * @return DataCiteClient
     */
    private function getDataciteClient(Client $client)
    {
        $config = Config::get('datacite');

        $dataciteClient = new FabricaClient($client->datacite_symbol, $config['password']);

        return $dataciteClient;
    }

    private function mintMissingDOIs(Client $client, $stat, InputInterface $input, OutputInterface $output)
    {
        $doiModel = new Doi;
        $doiModel->setConnection('dois');

        $diff = $stat['datacite_missing'];
        $output->writeln("Listing Missing Datacite: ");
        foreach ($diff as $id) {
            $doi = $doiModel->where('doi_id', $id)->first();
            if (!$doi) {
                $output->writeln("<error>$id does not exist</error>");
                continue;
            }
            $output->writeln("$id - $doi->status - $doi->updated_when");
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Proceed to fix? [y|N] : ', false);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln("Aborting.");
            return;
        }

        $output->writeln("Proceeding to fix {$stat['count_datacite_missing']} DOIs.");

        $config = Config::get('database');
        $clientRepository = new ClientRepository(
            $config['dois']['hostname'], $config['dois']['database'], $config['dois']['username'], $config['dois']['password']
        );
        $doiRepository = new DoiRepository(
            $config['dois']['hostname'], $config['dois']['database'], $config['dois']['username'], $config['dois']['password']
        );

        $dataciteClient = $this->getDataciteClient($client);
        $doiService = new DOIServiceProvider($clientRepository, $doiRepository, $dataciteClient);
        $doiService->setAuthenticatedClient($client);

        foreach ($diff as $id) {
            $doi = $doiRepository->getByID($id);
            if (!$doi) {
                $output->writeln("<error>$id does not exist</error>");
                continue;
            }

            $output->writeln("Minting $id");
            $output->writeln("URL: ". $doi->url);
            $result = $dataciteClient->mint($id, $doi->url, $doi->datacite_xml);
            if ($result === true) {
                $output->writeln("Success minting $id");
            } else {
                $output->writeln("<error>Failed minting $id</error>");
                $output->writeln("<error>".array_first($dataciteClient->getErrors())."</error>");
            }
        }
    }


    private function fetchMissingDOIs(Client $client, $stat, InputInterface $input, OutputInterface $output)
    {
        $diff = $stat['db_missing'];
        $output->writeln("Listing Missing Datacite: ");
        foreach ($diff as $id) {
            $output->writeln("$id");
        }

//        $helper = $this->getHelper('question');
//        $question = new ConfirmationQuestion('Proceed to fix? [y|N] : ', false);
//        if (!$helper->ask($input, $output, $question)) {
//            $output->writeln("Aborting.");
//            return;
//        }

        foreach ($diff as $id) {
            $this->addMissingDOI($id, $client);
        }
    }

    private function addMissingDOI($id, Client $client)
    {
        $this->log("Fixing $id");
        $dataciteClient = $this->getDataciteClient($client);
        $url = $dataciteClient->get($id);

        $xml = $dataciteClient->request($dataciteClient->getDataciteUrl() . 'metadata/'. $id);
        if (!$xml || $xml == "dataset inactive") {
            $this->log("<error>Failed $id No XML found or dataset inactive</error>");
            return;
        }
        $status = "ACTIVE";

        $metadata = json_decode(file_get_contents("https://api.datacite.org/works/$id"), true);
        if (is_array_empty($metadata['data'])) {
            $this->log("$id No Metadata found");
        }

        date_default_timezone_set('UTC');
        $updated_when = Carbon::createFromTimestamp(1)->format("Y-m-d H:i:s");
        $created_when = Carbon::createFromTimestamp(1)->format("Y-m-d H:i:s");

        if (array_key_exists('attributes', $metadata['data'])) {
            $updated_when = Carbon::parse($metadata['data']['attributes']['updated'])->format("Y-m-d H:i:s");
            $created_when = Carbon::parse($metadata['data']['attributes']['deposited'])->format("Y-m-d H:i:s");
        }

        if (!$url) {
            $this->log("<info>$id No URL found. Adding to RESERVED state</info>");
            $status = "RESERVED";
        }

        $doi = new Doi;
        $doi->setConnection('dois');

        try {
            $doiXML = new \DOMDocument();
            $doiXML->loadXML($xml);
            $publisher = $doiXML->getElementsByTagName('publisher');
            $publication_year = $doiXML->getElementsByTagName('publicationYear');
        } catch (\Exception $e) {
            $this->log("<error>Error loading XML: {$e->getMessage()}</error>");
            return;
        }

        $attributes = [
            'doi_id' => $id,
            'publisher' => $publisher->item(0)->nodeValue,
            'publication_year' => $publication_year->item(0)->nodeValue,
            'status' => $status,
            'url' => $url,
            'identifier_type' => 'DOI',
            'client_id' => $client->client_id,
            'created_who' => 'SYSTEM',
            'datacite_xml' => $xml,
            'updated_when' => $updated_when,
            'created_when' => $created_when
        ];
        $doi->setRawAttributes($attributes);
        $doi->save();

        $this->log("<info>Added $id to the database</info>");
    }

    /**
     * Update the xml for a client with the correct DOI identifier
     *
     * @param Client $client
     * @return array
     */
    private function processWrongDOI(InputInterface $input, OutputInterface $output){

        $doiModel = new Doi;
        $doiModel->setConnection('dois');

        $id = $input->getArgument('id');
        if((string)$id=="0") $id="00";
        if (!$id) {
            $output->writeln("<warning>Need a client ID to start processing</warning>");
            return;
        }

        $clientModel = new Client;
        $clientModel->setConnection('dois');

        $client = $clientModel->find($id);

        $output->writeln("Processing wrong doi in xml updates for $client->client_name ($client->client_id)");

        $stat = $this->getWrongDOIStat($client);
        $output->writeln("$client->client_name ($client->client_id) has ".$stat['count_db']. " DOIs to update");
        if($stat['count_db']>0) {
            $wrongDOIs = $stat['dois'];
            
            $config = Config::get('database');

            $clientRepository = new ClientRepository(
                $config['dois']['hostname'], $config['dois']['database'], $config['dois']['username'], $config['dois']['password']
            );
            $doiRepository = new DoiRepository(
                $config['dois']['hostname'], $config['dois']['database'], $config['dois']['username'], $config['dois']['password']
            );

            $dataciteClient = $this->getDataciteClient($client);
            $doiService = new DOIServiceProvider($clientRepository, $doiRepository, $dataciteClient);
            $doiService->setAuthenticatedClient($client);

            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Proceed to fix? [y|N] : ', false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln("Aborting.");
                return;
            }

            $output->writeln("Proceeding to fix {$stat['count_db']} DOIs.");

            foreach ($wrongDOIs as $id) {
                $doi = $doiRepository->getByID($id);
                if (!$doi) {
                    $output->writeln("<error>$id does not exist</error>");
                    continue;
                }
                $output->writeln("$id - $doi->status - $doi->updated_when");
                $update = $doiService->update($doi->doi_id, $url = NULL, $doi->datacite_xml);
                if(!$update){
                    $output->writeln($doi->doi_id." not updated successfully");
                }
            }
        }
    }

    /**
     * Get the lists of DOIs that have a DOI identifier in the latest xml which is incorrect
     *
     * @param
     * @return array
     */
    private function checkWrongDoiInXML(InputInterface $input, OutputInterface $output){

        $clientModel = new Client;
        $clientModel->setConnection('dois');

        $clients = $clientModel->get();

        if ($id = $input->getArgument('id')) {
            $client = $clientModel->find($id);
            if (!$client) {
                $output->writeln("<error>Error client $id not found</error>");
                die();
            }
            $clients = [ $client ];
        }

        $data = [];
        $progressBar = new ProgressBar($output, count($clients));
        foreach ($clients as $client) {
            $stat = $this->getWrongDOIStat($client);

            $data[] = [
                'client' => $client,
                'stat' => $stat
            ];
            $progressBar->advance(1);
        }
        $progressBar->finish();
        $hasWrongDoi = collect($data)->filter(function($data) {
            return $data['stat']['count_db'] > 0;
        })->toArray();

        if (count($hasWrongDoi) > 0) {
            foreach ($hasWrongDoi as $wrong) {
                $client = $wrong['client'];
                $stat = $wrong['stat'];
                $this->log("$client->client_name ($client->client_id) has {$stat['count_db']} wrong DOIs in xml");
            }
        }


    }

    private function getWrongDOIStat(Client $client)
    {
        $doiModel = new Doi;
        $doiModel->setConnection('dois');
        $prod_doi = "10.422";
        $dois = Array();

        $databaseDOIs = $doiModel
            ->where('client_id', $client->client_id)
            ->whereIn('status', ['ACTIVE', 'INACTIVE'])
            ->lists('doi_id','datacite_xml','client_id')
            ->filter(function($item,$xml) {
                return strpos($xml,$item) === false;
            })
            ->filter(function($item,$prod_doi) {
                return strpos($prod_doi,$item) === true;
            })->all();

        foreach($databaseDOIs as $key=>$value){
            $dois[] = $value;
        }

        return [
            'count_db' => count($databaseDOIs),
            'dois' => $dois
        ];
    }

    public function log($message)
    {
        $this->output->writeln($message);
    }


}