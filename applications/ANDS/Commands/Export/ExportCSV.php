<?php


namespace ANDS\Commands\Export;


use ANDS\Commands\ANDSCommand;
use ANDS\Registry\RelationshipView;
use ANDS\RegistryObject;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ExportCSV extends ANDSCommand
{
    protected $just = ['nodes', 'relations'];
    protected function configure()
    {
        $this
            ->setName('export:csv')
            ->setDescription('Export the Registry in CSV')
            ->addOption('nodes', null, InputOption::VALUE_NONE, "Just Nodes")
            ->addOption('relations', null, InputOption::VALUE_NONE, "Just Nodes")
//            ->addArgument('target', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);
        ini_set('memory_limit','512M');

        $nodes = $input->getOption('nodes');
        if ($nodes) {
            return $this->timedActivity("Exporting Nodes", function() {
                return $this->exportNodes();
            });
        }

        if ($input->getOption('relations')){
            return $this->timedActivity("Exporting Relations", function() {
                return $this->exportRelations();
            });
        }

        // default
        return $this->timedActivity("Export Nodes and Relations", function() {
            $this->exportNodes();
            $this->exportRelations();
        });
    }

    private function timedActivity($activity, $closure)
    {
        $this->log("$activity started");
        $stopwatch = new Stopwatch();
        $stopwatch->start('event');
        call_user_func($closure);
        $event = $stopwatch->stop('event');
        $second = $event->getDuration() / 1000;
        $this->log("$activity completed. duration: {$second}s. Memory Usage: {$event->getMemory()}");
    }

    private $importPath = "/Users/minhd/dev/neo4j/import/";
    private $nodes = [ ['roId:ID', 'class', ':LABEL'] ];
    private function exportNodes()
    {
        $records = RegistryObject::orderBy('registry_object_id');
        $progressBar = new ProgressBar($this->getOutput(), $records->count());
        $records->chunk(10000, function($records) use ($progressBar) {
            foreach ($records as $record) {
                $this->nodes[] = [
                    $record->id, $record->class, 'RegistryObject'
                ];
                $progressBar->advance(1);
            }
        });
        $progressBar->finish();
        $filePath = $this->importPath."nodes.csv";
        $fp = fopen($filePath, "w");
        foreach ($this->nodes as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
        $this->log("Nodes written to $filePath");
        $this->nodes = [];
    }

    private function exportRelations()
    {
        $allRelations = RelationshipView::orderBy('from_id');
        $progressBar = new ProgressBar($this->getOutput(), $allRelations->count());
        $allRelations->chunk(10000, function($relations) use ($progressBar) {
            foreach ($relations as $relation) {
                $this->relations[] = [
                    $relation->from_id,
                    $relation->to_id,
                    str_replace(['{', '}', ' '], '', $relation->relation_type)
                ];
                $progressBar->advance(1);
            }
        });
        $progressBar->finish();

        // write csv
        $importPath = "/Users/minhd/dev/neo4j/import/";
        $fileName = "relations.csv";
        $filePath = $importPath.$fileName;
        $fp = fopen($filePath, "w");
        foreach ($this->relations as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
        $this->log("$fileName written to $filePath");
    }

}