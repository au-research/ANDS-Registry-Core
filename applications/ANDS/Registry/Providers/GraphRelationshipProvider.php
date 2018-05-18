<?php


namespace ANDS\Registry\Providers;


use ANDS\RegistryObject;
use ANDS\Util\Config;
use GraphAware\Common\Type\Node;
use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\Formatter\Type\Relationship;

class GraphRelationshipProvider implements RegistryContentProvider
{

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
        // read rifcs and insert direct relationships
        // (after process identifier) find identical records and establish identicalTo relations
        // insert into neo4j instance
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

        // get node id by roID
        $result = $client->run("MATCH (n {roId: {roId}}) RETURN n", ['roId' => $id]);
        $node = $result->firstRecord()->get('n');

        // TODO: if not found, return default

        $directQuery = "MATCH (n)-[:identicalTo*0..]-(identical) WHERE n.roId={id}
            WITH collect(identical.roId)+collect(n.roId) AS identicalIDs
            MATCH (n)-[r]-(direct) WHERE n.roId IN identicalIDs";

        // get direct relationships count
        $result = $client->run(
            "$directQuery
            RETURN TYPE(r) as relation, count(direct) as total;",[
                'id' => $id
            ]);
        $counts = [];
        foreach ($result->records() as $record) {
            $counts[$record->get('relation')] = $record->get('total');
        }

        $threshold = 20;

        $over = collect($counts)->filter(function($item) use ($threshold) {
            return $item > $threshold;
        })->toArray();

        $clusterRelationship = count($over) > 0 ? array_keys($over) : [];

        if (count($clusterRelationship) > 0) {
            $directQuery .= ' AND NOT TYPE(r) IN ["'. implode('","', $clusterRelationship).'"]';

            foreach ($clusterRelationship as $rel) {
                // add cluster node
                $nodes["Cluster$rel"] = static::formatNode(new \GraphAware\Neo4j\Client\Formatter\Type\Node(
                    "Cluster$rel",
                    ["RegistryObject", "cluster"],
                    [
                        "roId" => 'asdf',
                        'count' => $counts[$rel]
                    ])
                );

                // add cluster relationship
                $links["Cluster$rel"] = static::formatRelationship(new Relationship(
                    rand(1,999999), $rel, $node->identity(), "Cluster$rel", [
                        'count' => $counts[$rel]
                    ]
                ));
            }

        }


        // get direct relationships
        $result = $client->run(
            "$directQuery
            RETURN * LIMIT 100;",[
                'id' => $id
            ]);

        foreach ($result->records() as $record) {
            $nodes[$record->get('n')->identity()] = static::formatNode($record->get('n'));
            $nodes[$record->get('direct')->identity()] = static::formatNode($record->get('direct'));
            $links[$record->get('r')->identity()] = static::formatRelationship($record->get('r'));
        }

        // grants network
        $result = $client->run('
        MATCH (n)-[r:identicalTo|isPartOf|:hasPart|:produces|:isFundedBy|:funds*1..]-(n2) 
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

        // get relationships of records in result set
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

        $result = $client->run("MATCH (n)-[r]-(n2) WHERE n2.roId IN {$allNodesIDs} AND n.roId IN {$allNodesIDs} RETURN * LIMIT 100;");

        foreach ($result->records() as $record) {
            $links[$record->get('r')->identity()] = static::formatRelationship($record->get('r'));
        }

        return [
            'nodes' => $nodes,
            'links' => $links
        ];
    }

    private static function formatNode(Node $node)
    {
        return [
            'id' => $node->identity(),
            'labels' => $node->labels(),
            'properties' => $node->values()
        ];
    }

    private static function formatRelationship(Relationship $relationship)
    {
        return [
            'id' => $relationship->identity(),
            'startNode' => $relationship->startNodeIdentity(),
            'endNode' => $relationship->endNodeIdentity(),
            'type' => $relationship->type(),
            'properties' => array_merge($relationship->values(), ['from' => rand(1,100)])
        ];
    }


    public static function getGrantsNetwork(RegistryObject $record)
    {
        // TODO
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
                "http://{$config['username']}:{$config['password']}@{$config['hostname']}:7687"
            )
            ->build();
    }
}