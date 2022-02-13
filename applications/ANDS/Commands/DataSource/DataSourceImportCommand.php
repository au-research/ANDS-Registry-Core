<?php

namespace ANDS\Commands\DataSource;

use ANDS\Commands\ANDSCommand;
use ANDS\DataSource;
use ANDS\DataSourceAttribute;
use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\RegistryObjectAttribute;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DataSourceImportCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('ds:import')
            ->setDescription('Import a datasource from a directory')
            ->setHelp("This command allows you to import a data source from file")
            ->addOption('from', 'f', InputOption::VALUE_REQUIRED, "Import Path", "");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);
        $from = $input->getOption('from');

        // import dataSource
        $dataSourceFile = $from .'/dataSource.json';
        $dataSourceExport = json_decode(file_get_contents($dataSourceFile), true);

        $dataSourceMeta = $dataSourceExport['metadata'];
        DataSource::unguard(true);
        $dataSource = DataSource::firstOrCreate($dataSourceMeta);

        $dataSourceAttributes = $dataSourceExport['attributes'];
        foreach ($dataSourceAttributes as $attribute) {
            $dataSource->setDataSourceAttribute($attribute['attribute'], $attribute['value']);
        }

        // import records
        $recordsDirectory = $from .'/records';
        $files = scandir($recordsDirectory);

        $progressBar = new ProgressBar($this->getOutput(), count($files));
        $progressBar->setFormat('ands-command');
        $progressBar->start();

        foreach ($files as $file) {

            if ($file == '.' || $file == '..') continue;

            $filePath = $recordsDirectory.'/'.$file;
            $progressBar->setMessage("Importing $filePath");

            $recordExport = json_decode(file_get_contents($filePath), true);

            $id = $recordExport['metadata']['registry_object_id'];
            if (RegistryObject::where('registry_object_id')->exists()) {
                $progressBar->setMessage("RegistryObject[id=$id] exists. Skipping...");
                $progressBar->advance(1);
                continue;
            }

            RegistryObject::unguard(true);
            unset($recordExport['metadata']['registry_object_attributes']);
            $record = RegistryObject::firstOrCreate($recordExport['metadata']);
            foreach ($recordExport['attributes'] as $attribute) {
                $record->setRegistryObjectAttribute($attribute['attribute'], $attribute['value']);
            }
            RecordData::firstOrCreate([
                'registry_object_id' => $record->id,
                'current' => TRUE,
                'data' => base64_decode($recordExport['xml'])
            ]);
            $progressBar->advance(1);
        }
    }
}