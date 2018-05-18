<?php


namespace ANDS\Registry\Providers;


use ANDS\RegistryObject;
use ANDS\Util\Config;
use GraphAware\Neo4j\Client\ClientBuilder;

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