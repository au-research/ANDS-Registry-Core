<?php


namespace ANDS\Commands\Mycelium;


use ANDS\Commands\ANDSCommand;
use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\RegistryObject;
use ANDS\Util\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MyceliumImportCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('mycelium:import')
            ->setDescription('Import a Record by ID to mycelium')
            ->setHelp("This command allows you to interact with the mycelium service")
            ->addArgument('id', InputArgument::REQUIRED, 'id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $id = $input->getArgument('id');

        $record = RegistryObject::find($id);

        $client = new MyceliumServiceClient(Config::get('mycelium.url'));
        $result = $client->importRecord($record);
        print_r([
            'http_code' => $result->getStatusCode(),
            'request' => json_decode($result->getBody()->getContents(), true)
        ]);
    }
}