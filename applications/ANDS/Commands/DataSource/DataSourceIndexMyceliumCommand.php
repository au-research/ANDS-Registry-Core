<?php

namespace ANDS\Commands\DataSource;

use ANDS\Commands\ANDSCommand;
use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DataSourceIndexMyceliumCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('ds:index-mycelium')
            ->setDescription('Index a datasource to mycelium')
            ->addArgument('id', InputArgument::REQUIRED, 'id')
            ->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, "offset", 0)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $myceliumURL = Config::get('mycelium.url');
        $this->logv("Mycelium URL: $myceliumURL");
        $client = new MyceliumServiceClient($myceliumURL);

        $dataSourceId = $input->getArgument('id');
        $dataSource = DataSourceRepository::getByID($dataSourceId);

        $offset = $input->getOption('offset');

        $records = RegistryObject::where('data_source_id', $dataSource->id)
            ->limit(100000)->offset($offset)
            ->orderBy('registry_object_id')->pluck('registry_object_id');

        $progressBar = new ProgressBar($this->getOutput(), $records->count());
        $progressBar->setFormat('ands-command');
        $progressBar->start();

        foreach ($records as $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            if (!$record || $record->isDraftStatus()) {
                $progressBar->advance();
                continue;
            }
            $progressBar->setMessage("Importing RegistryObject[id={$id}]");
//            try {
                $client->indexRecord($record);
//            } catch (\Exception $e) {
//                $this->log("Error Indexing RegistryObject[id={$id}]: ". $e->getMessage());
//            }

            $progressBar->advance();
        }
    }
}