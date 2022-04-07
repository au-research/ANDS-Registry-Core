<?php

namespace ANDS\Commands\Backup;

use ANDS\Commands\ANDSCommand;
use ANDS\DataSource;
use ANDS\Registry\Backup\Backup;
use ANDS\Registry\Backup\BackupRepository;
use ANDS\Repository\DataSourceRepository;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
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
            ->addOption('data_source_id', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'data source id to include in the backup');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $id = $input->getOption('id') ?: uniqid();
        $dataSourceIds = $input->getOption("data_source_id") ?: [];
        $title = "No title";
        $description = "No Description";
        $authors = [
            [
                'name' => 'SYSTEM',
                'email' => null
            ]
        ];
        // todo check dataSourceIds existence

        $dataSources = collect($dataSourceIds)->map(function($id) {
            return DataSourceRepository::getByID($id);
        })->filter(function($dataSource) {
            return $dataSource != null;
        });

        BackupRepository::init();
        $backup = Backup::create($id, $title, $description, $authors, $dataSources);
        $result = BackupRepository::storeBackup($backup);

        $this->assocTable($result);
    }
}