<?php


namespace ANDS\Commands\DataSource;


use ANDS\Commands\ANDSCommand;
use ANDS\Registry\Providers\DCI\DataCitationIndexProvider;
use ANDS\Registry\Providers\GraphRelationshipProvider;
use ANDS\Registry\Providers\LinkProvider;
use ANDS\Registry\Providers\Quality\QualityMetadataProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\SubjectProvider;
use ANDS\Registry\Providers\Scholix\ScholixProvider;
use ANDS\Registry\Providers\RIFCS\TitleProvider;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;
use ReflectionMethod;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DataSourceProcessCommand extends ANDSCommand
{
    protected $processors = [
        'scholix' => ScholixProvider::class,
        'subject' => SubjectProvider::class,
        'quality' => QualityMetadataProvider::class,
        'links' => LinkProvider::class,
        'relationship' => RelationshipProvider::class,
        'title' => TitleProvider::class,
        'core' => CoreMetadataProvider::class,
        'date' => DatesProvider::class,
        'graph' => GraphRelationshipProvider::class,
        'dci' => DataCitationIndexProvider::class
    ];

    protected function configure()
    {
        $this
            ->setName('ds:process')
            ->setDescription('Run process on all data source records')
            ->setHelp("This command allows you to run provider:process on data source by id")
            ->addArgument('what', InputArgument::REQUIRED, implode('|', array_keys($this->processors)))
            ->addArgument('id', InputArgument::REQUIRED, 'id')
            ->addOption('all', null, InputOption::VALUE_NONE, "Process All Records")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $process = $input->getArgument('what');
        $id = $input->getArgument('id');

        if (!in_array($process, array_keys($this->processors))) {
            $this->log("Unknown process $process, available process: " .implode('|', array_keys($this->processors)));
            return;
        }

        $records = RegistryObject::where('data_source_id', $id)
            ->where('status', 'PUBLISHED')
            ->orderBy('registry_object_id');

        if ($input->getOption('all')) {
            $records = RegistryObject::where('data_source_id', $id)
                ->orderBy('registry_object_id');
        }

        return $this->timedActivity("Process {$process} on data source: {$id}",
            function () use ($id, $records, $process) {
                $progressBar = new ProgressBar($this->getOutput(), $records->count());
                $records->chunk(1000, function ($records) use ($progressBar, $process) {
                    foreach ($records as $record) {
                        try {
                            $this->logd("Processing $process on {$record->id}");
                            $processMethod = new ReflectionMethod($this->processors[$process], 'process');
                            $processMethod->invoke(new $this->processors[$process], $record);
                            $this->logd("Success $process on ({$record->id})", "info");
                        } catch (\Exception $e) {
                            $this->log("Error process {$process} for record: {$record->id}: {$e->getMessage()}", "error");
                            continue;
                        }
                        $progressBar->advance(1);
                    }
                });
                $progressBar->finish();
                $this->log("Process {$process} done on data source: {$id}");
            }
        );
    }
}