<?php


namespace ANDS\Commands\Export;


use ANDS\Commands\ANDSCommand;
use ANDS\DataSource;
use ANDS\Registry\RelationshipView;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Identifier;
use Exception;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ExportCSVGraph extends ANDSCommand
{
//    private $importPath = "/Users/minhd/dev/neo4j/import/";
    private $importPath = "/Users/minhd/dev/ands/amir/";

    protected function configure()
    {
        $this
            ->setName('export:csv-graph')
            ->setDescription('Export the Registry in CSV')
            ->addOption('nodes', null, InputOption::VALUE_NONE, "Nodes")
            ->addOption('direct', null, InputOption::VALUE_NONE, "Direct Relations")
            ->addOption('primary', null, InputOption::VALUE_NONE, "Direct Relations")
            ->addOption('identical', null, InputOption::VALUE_NONE, "Identical Relations")
            ->addOption('relatedInfoRelations', null, InputOption::VALUE_NONE, "Related Info Relations")
            ->addOption('relatedInfoNodes', null, InputOption::VALUE_NONE, "Related Info Nodes");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);
        ini_set('memory_limit','1024M');

        if ($input->getOption('nodes')) {
            return $this->timedActivity("Exporting Nodes", function() {
                return $this->exportNodes();
            });
        }

        if ($input->getOption('direct')){
            return $this->timedActivity("Exporting Direct Relations", function() {
                return $this->exportDirectRelations();
            });
        }

        if ($input->getOption('primary')) {
            return $this->timedActivity("Exporting Primary Relations", function() {
                return $this->exportPrimaryRelations();
            });
        }

        if ($input->getOption('identical')) {
            return $this->timedActivity("Exporting Identical Relations", function() {
                return $this->exportIdenticalRelations();
            });
        }

        return null;
    }

    private $nodes = [
        [
            'key:ID',
            'source',
            'local_id',
            'title',
            'author_list',
            'last_updated',
            'publication_year',
            'url'
        ]
    ];
    private function exportNodes()
    {
        $records = RegistryObject::orderBy('registry_object_id');
        $progressBar = new ProgressBar($this->getOutput(), $records->count());
        $records->chunk(10000, function($records) use ($progressBar) {
            foreach ($records as $record) {
                $this->nodes[] = [
                    'researchgraph.org/ands/'.$record->key,
                    'ands.org.au',
                    $record->key,
                    $record->title,
                    '',
                    $record->getRegistryObjectAttributeValue('updated'),
                    '',
                    $record->portal_url
                ];
                $progressBar->advance(1);
            }
        });
        $progressBar->finish();

        $this->writeToCSV($this->nodes, "nodes");
        unset($this->nodes);
    }

    private $directRelations = [ [':START_ID', ':END_ID', ':TYPE'] ];
    private function exportDirectRelations()
    {
        $allRelations = RelationshipView::where('relation_origin', 'EXPLICIT');
        $progressBar = new ProgressBar($this->getOutput(), $allRelations->count());
        $allRelations->chunk(10000, function($relations) use ($progressBar) {
            foreach ($relations as $relation) {
                $type = str_replace(['{', '}', ' '], '', $relation->relation_type);
                if ($type == '') continue;
                $this->directRelations[] = [
                    'researchgraph.org/ands/'.$relation->from_key,
                    'researchgraph.org/ands/'.$relation->to_key,
                    $type
                ];
                $progressBar->advance(1);
            }
        });
        $progressBar->finish();

        $this->writeToCSV($this->directRelations, "direct");
        unset($this->directRelations);
    }

    private function timedActivity($activity, $closure)
    {
        $this->log("$activity started");
        $stopwatch = new Stopwatch();
        $stopwatch->start('event');
        call_user_func($closure);
        $event = $stopwatch->stop('event');
        $second = $event->getDuration() / 1000;
        $megaBytes = $event->getMemory() / 1000000;
        $this->log("\n$activity completed. duration: {$second}s. Memory Usage: {$megaBytes} MB");
    }

    private function writeToCSV($content, $name)
    {
        $filePath = $this->importPath."$name.csv";
        $fp = fopen($filePath, "w");
        if (!$fp) {
            throw new Exception("$filePath is not writable");
        }
        foreach ($content as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
        $this->log("\n$name written to $filePath\n", "info");
    }

    private $primaryRelations =  [ [':START_ID', ':END_ID', ':TYPE'] ];
    private function exportPrimaryRelations()
    {
        $allRelations = RelationshipView::where('relation_origin', 'PRIMARY');
        $progressBar = new ProgressBar($this->getOutput(), $allRelations->count());
        $allRelations->chunk(500, function($relations) use($progressBar) {
            foreach ($relations as $relation) {
                $type = $this->getPrimaryRelationType($relation);

                $this->primaryRelations[] = [
                    'researchgraph.org/ands/'.$relation->from_key,
                    'researchgraph.org/ands/'.$relation->to_key,
                    $type
                ];
                $progressBar->advance(1);
            }
        });
        $progressBar->finish();

        $this->writeToCSV($this->primaryRelations, "primary");
        unset($this->primaryRelations);
    }

    private function getPrimaryRelationType($relation)
    {
        $defaultType = "hasAssociationWith";
        $ds = DataSource::find($relation->from_data_source_id);
        if (!$ds) {
            $this->log("Data Source {$relation->data_source_id} not found", "error");
            return $defaultType;
        }

        $class = $relation->to_class;
        $key = $relation->to_key;

        if (!$key) {
            $this->log("\n To key not found for relation from:{$relation->from_id} to:{$relation->to_id}");
            return $defaultType;
        }

        if ($ds->getDataSourceAttributeValue("primary_key_1") === $key) {
            $order = 1;
        } elseif ($ds->getDataSourceAttributeValue("primary_key_2") === $key) {
            $order = 2;
        } else {
            $order = null;
            $this->log("\nData Source {$ds->title}does not have primary key to $key", "error");
            return $defaultType;
        }

        $relationType = $ds->getDataSourceAttributeValue("{$class}_rel_$order");

        if ($relationType) {
            return $relationType;
        }

        $this->logv("\nCan't find relationType for class:$class and order:$order for data source {$ds->title}($ds->id), defaulting to $defaultType", "info");
        return $defaultType;
    }

    private $identicalRelations =  [ [':START_ID', ':END_ID', ':TYPE'] ];
    private function exportIdenticalRelations()
    {
        $identifiers = Identifier::selectRaw('identifier, COUNT(*)')
            ->groupBy('identifier')
            ->havingRaw('COUNT(*) > 1')->get();

        $progressBar = new ProgressBar($this->getOutput(), $identifiers->count());
        foreach ($identifiers as $identifier) {
            $ids = Identifier::where('identifier', $identifier->identifier)->pluck('registry_object_id')->unique()->values();

            if (count($ids) < 2) {
                continue;
            }

            // everything relates to the first one
            for ($i = 1; $i <= count($ids) - 1; $i++) {
                $from = RegistryObject::find($ids[0]);
                $to = RegistryObject::find($ids[$i]);
                if ($from && $to) {
                    $this->identicalRelations[] = [
                        'researchgraph.org/ands/'.RegistryObject::find($ids[0])->key,
                        'researchgraph.org/ands/'.RegistryObject::find($ids[$i])->key,
                        'knownAs'
                    ];
                } else {
                    $this->log("Error finding either:{$ids[$i]} or {$ids[0]}", "error");
                }
            }
            $progressBar->advance(1);
        }
        $progressBar->finish();

        $this->writeToCSV($this->identicalRelations, "identical");
        unset($this->identicalRelations);
    }
}