<?php

namespace ANDS\Commands\Queue;

use ANDS\Commands\ANDSCommand;
use ANDS\Queue\Job\SyncRegistryObjectJob;
use ANDS\Queue\QueueService;
use ANDS\RegistryObject;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueSyncCommand extends ANDSCommand
{
    protected function configure()
    {
        /**
         * php ands.php queue:sync --published-only --data_source_id={} --queue={} --log_path={} -q
         */
        $this
            ->setName('queue:sync')
            ->setDescription('Add Record to Queue')
            ->addOption('published-only', null, InputOption::VALUE_NONE, 'Only include Published RegistryObjects (default: all RegistryObjects in any status)')
            ->addOption('deleted-only', null, InputOption::VALUE_NONE, 'Only include DELETED RegistryObjects (default: all RegistryObjects in any status)')
            ->addOption('data_source_id', null, InputOption::VALUE_OPTIONAL, 'RegistryObjects within this data source', null)
            ->addOption('queue', null, InputOption::VALUE_OPTIONAL, "Target Queue ID", null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);
        QueueService::init();

        $publishedOnly = $input->getOption('published-only');
        $deletedOnly = $input->getOption('deleted-only');
        $dataSourceId = $input->getOption('data_source_id');

        $ids = RegistryObject::query();
        if ($publishedOnly) {
            $ids = $ids->where('status', 'PUBLISHED');
            $this->log("Include only PUBLISHED registryObjects");
        } else if ($deletedOnly) {
            $ids = $ids->where('status', 'DELETED');
            $this->log("Include only DELETED registryObjects");
        }

        if ($dataSourceId) {
            $ids = $ids->where('data_source_id', $dataSourceId);
            $this->log("Include only RegistryObjects from DataSource:$dataSourceId");
        }

        // todo queue
        // todo modified_after

        $ids = $ids->pluck('registry_object_id');
        $total = $ids->count();

        if ($total === 0) {
            $this->log("No records match!");
            return;
        }

        $this->log("Queueing $total RegistryObjects");

        $progressBar = new ProgressBar($this->getOutput(), $total);
        $progressBar->setFormat('ands-command');
        $progressBar->start();
        foreach ($ids as $id) {
            $job = new SyncRegistryObjectJob();
            $job->init(['registry_object_id' => $id]);
            QueueService::push($job);
            $progressBar->setMessage("Queued Job[class=SyncRegistryObjectJob, registryObjectId=$id]");
            $progressBar->advance();
        }
        $progressBar->setMessage("Done");
        $progressBar->finish();
        $this->log("\nQueued $total RegistryObjects to to the default queue");
    }
}