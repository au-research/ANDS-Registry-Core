<?php


namespace ANDS\Commands;


use ANDS\RegistryObject;
use ANDS\RegistryObjectAttribute;
use ANDS\Task\SyncRecordTask;
use ANDS\Util\Config;
use Illuminate\Support\Facades\Input;
use MinhD\SolrClient\SolrClient;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Predis\Client;
use Simpleue\Queue\RedisQueue;
use Simpleue\Worker\QueueWorker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class SyncRecordWorkerRedisCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('queue-work:redis')
            ->setDescription('Show in the terminal messages received redis queue')
            ->addArgument('what', InputArgument::REQUIRED, 'identify|process')
            ->addOption(
                'queue',
                null,
                InputOption::VALUE_REQUIRED,
                'Queue name',
                'ands.task-queue'
            )

            ->addOption(
                'host',
                null,
                InputOption::VALUE_REQUIRED,
                'Redis host',
                'localhost'
            )
            ->addOption(
                'port',
                null,
                InputOption::VALUE_REQUIRED,
                'Redis port',
                6379
            )
            ->addOption(
                'database',
                null,
                InputOption::VALUE_REQUIRED,
                'Redis database',
                0
            )
        ;
    }
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

        if ($command == "identify-index") {
            $this->identifyIndex($input, $output);
        }

        if ($command == "wipe") {
            $this->wipe($input, $output);
        }

    }

    private function wipe(InputInterface $input, OutputInterface $output)
    {
        // delete all indexed_portal_at
        RegistryObjectAttribute::where('attribute', 'indexed_portal_at')->delete();
        $output->writeln("Done");
    }

    private function process(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting...</info>');
        $redisClient = new Client(array(
            'host' => $input->getOption('host'),
            'port' => $input->getOption('port'),
            'database' => $input->getOption('database'),
            'schema' => 'tcp'
        ));
        $redisQueue = new RedisQueue($redisClient, $input->getOption('queue'));
        //        $logger = new Logger('ConsoleMessage');
        //        $logger->pushHandler(new StreamHandler(__DIR__.'/../../logs/redis_console_message.log', Logger::INFO));
        $consoleMessagesWorker = new QueueWorker($redisQueue, new SyncRecordTask($output));
        //        $consoleMessagesWorker->setLogger($logger);
        $consoleMessagesWorker->start();
        $output->writeln('<info>End.</info>');
    }

    private function identify(InputInterface $input, OutputInterface $output)
    {
//        $notSynced = RegistryObject::where('status', 'PUBLISHED')
//            ->whereHas('registryObjectAttributes', function($query){
//                return $query
//                    ->where('attribute', 'indexed_portal_at');
//            }, '<', 1);
//        $notSyncedCount = $notSynced->count();
//        $output->writeln("There are {$notSyncedCount} un-synced records");
//
//        $helper = $this->getHelper('question');
//        $question = new ConfirmationQuestion('Proceed to fix? [y|N] : ', false);
//        if (!$helper->ask($input, $output, $question)) {
//            $output->writeln("Aborting.");
//            return;
//        }

        // write to ands.task-queue
        $redisClient = new Client(array(
            'host' => $input->getOption('host'),
            'port' => $input->getOption('port'),
            'database' => $input->getOption('database'),
            'schema' => 'tcp'
        ));
        $redisQueue = new RedisQueue($redisClient, $input->getOption('queue'));
//        $notSyncedIDs = $notSynced->pluck('registry_object_id');
        $notSyncedIDs = [];

        $dataSourceID = 96;
        $DBIDs = RegistryObject::where('data_source_id', $dataSourceID)->where('status', 'PUBLISHED')->pluck('registry_object_id')->toArray();
        $output->writeln("Found ". count($DBIDs) . " PUBLISHED records for data source $dataSourceID in the database");
        $solrIDs = [];
//        $solrClient = new SolrClient(Config::get('app.solr_url'));
//        $solrClient->setCore('portal');
//        $result = $solrClient->search([
//            'q' => "data_source_id:$dataSourceID",
//            'rows' => 20000,
//            'fl' => 'id'
//        ])->getDocs();
//        foreach ($result as $doc) {
//            $solrIDs[] = $doc->id;
//        }

        $notSyncedIDs = array_diff($DBIDs, $solrIDs);

        foreach ($notSyncedIDs as $id) {
            $redisQueue->sendJob(
                json_encode([
                    'record_id' => $id
                ])
            );
            $output->writeln("Sent $id to queue");
        }
    }

    private function identifyIndex(InputInterface $input, OutputInterface $output)
    {
        $DBPublished = RegistryObject::where('status', 'PUBLISHED');
        $output->writeln("There are {$DBPublished->count()} PUBLISHED records");

        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore('portal');
        $numFound = $solrClient->search([
            'q' => '*:*',
        ])->getNumFound();
        $output->writeln("There are {$numFound} Indexed records");

        $result = $solrClient->search([
            'q' => '*:*',
            'fl' => 'id',
            'rows' => $numFound
        ]);
        $ids = [];
        foreach ($result->getDocs() as $doc) {
            $ids[] = $doc->id;
        }

        $DBPublished = $DBPublished->pluck('registry_object_id')->toArray();
        $diffLeft = array_diff($DBPublished, $ids);
        $diffLeftCount = count($diffLeft);
        $output->writeln("There are {$diffLeftCount} records that needs indexed");

        $diffRight = array_diff($ids, $DBPublished);
        $diffRightCount = count($diffRight);
        $output->writeln("There are {$diffRightCount} index that needs removal");

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Proceed to remove {$diffRightCount} index? [y|N] : ", false);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln("Aborting.");
            return;
        }

        // chunk at 200 each
        $chunks = collect($diffRight)->chunk(200);
        $total = count($chunks);
        foreach ($chunks as $index => $chunk) {
            $query = "id:(". implode(' OR ', $chunk->toArray()). ")";
            $solrClient->removeByQuery($query);
            $i = $index + 1;
            $output->writeln("Processed $i/$total");
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Proceed to fix {$diffLeftCount} unindexed records ? [y|N] : ", false);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln("Aborting.");
            return;
        }

        // Fix diffLeft
        $output->writeln("Sending {$diffLeftCount} records to index queue");
        $redisClient = new Client(array(
            'host' => $input->getOption('host'),
            'port' => $input->getOption('port'),
            'database' => $input->getOption('database'),
            'schema' => 'tcp'
        ));
        $redisQueue = new RedisQueue($redisClient, $input->getOption('queue'));
        foreach ($diffLeft as $id) {
            $redisQueue->sendJob(
                json_encode([
                    'record_id' => $id
                ])
            );
            $output->writeln("Sent $id to queue");
        }

        // TODO: Fix diffRight
    }
}