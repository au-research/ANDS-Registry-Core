<?php


namespace ANDS\Commands\RegistryObject;

use ANDS\Commands\ANDSCommand;
use ANDS\Queue\Job\SyncRegistryObjectJob;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegistryObjectSyncCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('ro:sync')
            ->setDescription('Sync a record')
            ->setHelp("This command allows you to sync a record")
            ->addArgument('id', InputArgument::REQUIRED, 'id of the record');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        initEloquent();
        ini_set('memory_limit', '512M');

        $id = $input->getArgument('id');
        $this->log("Syncing RegistryObjectp[id=$id]");

        $job = new SyncRegistryObjectJob();
        $job->init(['registry_object_id' => $id]);
        $job->run();
    }
}