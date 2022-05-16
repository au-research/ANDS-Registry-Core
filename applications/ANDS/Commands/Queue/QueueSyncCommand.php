<?php

namespace ANDS\Commands\Queue;

use ANDS\Commands\ANDSCommand;
use ANDS\Queue\Job\SyncRegistryObjectJob;
use ANDS\Queue\QueueService;
use ANDS\RegistryObject;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueSyncCommand extends ANDSCommand
{
    protected function configure()
    {
        /**
         * php ands.php queue:sync --published --draft --data_source_id={} --queue={} --log_path={} -q
         */
        $this
            ->setName('queue:sync')
            ->setDescription('Add Record to Queue')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        QueueService::init();
        $ids = RegistryObject::where('status', 'PUBLISHED')->pluck('registry_object_id');

        foreach ($ids as $id) {
            $job = new SyncRegistryObjectJob();
            $job->init(['registry_object_id' => $id]);
            QueueService::push($job);
            $this->log("Queued Job[class=SyncRegistryObjectJob, registryObjectId=$id]");
        }
    }
}