<?php


namespace ANDS\Commands\Graph;


use ANDS\Commands\ANDSCommand;
use ANDS\Registry\RelationshipView;
use ANDS\RegistryObject;
use GraphAware\Neo4j\Client\ClientBuilder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class GraphGenerate extends ANDSCommand
{

    /** @var \GraphAware\Neo4j\Client\ClientInterface */
    private $client = null;

    /** @var Stopwatch */
    private $stopwatch = null;

    protected function configure()
    {
        $this
            ->setName('graph:generate')
            ->setDescription('Generate the graph database to neo4j')
//            ->setHelp("This command allows you to export the registry in different formats")
//            ->addArgument('what', InputArgument::REQUIRED, 'registry')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);
        ini_set('memory_limit','512M');

        // setup client
        $this->client = ClientBuilder::create()
//            ->addConnection('default', 'http://neo4j:abc123@localhost:7474')
            ->addConnection('bolt', 'bolt://neo4j:abc123@localhost:7687')
            ->build();

//        $this->timedActivity("writeRelationsToCSV");
        $this->timedActivity("writeImportableCSV");
//        $this->timedActivity("createIndex");
//        $this->timedActivity("indexNodesFromFile");
//        $this->timedActivity("indexRelationsFromFile");
    }

    public function indexNodes()
    {
        $nodeStack = $this->client->stack();
        RegistryObject::where('status', 'PUBLISHED')
            ->limit(1000)
            ->pluck('registry_object_id')
            ->chunk(500)->each(function($ids) use ($nodeStack) {
                foreach ($ids as $id) {
                    $record = RegistryObject::find($id);
                    $nodeStack->push('CREATE (n:RegistryObject {id: {id} })', [
                        'id' => $record->id
                    ]);
                }
                $this->log('.');
            });

        $this->client->runStack($nodeStack);
    }

    private $importPath = "/Users/minhd/dev/neo4j/import/";
    private $nodes = [['roId:ID', 'class', ':LABEL']];
    private $relations = [[":START_ID", ":END_ID", ":TYPE"]];
    private function writeImportableCSV()
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

        return;
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


    private $csvContent = [];
    private function writeRelationsToCSV()
    {
        // collect data
        $this->csvContent = [
            ['from_id', 'to_id', 'relation']
        ];
        $allRelations = RelationshipView::orderBy('from_id');
        $progressBar = new ProgressBar($this->getOutput(), $allRelations->count());
        $allRelations->chunk(10000, function($relations) use ($progressBar) {
            foreach ($relations as $relation) {
                $this->csvContent[] = [
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
        foreach ($this->csvContent as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
        $this->log("$fileName written to $filePath");
    }

    private function createIndex()
    {
        $this->client->run("CREATE INDEX ON :RegistryObject(id)");
        $this->client->run("CREATE INDEX ON :REL(type)");
    }

    private function indexNodesFromFile()
    {
        $this->client->run("USING PERIODIC COMMIT 10000
LOAD CSV WITH HEADERS FROM \"file:///relations.csv\" AS line WITH line
MERGE (from:RegistryObject { id: line.from_id })
MERGE (to:RegistryObject { id: line.to_id })");
    }

    private function indexRelationsFromFile()
    {
        $this->client->run("USING PERIODIC COMMIT 10000
LOAD CSV WITH HEADERS FROM \"file:///relations.csv\" AS line WITH line
MATCH (from { id: line.from_id }), (to { id: line.to_id }) 
MERGE (from)-[:REL {type:line.relation}]->(to)");
    }

    private function indexRelations()
    {
        $stopwatch = new Stopwatch();
        $stack = $this->client->stack();

        $allRelations = RelationshipView::orderBy('from_id');
        $progressBar = new ProgressBar($this->getOutput(), $allRelations->count());
        $allRelations->chunk(5000, function($relations) use ($stack, $progressBar, $stopwatch) {
            foreach ($relations as $relation) {
                $relType = $relation->relation_type;
                $relType = str_replace(['{', '}', ' '], '', $relType);
                $stack->push('MERGE (n:RegistryObject {id: {id} })', ['id' => $relation->from_id]);
                $stack->push('MERGE (n:RegistryObject {id: {id} })', ['id' => $relation->to_id]);
                $stack->push("MATCH (from:RegistryObject {id: {from_id}}), (to:RegistryObject {id:{to_id}}) MERGE (from)-[r:{$relType}]->(to)", [
                    'from_id' => $relation->from_id,
                    'to_id' => $relation->to_id
                ]);

                $progressBar->advance(1);

                $count = $stack->size();
                if ($count > 20000) {
                    $this->log("Flushing...");
                    $stopwatch->start('stack');
                    $this->client->runStack($stack);
                    $event = $stopwatch->stop('stack');
                    $second = $event->getDuration() / 1000;
                    $this->log("Flushed {$count} relations. duration: {$second}s.");
                }
            }
        });
        $progressBar->finish();
    }
}