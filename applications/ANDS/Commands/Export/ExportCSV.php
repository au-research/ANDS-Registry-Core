<?php


namespace ANDS\Commands\Export;


use ANDS\Commands\ANDSCommand;
use ANDS\DataSource;
use ANDS\Registry\IdentifierRelationshipView;
use ANDS\Registry\RelationshipView;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Identifier;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ExportCSV extends ANDSCommand
{
    protected $just = ['nodes', 'relations'];
    private $importPath = "/Users/minhd/dev/neo4j/import/";

    protected function configure()
    {
        $this
            ->setName('export:csv')
            ->setDescription('Export the Registry in CSV')
            ->addOption('nodes', null, InputOption::VALUE_NONE, "Nodes")
            ->addOption('direct', null, InputOption::VALUE_NONE, "Direct Relations")
            ->addOption('primary', null, InputOption::VALUE_NONE, "Direct Relations")
            ->addOption('identical', null, InputOption::VALUE_NONE, "Identical Relations")
            ->addOption('relatedInfoRelations', null, InputOption::VALUE_NONE, "Related Info Relations")
            ->addOption('relatedInfoNodes', null, InputOption::VALUE_NONE, "Related Info Nodes")
            ->addOption('importPath', $this->importPath, InputOption::VALUE_OPTIONAL, "Import Path")
//            ->addArgument('target', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);
        ini_set('memory_limit','512M');

        $this->importPath = $input->getOption("importPath");

        $nodes = $input->getOption('nodes');
        if ($nodes) {
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

        if ($input->getOption('relatedInfoRelations')) {
            return $this->timedActivity("Exporting RelatedInfo Relations", function() {
               return $this->exportRelatedInfoRelations();
            });
        }
        if ($input->getOption('relatedInfoNodes')) {
            return $this->timedActivity("Exporting RelatedInfo Nodes", function() {
               return $this->exportRelatedInfoNodes();
            });
        }

        // default
        return $this->timedActivity("Export Nodes and Relations", function() {
            $this->exportNodes();
            $this->exportIdenticalRelations();
            $this->exportPrimaryRelations();
            $this->exportRelatedInfoNodes();
            $this->exportRelatedInfoRelations();
            $this->exportDirectRelations();
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
        $megaBytes = $event->getMemory() / 1000000;
        $this->log("\n$activity completed. duration: {$second}s. Memory Usage: {$megaBytes} MB");
    }

    private function writeToCSV($content, $name)
    {
        $filePath = $this->importPath."$name.csv";
        $fp = fopen($filePath, "w");
        foreach ($content as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
        $this->log("\n$name written to $filePath\n", "info");
    }

    private function writeCSVAssoc($content, $name)
    {
        $filePath = $this->importPath."$name.csv";
        $fp = fopen($filePath, "w");

        // write first row with keys
        $header = array_keys($content[0]);
        fputcsv($fp, $header);
        fclose($fp);

        // keep writing
        $fp = fopen($filePath, "a");
        foreach ($content as $fields) {
            fputcsv($fp, $fields);
        }
        $this->log("\n$name written to $filePath\n", "info");
    }

    private function wipe($name)
    {
        $filePath = $this->importPath."$name.csv";
        $f = @fopen($filePath, "r+");
        if ($f !== false) {
            ftruncate($f, 0);
            fclose($f);
        }
    }

    private function stream($name, $content)
    {
        $filePath = $this->importPath."$name.csv";
        $fp = fopen($filePath, "a");
        fputcsv($fp, $content);
    }

    private function sanitizeTitle($title)
    {
        $title = str_replace([',', '"', ';', '\t', ':'], '', $title);

        $title = preg_replace( "/\r|\n/", " ", $title);

        return $title;
    }

    private function exportNodes()
    {
        $this->wipe("nodes");
        $filePath = $this->importPath."nodes.csv";
        $fp = fopen($filePath, "a");

        $records = RegistryObject::where('status', 'PUBLISHED')->orderBy('registry_object_id');
        $progressBar = new ProgressBar($this->getOutput(), $records->count());

        // stream into file
        $first = true;
        $records->chunk(10000, function($records) use ($progressBar, $fp, &$first) {
            foreach ($records as $record) {

                $row = [
                    "roId:ID" => $record->id,
                    ":LABEL" => implode(";", ["RegistryObject", $record->class]),
                    "key" => $record->key,
                    "type" => $record->type,
                    "group" => $record->group,
                    "data_source_id" => $record->data_source_id,
                    "title" => $this->sanitizeTitle($record->title),
                    "record_owner" => $record->record_owner
                ];

                // insert header if first
                if ($first) {
                    fputcsv($fp, array_keys($row));
                    $first = false;
                }

                // stream to file
                fputcsv($fp, $row);

                $progressBar->advance(1);
            }
        });
        $progressBar->finish();
        fclose($fp);

        $this->log("Nodes written to $filePath", "info");

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
                    $relation->from_id,
                    $relation->to_id,
                    $type
                ];
                $progressBar->advance(1);
            }
        });
        $progressBar->finish();

        $this->writeToCSV($this->directRelations, "direct");
        unset($this->directRelations);
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
                    $relation->from_id,
                    $relation->to_id,
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
            ->havingRaw('COUNT(*) > 1');

        foreach ($identifiers->get() as $identifier) {
            $ids = Identifier::where('identifier', $identifier->identifier)->pluck('registry_object_id')->unique()->values();

            if (count($ids) < 2) {
                continue;
            }

            // everything relates to the first one
            for ($i = 1; $i <= count($ids) - 1; $i++) {
                $this->identicalRelations[] = [
                    $ids[0],
                    $ids[$i],
                    'identicalTo'
                ];
            }
        }

        $this->writeToCSV($this->identicalRelations, "identical");
        unset($this->identicalRelations);
    }

    private $relatedInfoRelations =  [ [':START_ID', ':END_ID', ':TYPE'] ];
    private function exportRelatedInfoRelations()
    {
        $allRelations = IdentifierRelationshipView::orderBy('from_id');
        $progressBar = new ProgressBar($this->getOutput(), $allRelations->count());
        $allRelations->chunk(5000, function($relations) use($progressBar) {
            foreach ($relations as $relation) {
                $type = $relation->relation_type;

                if (!$type) {
                    continue;
                }

                $this->relatedInfoRelations[] = [
                    $relation->from_id,
                    $relation->to_id ? $relation->to_id : $relation->to_identifier,
                    $type
                ];
                $progressBar->advance(1);
            }
        });
        $progressBar->finish();

        $this->writeToCSV($this->relatedInfoRelations, "relations-relatedInfo");
        unset($this->relatedInfoRelations);
    }

    private $relatedInfoNodes = [ ['identifier:ID', 'type', 'relatedInfoType',':LABEL'] ];
    private function exportRelatedInfoNodes()
    {
        $allRelations = IdentifierRelationshipView::whereNull('to_id')->distinct('to_identifier')->orderBy('from_id');
        $progressBar = new ProgressBar($this->getOutput(), $allRelations->count());
        $done = [];
        $allRelations->chunk(5000, function($relations) use($progressBar, &$done) {
            foreach ($relations as $relation) {
                if (in_array($relation->to_identifier, $done)) {
                    continue;
                }
                $this->relatedInfoNodes[] = [
                    $relation->to_identifier,
                    $relation->to_identifier_type,
                    $relation->to_related_info_type,
                    'RelatedInfo'
                ];
                $done[] = $relation->to_identifier;
                $progressBar->advance(1);
            }
        });
        $progressBar->finish();

        $this->writeToCSV($this->relatedInfoNodes, "nodes-relatedInfo");
        unset($this->relatedInfoNodes);
    }

}