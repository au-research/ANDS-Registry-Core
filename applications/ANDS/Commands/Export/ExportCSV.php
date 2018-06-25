<?php


namespace ANDS\Commands\Export;


use ANDS\Commands\ANDSCommand;
use ANDS\DataSource;
use ANDS\Registry\IdentifierRelationshipView;
use ANDS\Registry\Providers\GraphRelationshipProvider;
use ANDS\Registry\Relation;
use ANDS\Registry\RelationshipView;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Identifier;
use ANDS\Repository\RegistryObjectsRepository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ExportCSV extends ANDSCommand
{
    protected $just = ['nodes', 'relations'];
    private $importPath = null;
    private $format = null;

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
            ->addOption('importPath', 'i', InputOption::VALUE_REQUIRED, "Import Path", env("NEO4J_IMPORT_PATH", "/tmp/"))
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, "Export Format", RegistryObject::$CSV_NEO_GRAPH)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);
        ini_set('memory_limit','512M');

        $this->importPath = $input->getOption("importPath");
        $this->format = $input->getOption('format');

        $this->log("Import Path: {$this->importPath}", "info");

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

    /**
     * Wipe a file
     * TODO: Refactor
     * @param $name
     */
    private function wipe($name)
    {
        $filePath = $this->importPath."$name.csv";
        if (!file_exists($filePath)) {
            touch($filePath);
        }
        $f = @fopen($filePath, "r+");
        if ($f !== false) {
            ftruncate($f, 0);
            fclose($f);
        }
    }


    /**
     * Export nodes to CSV
     */
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
                /* @var $record RegistryObject */

                try {
                    $row = $record->toCSV($this->format);
                    $row[':LABEL'] = str_replace('`', '', $row[':LABEL']);

                    // insert header if first
                    if ($first) {
                        fputcsv($fp, array_keys($row));
                        $first = false;
                    }

                    // stream to file
                    fputcsv($fp, $row);

                } catch (\Exception $e) {
                    $this->log("Failed exporting record {$record->id}: {$e->getMessage()}", "error");
                }

                $progressBar->advance(1);
            }
        });
        $progressBar->finish();
        fclose($fp);

        $this->log("Nodes written to $filePath", "info");
    }

    /**
     * Export Relations to CSV
     */
    private function exportDirectRelations()
    {
        $name = "direct";
        $this->wipe("$name");
        $filePath = $this->importPath."$name.csv";
        $fp = fopen($filePath, "a");

        $allRelations = RelationshipView::where('relation_origin', 'EXPLICIT');
        $progressBar = new ProgressBar($this->getOutput(), $allRelations->count());

        $first = true;
        $allRelations->chunk(10000, function ($relations) use ($progressBar, $fp, &$first) {
            foreach ($relations as $relation) {
                $type = str_replace(['{', '}', ' '], '', $relation->relation_type);
                if ($type == '') {
                    continue;
                }

                $rel = [
                    ':START_ID' => $relation->from_id,
                    ':END_ID' => $relation->to_id,
                    ':TYPE' => $type
                ];

                if ($this->format === RegistryObject::$CSV_RESEARCH_GRAPH) {
                    $rel = [
                        ':START_ID' => 'researchgraph.org/ands/'.$relation->from_id,
                        ':END_ID' => 'researchgraph.org/ands/'.$relation->to_id,
                        ':TYPE' => $type
                    ];
                }

                $rel = $this->postProcessRelation($rel);

                if ($first) {
                    fputcsv($fp, array_keys($rel));
                    $first = false;
                }

                fputcsv($fp, $rel);

                $progressBar->advance(1);
            }
        });
        $progressBar->finish();
        fclose($fp);

        $this->log("Direct Relations written to $filePath", "info");
    }

    private function postProcessRelation($relation)
    {
        $flippableRelations = GraphRelationshipProvider::$flippableRelation;
        if (in_array($relation[':TYPE'], array_keys($flippableRelations))) {
            return [
                ':START_ID' => $relation[':END_ID'],
                ':END_ID' => $relation[':START_ID'],
                ':TYPE' => $flippableRelations[$relation[':TYPE']]
            ];
        }
        return $relation;
    }

    /**
     * Export Primary Relationships
     */
    private function exportPrimaryRelations()
    {
        $name = "primary";
        $this->wipe("$name");
        $filePath = $this->importPath."$name.csv";
        $fp = fopen($filePath, "a");

        $allRelations = RelationshipView::where('relation_origin', 'PRIMARY');
        $progressBar = new ProgressBar($this->getOutput(), $allRelations->count());

        $first = true;
        $allRelations->chunk(500, function($relations) use($progressBar, $fp, &$first) {
            foreach ($relations as $relation) {
                $type = $this->getPrimaryRelationType($relation);

                $rel = [
                    ':START_ID' => $relation->from_id,
                    ':END_ID' => $relation->to_id,
                    ':TYPE' => $type
                ];

                if ($this->format === RegistryObject::$CSV_RESEARCH_GRAPH) {
                    $rel = [
                        ':START_ID' => 'researchgraph.org/ands/'.$relation->from_id,
                        ':END_ID' => 'researchgraph.org/ands/'.$relation->to_id,
                        ':TYPE' => $type
                    ];
                }

                $rel = $this->postProcessRelation($rel);

                if ($first) {
                    fputcsv($fp, array_keys($rel));
                    $first = false;
                }
                fputcsv($fp, $rel);

                $progressBar->advance(1);
            }
        });
        $progressBar->finish();
        fclose($fp);
    }

    /**
     * Get primary relationship type for a particular relation
     * TODO: Refactor to helper
     * @param RelationshipView $relation
     * @return string
     */
    private function getPrimaryRelationType(RelationshipView $relation)
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

    /**
     * Export relations about identical
     */
    private function exportIdenticalRelations()
    {
        $name = "identical";
        $this->wipe("$name");
        $filePath = $this->importPath."$name.csv";
        $fp = fopen($filePath, "a");

        $identifiers = Identifier::selectRaw('identifier, COUNT(*)')
            ->groupBy('identifier')
            ->havingRaw('COUNT(*) > 1')->get();

        $progressBar = new ProgressBar($this->getOutput(), $identifiers->count());

        $first = true;
        foreach ($identifiers as $identifier) {
            $ids = Identifier::where('identifier', $identifier->identifier)
                ->pluck('registry_object_id')->unique()->values();

            if (count($ids) < 2) {
                continue;
            }

            // everything relates to the first one
            for ($i = 1; $i <= count($ids) - 1; $i++) {
                $from = RegistryObject::where('registry_object_id', $ids[0])->where('status', 'PUBLISHED')->first();
                $to = RegistryObject::where('registry_object_id', $ids[$i])->where('status', 'PUBLISHED')->first();
                if ($from && $to) {
                    $relation = [
                        ':START_ID' => $ids[0],
                        ':END_ID' => $ids[$i],
                        ':TYPE' => 'identicalTo'
                    ];

                    if ($this->format === RegistryObject::$CSV_RESEARCH_GRAPH) {
                        $relation = [
                            ':START_ID' => 'researchgraph.org/ands/'.$ids[0],
                            ':END_ID' => 'researchgraph.org/ands/'.$ids[$i],
                            ':TYPE' => 'knownAs'
                        ];
                    }

                    $relation = $this->postProcessRelation($relation);
                    if ($first) {
                        fputcsv($fp, array_keys($relation));
                        $first = false;
                    }
                    fputcsv($fp, $relation);
                }
            }
            $progressBar->advance(1);
        }
        $progressBar->finish();
        fclose($fp);
        $this->log("Identical writen to $filePath\n");
    }

    /**
     * Export RelatedInfo Relations
     */
    private function exportRelatedInfoRelations()
    {
        $name = "relations-relatedInfo";
        $this->wipe("$name");
        $filePath = $this->importPath."$name.csv";
        $fp = fopen($filePath, "a");

        $allRelations = IdentifierRelationshipView::orderBy('from_id');
        $progressBar = new ProgressBar($this->getOutput(), $allRelations->count());
        $first = true;
        $allRelations->chunk(5000, function($relations) use($progressBar, $fp, &$first) {
            foreach ($relations as $relation) {
                $type = $relation->relation_type;

                if (!$type) {
                    continue;
                }

                $rel = [
                    ':START_ID' => $relation->from_id,
                    ':END_ID' => $relation->to_id ? $relation->to_id : md5($relation->to_identifier),
                    ':TYPE' => $type
                ];
                $rel = $this->postProcessRelation($rel);

                if ($first) {
                    fputcsv($fp, array_keys($rel));
                    $first = false;
                }
                fputcsv($fp, $rel);

                $progressBar->advance(1);
            }
        });
        $progressBar->finish();
        $this->log("Related Info Relations has been written to $filePath\n");
    }

    /**
     *
     */
    private function exportRelatedInfoNodes()
    {
        $name = "nodes-relatedInfo";
        $this->wipe("$name");
        $filePath = $this->importPath."$name.csv";
        $fp = fopen($filePath, "a");

        $allRelations = IdentifierRelationshipView::whereNull('to_id')->distinct('to_identifier');
        $progressBar = new ProgressBar($this->getOutput(), $allRelations->count());

        $done = [];
        $first = true;
        $allRelations->chunk(5000, function($relations) use($progressBar, $fp, &$done, &$first) {
            foreach ($relations as $relation) {

                /* @var $relation RegistryObject\IdentifierRelationship */

                if (in_array($relation->to_identifier, $done)) {
                    $progressBar->advance(1);
                    continue;
                }

                $row = $relation->toCSV();
                $row[':LABEL'] = str_replace('`', '', $row[':LABEL']);

                // insert header if first
                if ($first) {
                    fputcsv($fp, array_keys($row));
                    $first = false;
                }

                // stream to file
                fputcsv($fp, $row);
                $done[] = $relation->to_identifier;

                $progressBar->advance(1);
            }
        });
        $progressBar->finish();

        fclose($fp);
        $this->log("Related Info nodes written to $filePath\n");
    }

}