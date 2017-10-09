<?php


namespace ANDS\Commands\RegistryObject;

use ANDS\RegistryObject;
use ANDS\RegistryObjectAttribute;
use ANDS\Repository\RegistryObjectsRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;

class RegistryObjectSyncCommand extends Command
{

    private $notSynced;
    private $enableLogging = true;
    private $loggingPath = "./logs/ro-sync-reports/";

    protected function configure()
    {
        $this
            ->setName('ro:sync')
            ->setDescription('Sync a record')
            ->setHelp("This command allows you to sync a record")
            ->setDefinition(
                new InputDefinition([
                    new InputOption(
                        'enable_logging',
                        'e',
                        InputOption::VALUE_REQUIRED,
                        'enable logging',
                        $this->enableLogging
                    ),
                    new InputOption(
                        'log_path',
                        'l',
                        InputOption::VALUE_REQUIRED,
                        'logging path',
                        $this->loggingPath
                    )
                ])
            )
            ->addArgument('what', InputArgument::REQUIRED, 'identify|process')
            ->addArgument('id', InputArgument::OPTIONAL, 'id of the record')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        initEloquent();
        ini_set('memory_limit','512M');
        $this->printOptions($input, $output);

        $command = $input->getArgument("what");

        if ($command == "identify") {
            $this->identify($input, $output);
        }

        if ($command == "process") {
            $this->process($input, $output);
        }

        if ($command == "rprocess") {
            $this->randomProcess($input, $output);
        }

        if ($command == "wipe") {
            $this->wipe($input, $output);
        }
    }

    private function wipe(InputInterface $input, OutputInterface $output)
    {
        RegistryObjectAttribute::where('attribute', 'indexed_portal_at')->delete();
        RegistryObjectAttribute::where('attribute', 'processing_by')->delete();
        $this->identify($input, $output);
    }

    private function printOptions(InputInterface $input, OutputInterface $output)
    {
        $this->enableLogging = $input->getOption('enable_logging');
        $this->loggingPath = $input->getOption('log_path');
        if ($this->enableLogging) {
            $output->writeln("Logging is enabled. Path: $this->loggingPath");
            return;
        }

        $output->writeln("Logging is disabled");
    }

    private function identify(InputInterface $input, OutputInterface $output)
    {
        if ($id = $input->getArgument('id')) {
            // Single record
            $output->writeln("Identifying $id");
            $record = RegistryObjectsRepository::getRecordByID($id);
            // TODO
            return;
        }

        $output->writeln("Identifying all records");

        $total = RegistryObject::where('status', 'PUBLISHED')->count();
        $output->writeln("There are $total PUBLISHED records");

        $synced = RegistryObject::where('status', 'PUBLISHED')
            ->whereHas('registryObjectAttributes', function($query){
                return $query
                    ->where('attribute', 'indexed_portal_at');
            });
        $syncedCount = $synced->count();
        $output->writeln("There are {$syncedCount} synced records");

        $notSynced = RegistryObject::where('status', 'PUBLISHED')
            ->whereHas('registryObjectAttributes', function($query){
                return $query
                    ->where('attribute', 'indexed_portal_at');
            }, '<', 1);
        $notSyncedCount = $notSynced->count();
        $output->writeln("There are {$notSyncedCount} un-synced records");

        $syncedPercentage = $syncedCount / $notSyncedCount * 100;
        $output->writeln("Sync Percentage: {$syncedPercentage}%");

        $this->notSynced = $notSynced;
    }

    private function randomProcess(InputInterface $input, OutputInterface $output)
    {
        // get a random record that does not have an attribute indexed_portal_at

        $record = $this->getARandomUnprocessedRecord();
        while ($record != NULL) {
            $output->writeln("Processing $record->id");
            $this->processRecord($record);
            $record = $this->getARandomUnprocessedRecord();
        }
    }

    private function getARandomUnprocessedRecord()
    {
        return RegistryObject::where('status', 'PUBLISHED')
            ->wherehas('registryObjectAttributes', function($query){
                return $query
                    ->where('attribute', 'indexed_portal_at');
            }, '<', 1)->whereHas('registryObjectAttributes', function($query){
                return $query->where('attribute', 'processing_by');
            }, '<', 1)->first();
    }

    private function process(InputInterface $input, OutputInterface $output)
    {
        if ($id = $input->getArgument('id')) {
            // Single record
            $output->writeln("Processing $id");
            $record = RegistryObjectsRepository::getRecordByID($id);
            if (!$record) {
                $output->writeln("Record $id does not exist");
                return;
            }
            $this->processRecord($record);
            return;
        }

        $this->identify($input, $output);

        $count = $this->notSynced->count();
        $output->writeln("There are $count not synced records");

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Proceed to fix? [y|N] : ', false);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln("Aborting.");
            return;
        }

        $ids = $this->notSynced->lists('registry_object_id');
        $progressBar = new ProgressBar($output, $this->notSynced->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        foreach ($ids as $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            $this->processRecord($record);
            $progressBar->advance(1);
        }
        $progressBar->finish();

        $output->writeln("Finished");

        $this->identify($input, $output);

    }

    private function processRecord(RegistryObject $record)
    {
        // TODO: Workaround CI limitation, have to call internal API
        $client = new Client([
            'base_uri' => baseUrl("api/registry/object/"),
            'timeout'  => 360,
        ]);

        try {

            // mark record
            $record->setRegistryObjectAttribute('processing_by', uniqid());

            $response = $client->get(
                baseUrl("api/registry/object/$record->id/sync")
            );

            // unmark record
            $record->deleteRegistryObjectAttribute('processing_by');

            $body = (string) $response->getBody();
            $this->writeLog($record, $body);
        } catch (RequestException $e) {
            $this->writeLog($record, $e->getMessage(), "ERROR-");
        }
    }

    private function writeLog(RegistryObject $record, $result, $append = "")
    {
        if (!$this->enableLogging) {
            return;
        }
        $fs = new Filesystem();
        $fs->dumpFile($this->loggingPath.'/'.$append.$record->id, $result);
    }
}