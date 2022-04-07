<?php

namespace ANDS\Commands\DataSource;

use ANDS\Commands\ANDSCommand;
use ANDS\DataSource;
use ANDS\RegistryObject;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DataSourceExportCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('ds:export')
            ->setDescription('Export a datasource to a directory')
            ->setHelp("This command allows you to export a data source to file")
            ->addArgument('id', InputArgument::REQUIRED, 'id')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'target directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        // todo check if dataSource by id exists
        // todo check if to path exists & writable

        $to = $input->getOption('to');
        if (!is_dir($to)) {
            mkdir($to);
        }

        // export data source meta & attributes
        $filePath = $to . '/' . 'dataSource.json';
        $dataSource = DataSource::find($input->getArgument('id'));
        $this->timedActivity("Exporting dataSource[id={$dataSource->id}]", function () use ($dataSource, $filePath) {
            $this->logv("Exporting dataSource[id={$dataSource->id}, title={$dataSource->title}] to $filePath");
            if (is_file($filePath)) {
                $this->logv("$filePath already exists. Skipping...");
                return;
            }
            $attributes = $dataSource->dataSourceAttributes->toArray();
            $exported = [
                'metadata' => $dataSource->toArray(),
                'attributes' => $attributes
            ];
            unset($exported['metadata']['data_source_attributes']);
            file_put_contents($filePath, json_encode($exported));
        });

        // export the registryObjects
        $recordsDirPath = $to . '/records';
        if (!is_dir($recordsDirPath)) {
            mkdir($recordsDirPath);
        }

        $records = RegistryObject::where('data_source_id', $dataSource->id)->orderBy('registry_object_id')->pluck('registry_object_id');
        $count = $records->count();
        $this->timedActivity("Exporting {$count} registryObjects to directory: $recordsDirPath", function () use ($records, $recordsDirPath) {
            $progressBar = new ProgressBar($this->getOutput(), $records->count());
            foreach ($records as $id) {
                $this->logv("Exporting Record[id={$id}]");
                $filePath = $recordsDirPath . "/{$id}.json";
                if (is_file($filePath)) {
                    $this->logv("$filePath already exists. Skipping...");
                    $progressBar->advance(1);
                    continue;
                }
                $record = RegistryObject::find($id);
                $exported = [
                    'metadata' => $record,
                    'attributes' => $record->registryObjectAttributes->toArray(),
                    'xml' => base64_encode($record->getCurrentData()->data)
                ];
                unset($exported['metadata']['registry_object_attributes']);
                file_put_contents($filePath, json_encode($exported));
                $progressBar->advance(1);
            }
        });
    }
}