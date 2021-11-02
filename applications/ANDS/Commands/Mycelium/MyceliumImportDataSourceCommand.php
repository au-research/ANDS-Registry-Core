<?php

namespace ANDS\Commands\Mycelium;

use ANDS\Commands\ANDSCommand;
use ANDS\DataSource;
use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\RegistryObject;
use ANDS\Util\Config;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MyceliumImportDataSourceCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('mycelium:import-datasource')
            ->setDescription('Import Datasource(s) to Mycelium')
            ->setHelp("This command allows you to import data sources to Mycelium Service")
            ->addArgument('id', InputArgument::OPTIONAL, 'id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $id = $input->getArgument('id');


        $myceliumURL = Config::get('mycelium.url');
        $this->logv("Mycelium URL: $myceliumURL");
        $client = new MyceliumServiceClient($myceliumURL);
        if (!$id) {
            $this->importAllDataSources($client);
        } else {
            $this->importDataSource(DataSource::find($id), $client);
        }
    }

    private function importDataSource(DataSource $dataSource, MyceliumServiceClient $client)
    {
        $this->logv("Importing DataSource ID: $dataSource->id");
        $result = $client->updateDataSource($dataSource);
        $this->logv($result->getBody());
        $this->logv("Importing DataSource ID: $dataSource->id to Mycelium Completed");
    }

    private function importAllDataSources(MyceliumServiceClient $client)
    {
        $this->logv("Importing All DataSources");

        $dataSources = DataSource::all();
        $progressBar = new ProgressBar($this->getOutput(), $dataSources->count());
        $dataSources->each(function ($ds) use ($progressBar, $client) {
            $this->importDataSource($ds, $client);
            $progressBar->advance();
        });
        $progressBar->finish();
        $this->log("\nImporting All DataSource to Mycelium Completed");
    }
}