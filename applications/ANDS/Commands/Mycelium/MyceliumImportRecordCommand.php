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

class MyceliumImportRecordCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('mycelium:import-record')
            ->setDescription('Import Record to Mycelium')
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
            $this->timedActivity("Importing RegistryObject[id=$record->id] to Mycelium",
                function () use ($record, $client) {
                    $this->importRecord($record, $client);
                });
        } else {
            $this->timedActivity("Importing all Published Records", function() use ($client) {
                $this->importPublishedRecords($client);
            });
        }
    }

    public function importRecord(RegistryObject $record, MyceliumServiceClient $client) {
        $client->importRecord($record);
        $this->logv("Imported RegistryObject[id=$record->id] to Mycelium");
    }

    public function importPublishedRecords(MyceliumServiceClient $client) {
        $ids = RegistryObject::where("status", "PUBLISHED")->pluck('registry_object_id');
        $total = count($ids);
        $progressBar = new ProgressBar($this->getOutput(), $total);
        $this->logv("Processing $total records");

        // get a request import id
        $result = $client->createNewImportRecordRequest(uniqid());
        $request = json_decode($result->getBody()->getContents(), true);
        $myceliumRequestId = $request['id'];

        collect($ids)->each(function($id)  use ($client, $progressBar, $myceliumRequestId){
            $record = RegistryObject::find($id);
            $this->importRecord($record, $client, $myceliumRequestId);
            $progressBar->advance();
        });

        $progressBar->finish();
    }
}