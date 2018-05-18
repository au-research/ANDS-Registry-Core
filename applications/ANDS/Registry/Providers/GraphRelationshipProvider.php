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
     *
     * @param $id
     * @return array
     */
    public static function getByID($id)
    {
        $client = static::db();
        $nodes = [];
        $relationships = [];

        // get direct relationships
        $result = $client->run(
            'MATCH (n)-[:identicalTo*0..]-(nn) WHERE n.roId={id} 
             WITH collect(nn.roId)+collect(n.roId) AS cs
             MATCH (n)-[r]-(n2) WHERE n.roId IN cs RETURN * LIMIT 100;',[
                'id' => $id
            ]);

        foreach ($result->records() as $record) {
            $nodes[$record->get('n')->identity()] = static::formatNode($record->get('n'));
            $nodes[$record->get('n2')->identity()] = static::formatNode($record->get('n2'));
            $relationships[$record->get('r')->identity()] = static::formatRelationship($record->get('r'));
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
            $relationships[$record->get('r')->identity()] = static::formatRelationship($record->get('r'));
        }

        return [
            'nodes' => $nodes,
            'links' => $relationships
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

    public static function getDirect(RegistryObject $record)
    {
        // TODO
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