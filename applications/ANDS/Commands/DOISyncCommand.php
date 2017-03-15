<?php


namespace ANDS\Commands;


use ANDS\DOI\DataCiteClient;
use ANDS\DOI\DOIServiceProvider;
use ANDS\DOI\Model\Client;
use ANDS\DOI\Model\Doi;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Repository\DoiRepository;
use ANDS\Util\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DOISyncCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('doi:sync')
            ->setDescription('Sync DOI with Datacite')
            ->setHelp("This command allows you to sync local DOI with remote Datacite server")

            ->addArgument('what', InputArgument::REQUIRED, 'identify|process')
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

        $command = $input->getArgument("what");

        if ($command == "identify") {
            $this->identify($input, $output);
        }

        if ($command == "process") {
            $this->process($input, $output);
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

        foreach ($clients as $client) {
            $stat = $this->getStat($client);
            $display = collect($stat)->filter(function($item, $key) {
                return strpos($key, 'count') !== false;
            })->toArray();
            $this->displayStat($client, $display, $output);
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

        if ($stat['count_datacite_missing'] == 0) {
            $output->writeln("There are no missing DOI on datacite. Aborting");
            return;
        }

        $output->writeln("There are {$stat['count_datacite_missing']} missing DOI on datacite");

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
            ->whereIn('status', ['ACTIVE', 'INACTIVE'])
            ->lists('doi_id')
            ->map(function($item) {
                return strtoupper($item);
            })->filter(function($item) {
                return strpos($item, "10.5072") === false;
            })->toArray();

        $dataciteClient = $this->getDataciteClient($client);

        $allDataciteMintedDOIs = $dataciteClient->get("");
        $allDataciteMintedDOIs = explode("\n", $allDataciteMintedDOIs);

        $allDataciteMintedDOIs = collect($allDataciteMintedDOIs)
            ->filter(function($item) {
                return strpos($item, "10.5072") === false;
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

        $clientUsername = $config['name_prefix'] . "." . $config['name_middle'] . str_pad($client->client_id, 2, '-', STR_PAD_LEFT);
        $dataciteClient = new DataCiteClient(
            $clientUsername,
            $password = $config['password']
        );
        $dataciteClient->setDataciteUrl($config['base_url']);
        return $dataciteClient;
    }


}