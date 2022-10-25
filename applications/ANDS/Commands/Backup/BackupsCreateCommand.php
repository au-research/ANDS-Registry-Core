<?php

namespace ANDS\Commands\Backup;

use ANDS\Commands\ANDSCommand;
use ANDS\Registry\Backup\BackupRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BackupsCreateCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('backups:create')
            ->setDescription('Create a new backup')
            ->setHelp("Create and Store a new Backup")
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'id of the backup, alphanumeric')
            ->addOption('data_source_id', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'data source id to include in the backup')
            ->addOption('include-graphs', null, InputOption::VALUE_OPTIONAL, 'include graph or not', true)
            ->addOption('include-portal-index', null, InputOption::VALUE_OPTIONAL, 'include portal index', true)
            ->addOption('include-relationships-index', null, InputOption::VALUE_OPTIONAL, 'include relationships index', true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $id = $input->getOption('id') ?: uniqid();
        $dataSourceIds = $input->getOption("data_source_id") ?: [];

        $options = [
            'includeGraphs' => $input->getOption('include-graphs'),
            'includePortalIndex' => $input->getOption('include-portal-index'),
            'includeRelationshipsIndex' => $input->getOption('include-relationships-index')
        ];

        BackupRepository::init();
        $result = BackupRepository::create($id, $dataSourceIds, $options);
        $this->assocTable($result);
    }
}