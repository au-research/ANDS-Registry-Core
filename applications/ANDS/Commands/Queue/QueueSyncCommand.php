<?php

namespace ANDS\Commands\Queue;

use ANDS\Commands\ANDSCommand;
use ANDS\Queue\Job\SyncRegistryObjectJob;
use ANDS\Queue\QueueService;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
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
         * php ands.php queue:sync --published-only --data_source_id={} --modified_after={}
         */
        $this
            ->setName('queue:sync')
            ->setDescription('Add Record to Queue')
            ->addOption('published-only', null, InputOption::VALUE_NONE, 'Only include Published RegistryObjects (default: all RegistryObjects in any status)')
            ->addOption('deleted-only', null, InputOption::VALUE_NONE, 'Only include DELETED RegistryObjects (default: all RegistryObjects in any status)')
            ->addOption('data_source_id', null, InputOption::VALUE_OPTIONAL, 'RegistryObjects within this data source', null)
            ->addOption('modified_after', null, InputOption::VALUE_OPTIONAL, 'RegistryObjects modified after a date stamp, "yyyy-mm-dd hh:mm:ss" format', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);
        QueueService::init();

        $ids = RegistryObject::query();

        // include registryObjects with status published-only or deleted-only, not both
        $publishedOnly = $input->getOption('published-only');
        $deletedOnly = $input->getOption('deleted-only');
        if ($publishedOnly) {
            $ids = $ids->where('status', 'PUBLISHED');
            $this->log("Include only PUBLISHED registryObjects");
        } else if ($deletedOnly) {
            $ids = $ids->where('status', 'DELETED');
            $this->log("Include only DELETED registryObjects");
        }

        // include registryObjects belongs to a specific data source by id
        $dataSourceId = $input->getOption('data_source_id');
        if ($dataSourceId) {
            $ids = $ids->where('data_source_id', $dataSourceId);
            $this->log("Include only RegistryObjects from DataSource:$dataSourceId");
        }

        // include registryObjects that is modified after a certain timestamp in yyyy-mm-dd format
        $modifiedAfter = $input->getOption('modified_after');
        if ($modifiedAfter) {
            $dateTimeString = DatesProvider::parseDate($modifiedAfter)->toDateTimeString();
            $ids = $ids->where('modified_at', '>=', $dateTimeString);
            $this->log("Include only RegistryObjects modified after $dateTimeString");
        }

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