<?php


namespace ANDS\Registry\Providers;


use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use GraphAware\Common\Type\Node;
use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\Formatter\Type\Relationship;
use GraphAware\Neo4j\Client\Stack;

class GraphRelationshipProvider implements RegistryContentProvider
{

    protected static $threshold = 20;
    protected static $limit = 100;
    protected static $enableIdentical = true;
    protected static $enableCluster = true;
    protected static $enableGrantsNetwork = true;
    protected static $enableInterlinking = true;

    public static $flippableRelation = [
        'describes' => 'isDescribedBy',
        'hasPart' => 'isPartOf',
        'hasCollector' => 'isCollectorOf',
        'funds' => 'isFundedBy',
        'isFunderOf' => 'isFundedBy',
        'outputs' => 'isOutputOf',
        'hasOutput' => 'isOutputOf'
    ];

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        $client = static::db();
        $stack = $client->stack();

        // current node
        $stack->push(static::getMergeNodeQuery($record));

        // relationships should already be available in database
        $relationships = $record->relationships;
        foreach ($relationships as $relationship) {
            // add to node
            $to = RegistryObjectsRepository::getPublishedByKey($relationship->related_object_key);
            if ($to) {
                $stack->push(static::getMergeNodeQuery($to));
                $stack->push(static::getMergeLinkQuery($record, $to, $relationship));
            }
        }

//        $identifierRelationships = $record->identifierRelationships;
//        foreach ($identifierRelationships as $relationship) {
//            // TODO add relatedInfo node
//            // TODO add relationship
//        }

        // (after process identifier) find identical records and establish identicalTo relations
        // insert into neo4j instance
        $result = $client->runStack($stack);

        return $result->updateStatistics();
    }

    public static function getMergeNodeQuery(RegistryObject $record)
    {
        $csv = $record->toCSV(RegistryObject::$CSV_NEO_GRAPH);
        $data = collect($csv)->except(['roId:ID', ':LABEL'])->toArray();
        $labels = str_replace(';', ':', $csv[':LABEL']);

        $sets = [];
        foreach ($data as $key => $value) {
            if ($value) {
                $sets[] = "n.$key = '$value'";
            }
        }
        $sets = implode(', ', $sets);

        return "MERGE (n:{$labels} {roId: \"{$record->id}\" }) ON CREATE SET {$sets} ON MATCH SET {$sets} RETURN n";
    }

    public static function getMergeLinkQuery(RegistryObject $record, RegistryObject $to, $relationship)
    {
        return 'MATCH (a {roId:"'.$record->id.'"}) MATCH (b {roId:"'.$to->id.'"}) MERGE (a)-[:'.$relationship->relation_type.']->(b)';
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function get(RegistryObject $record)
    {
        // TODO: Implement get() method.
        // get direct nodes and relationships (covers primary links)
        // get direct relationships for identicalTo records
        // get grants relationships path
        // get relationships between result set
        return static::getByID($record->id);

    }

    /**
     * Return all the relevant nodes and links from getting all relationships for a given node
     * direct relationships (include primary links)
     * identical records (via identicalTo relationship)
     * relationships of result set
     * @param $id
     * @return array
     */
    public static function getByID($id)
    {
        $client = static::db();
        $nodes = [];
        $links = [];

        $node = static::getNodeByID($id);
        if (!$node) {
            return ['nodes' => [], 'links' => []];
        }

        // TODO: if not found, return default

        $directQuery = "MATCH (n)-[r]-(direct) WHERE n.roId={id}";
        if (static::$enableIdentical) {
            $directQuery = "MATCH (n)-[:identicalTo*0..]-(identical) WHERE n.roId={id}
            WITH collect(identical.roId)+collect(n.roId) AS identicalIDs
            MATCH (n)-[r]-(direct) WHERE n.roId IN identicalIDs";
        }

        if (static::$enableCluster) {
            $counts = static::getCountsByRelationshipsType($id, $directQuery);

            $over = collect($counts)->filter(function($item) {
                return $item['count'] > static::$threshold;
            })->toArray();

            $under = collect($counts)->filter(function($item) {
                return $item['count'] <= static::$threshold;
            })->toArray();

            // get all underThreshold relations that have been clustered
            if (count($under)) {
                $underRelationship = static::getUnderRelationships($id, $directQuery, $under);
                $nodes = collect($nodes)->merge($underRelationship['nodes'])->unique()->toArray();
                $links = collect($links)->merge($underRelationship['links'])->unique()->toArray();
            }

            if (count($over) > 0) {
                $clusterRelationships = static::getClusterRelationships($node, $over);
                $nodes = collect($nodes)->merge($clusterRelationships['nodes'])->unique()->toArray();
                $links = collect($links)->merge($clusterRelationships['links'])->unique()->toArray();

                $overThresholdRelationships = collect($over)->pluck('relation')->toArray();
                $directQuery .= ' AND NOT TYPE(r) IN ["'. implode('","', $overThresholdRelationships).'"]';
            }
        }

        // get direct relationships
        $result = $client->run(
            "$directQuery
            RETURN * LIMIT ".static::$limit.";",[
                'id' => (string) $id
            ]);

        foreach ($result->records() as $record) {
            $nodes[$record->get('n')->identity()] = static::formatNode($record->get('n'));
            $nodes[$record->get('direct')->identity()] = static::formatNode($record->get('direct'));
            $links[$record->get('r')->identity()] = static::formatRelationship($record->get('r'));
        }

        // grants network
        if (static::$enableGrantsNetwork) {
            $grantsNetwork = static::getGrantsNetwork($id);
            $nodes = collect($nodes)->merge($grantsNetwork['nodes'])->unique()->toArray();
            $links = collect($links)->merge($grantsNetwork['links'])->unique()->toArray();
        }

        // get relationships of records in result set
        if (static::$enableInterlinking) {
            $allNodesIDs = collect($nodes)
                ->pluck('properties')
                ->pluck('roId')
                ->filter(function ($item) use ($id){
                    return $item != $id;
                })
                ->map(function($item) {
                    return "$item";
                })->toArray();
            $allNodesIDs = '["'. implode('","', $allNodesIDs).'"]';

            $links = collect($links)
                ->merge(static::getRelationshipsBetweenIDs($allNodesIDs))
                ->unique()
                ->toArray();
        }

        return [
            'nodes' => $nodes,
            'links' => $links
        ];
    }

    public static function getGrantsNetwork($id)
    {
        $nodes = [];
        $links = [];
        $client = static::db();
        $result = $client->run('
            MATCH (n)-[r:identicalTo|isPartOf|:hasPart|:isProductOf|:isFundedBy*1..]->(n2) 
            WHERE n.roId={id}
            RETURN * LIMIT 100', [
                'id' => $id
            ]);
        foreach ($result->records() as $record) {
            $nodes[$record->get('n')->identity()] = static::formatNode($record->get('n'));
            $nodes[$record->get('n2')->identity()] = static::formatNode($record->get('n2'));
            $relations = $record->get('r');
            if (is_array($relations)) {
                foreach ($relations as $relation) {
                    $links[$relation->identity()] = static::formatRelationship($relation);
                }
            } else {
                $links[$relations->identity()] = static::formatRelationship($relations);
            }
        }
        return [
            'nodes' => $nodes,
            'links' => $links
        ];
    }

    public static function getRelationshipsBetweenIDs($ids)
    {
        $client = static::db();
        $result = $client->run("MATCH (n)-[r]-(n2) WHERE n2.roId IN {$ids} AND n.roId IN {$ids} RETURN * LIMIT 100;");
        $links = [];
        foreach ($result->records() as $record) {
            $links[$record->get('r')->identity()] = static::formatRelationship($record->get('r'));
        }
        return $links;
    }

    public static function getCountsByRelationshipsType($id, $directQuery)
    {
        $client = static::db();

        $result = $client->run(
            "$directQuery
            RETURN labels(direct) as labels, TYPE(r) as relation, count(direct) as total;",[
            'id' => (string) $id
        ]);
        $counts = [];
        foreach ($result->records() as $record) {
            $counts[] = [
                'relation' => $record->get('relation'),
                'labels' => $record->get('labels'),
                'count' => $record->get('total')
            ];
        }
        return $counts;
    }

    public static function getCountsByRelationships($id, $directQuery)
    {
        $client = static::db();

        $result = $client->run(
            "$directQuery
            RETURN TYPE(r) as relation, count(direct) as total;",[
            'id' => (string) $id
        ]);
        $counts = [];
        foreach ($result->records() as $record) {
            $counts[$record->get('relation')] = $record->get('total');
        }
        return $counts;
    }

    /**
     * @param $id
     * @return Node
     */
    public static function getNodeByID($id)
    {
        $client = static::db();
        $result = $client->run("MATCH (n {roId: {roId}}) RETURN n", ['roId' => (string) $id]);
        if (count($result->records()) == 0) {
            return null;
        }
        return $result->firstRecord()->get('n');
    }

    private static function formatNode(Node $node)
    {
        return [
            'id' => $node->identity(),
            'labels' => $node->labels(),
            'properties' => $node->values()
        ];
    }

    private static function formatRelationship($relationship)
    {
        return [
            'id' => $relationship->identity(),
            'startNode' => $relationship->startNodeIdentity(),
            'endNode' => $relationship->endNodeIdentity(),
            'type' => $relationship->type(),
            'properties' => array_merge($relationship->values(), [])
        ];
    }


    /**
     * The graph database instance
     *
     * @return \GraphAware\Neo4j\Client\ClientInterface
     */
    public static function db()
    {
        $config = Config::get('neo4j');

        return ClientBuilder::create()
            ->addConnection(
                'default',
                "http://{$config['username']}:{$config['password']}@{$config['hostname']}:7474"
            )
            ->addConnection(
                'bolt',
                "bolt://{$config['username']}:{$config['password']}@{$config['hostname']}:7687"
            )
            ->build();
    }

    private static function getClusterRelationships($node, $over)
    {
        $nodes = [];
        $links = [];
        foreach ($over as $rel) {

            $key = md5($rel['relation'].implode(',',$rel['labels']));
            $labels = array_merge(['cluster'], $rel['labels']);

            // add cluster node
            $nodes[$key] = static::formatNode(new \GraphAware\Neo4j\Client\Formatter\Type\Node(
                    $key, $labels, [ 'count' => $rel['count'] ])
            );

            // add cluster relationship
            $links[$key] = static::formatRelationship(new Relationship(
                rand(1,999999), $rel['relation'], $node->identity(), $key, [
                    'count' => $rel['count']
                ]
            ));
        }

        return [
            'nodes' => $nodes,
            'links' => $links
        ];
    }

    private static function getUnderRelationships($id, $directQuery, $under)
    {
        $client = static::db();
        $nodes = [];
        $links = [];

        foreach ($under as $rel) {

            $relationship = $rel['relation'];

            $labels = collect($rel['labels'])->flatten(2)->unique()->toArray();
            $labels = 'AND direct:'. implode(' AND direct:', $labels);

            /**
             * MATCH (n)-[:identicalTo*0..]-(identical) WHERE n.roId={id}
             * WITH collect(identical.roId)+collect(n.roId) AS identicalIDs
             * MATCH (n)-[r]-(direct) WHERE n.roId IN identicalIDs
             * AND TYPE(r) in ["relatesTo"]
             * AND direct:test
             * AND direct:party
             */

            $query = "$directQuery AND TYPE(r) = {relationship} $labels RETURN * LIMIT 100";
            $result = $client->run($query, [
                'id' => $id,
                'relationship' => $relationship
            ]);

            foreach ($result->records() as $record) {
                $nodes[$record->get('n')->identity()] = static::formatNode($record->get('n'));
                $nodes[$record->get('direct')->identity()] = static::formatNode($record->get('direct'));
                $links[$record->get('r')->identity()] = static::formatRelationship($record->get('r'));
            }
        }

        return [
            'nodes' => $nodes,
            'links' => $links
        ];
    }
}