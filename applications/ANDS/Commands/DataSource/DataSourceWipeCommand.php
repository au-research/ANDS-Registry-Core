<?php

namespace ANDS\Commands\DataSource;

use ANDS\Commands\ANDSCommand;
use ANDS\Registry\Importer;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DataSourceWipeCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('ds:wipe')
            ->setDescription('Wipe a datasource clean')
            ->setHelp("This command allows you to wipe an entire data source from database, SOLR, Mycelium")
            ->addArgument('id', InputArgument::REQUIRED, 'id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        // delete every registry objects for this data source
        $dataSourceId = $input->getArgument('id');
        $dataSource = DataSourceRepository::getByID($dataSourceId);

        Importer::wipeDataSourceRecords($dataSource);
    }

}