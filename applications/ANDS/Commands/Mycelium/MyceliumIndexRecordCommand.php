<?php

namespace ANDS\Commands\Mycelium;

use ANDS\Commands\ANDSCommand;
use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MyceliumIndexRecordCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('mycelium:index-record')
            ->setDescription("Index Record's relationships data to Mycelium")
            ->setHelp("This command allows you to interact with the mycelium service")
            ->addArgument('id', InputArgument::OPTIONAL, 'id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $id = $input->getArgument('id');

        $client = new MyceliumServiceClient(Config::get('mycelium.url'));

        if ($id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            $this->timedActivity("Indexing RegistryObject[id=$record->id] to Mycelium",
                function () use ($record, $client) {
                    $this->indexRecord($record, $client);
                });
        } else {
            $this->timedActivity("Indexing published RegistryObject", function () use ($client) {
                $this->indexPublishedRecords($client);
            });
        }
    }

    public function indexRecord(RegistryObject $record, MyceliumServiceClient $client)
    {
        $client->indexRecord($record);
        $this->logv("Imported RegistryObject[id=$record->id] to Mycelium");
    }

    public function indexPublishedRecords(MyceliumServiceClient $client)
    {
        $ids = RegistryObject::where("status", "PUBLISHED")->pluck('registry_object_id');
        $total = count($ids);
        $progressBar = new ProgressBar($this->getOutput(), $total);
        $this->logv("Processing $total records");
        collect($ids)->each(function ($id) use ($client, $progressBar) {
            $record = RegistryObject::find($id);
            $this->indexRecord($record, $client);
            $progressBar->advance();
        });
        $progressBar->finish();
    }
}