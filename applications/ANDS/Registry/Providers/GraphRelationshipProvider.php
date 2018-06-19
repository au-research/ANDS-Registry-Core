<?php


namespace ANDS\Registry\Providers;


use ANDS\Cache\Cache;
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
        'addsValueTo' => 'hasValueAddedBy',
        'describes' => 'isDescribedBy',
        'enriches' => 'isEnrichedBy',
        'hasCollector' => 'isCollectorOf',
        'hasDerivedCollection' => 'isDerivedFrom',
        'hasMember' => 'isMemberOf',
        'hasOutput' => 'isOutputOf',
        'hasPart' => 'isPartOf',
        'hasParticipant' => 'isParticipantIn',
        'hasPrincipalInvestigator' => 'isPrincipalInvestigatorOf',
        'isFunderOf' => 'isFundedBy',
        'makesAvailable' => 'isAvailableThrough',
        'operatesOn' => 'isOperatedOnBy',
        'presents' => 'isPresentedBy',
        'produces' => 'isProducedBy',
        'supports' => 'isSupportedBy',
        'isLocationFor' => 'isLocatedIn',
        'isManagerOf' => 'isManagedBy',
        'isOwnerOf' => 'isOwnedBy',
        'funds' => 'isFundedBy',
        'outputs' => 'isOutputOf',
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

        // delete outgoing relationships
        $stack->push("MATCH (n:RegistryObject {roId:\"{$record->id}\"}) OPTIONAL MATCH (n)-[r]->() DELETE r");

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

        // (after process identifier and process relationships) related info relationships
        $identifierRelationships = $record->identifierRelationships;
        foreach ($identifierRelationships as $relationship) {

            /** @var RegistryObject\IdentifierRelationship $relationship */
            if ($relationship->resolvesToRecord) {
                // it resolves to a record
                $to = $relationship->getToRecord();
                $stack->push(static::getMergeNodeQuery($to));
                $stack->push(static::getMergeLinkQuery($record, $to, $relationship));
            } else {
                // it resolves to an identifier, make the appropriate links
                $stack->push(static::getMergeRelatedInfoNodeQuery($relationship));
                $stack->push(static::getMergeLinkRelatedInfoQuery($record, $relationship));
            }
        }

        // (after process identifier) find identical records and establish identicalTo relations
        $duplicates = $record->getDuplicateRecords();
        foreach ($duplicates as $duplicate) {

            // make sure the duplicate record exists
            $stack->push(static::getMergeNodeQuery($duplicate));

            // this record is identical to all duplicates
            $stack->push("MATCH (n {roId:\"{$record->id}\"}) MATCH (i {roId:\"{$duplicate->id}\"}) MERGE (n)-[:identicalTo]->(i)");
        }

        // insert into neo4j instance
        $result = $client->runStack($stack);

        // todo: queue warm cache for retrieval?

        return $result->updateStatistics();
    }

    /**
     * Delete the node and all of it's relationships
     *
     * @param RegistryObject $record
     * @return \GraphAware\Common\Result\Result
     */
    public static function delete(RegistryObject $record)
    {
        $client = static::db();
        return $client->run("MATCH (n:RegistryObject {roId:\"{$record->id}\"}) OPTIONAL MATCH (n)-[r]-() DELETE n, r");
    }

    /**
     * Bulk delete an array of registryObject
     *
     * @param array $records
     * @return \GraphAware\Common\Result\Result|\GraphAware\Neo4j\Client\Result\ResultCollection|null
     */
    public static function bulkDelete(array $records)
    {
        $client = static::db();
        $stack = $client->stack();
        foreach ($records as $record) {
            return $client->run("MATCH (n:RegistryObject {roId:\"{$record->id}\"}) OPTIONAL MATCH (n)-[r]-() DELETE n, r");
        }
        return $client->runStack($stack);
    }

    public static function getMergeNodeQuery(RegistryObject $record)
    {
        $csv = $record->toCSV(RegistryObject::$CSV_NEO_GRAPH);
        $data = collect($csv)->except(['roId:ID', ':LABEL'])->toArray();
        $labels = str_replace(';', ':', $csv[':LABEL']);

        $sets = [];
        foreach ($data as $key => $value) {
            if ($value) {
                $value = addslashes($value);
                $sets[] = "n.$key = '$value'";
            }
        }
        $sets = implode(', ', $sets);

        return "MERGE (n:{$labels} {roId: \"{$record->id}\" }) ON CREATE SET {$sets} ON MATCH SET {$sets} RETURN n";
    }

    public static function getMergeRelatedInfoNodeQuery(RegistryObject\IdentifierRelationship $relationship)
    {
        $csv = $relationship->toCSV();
        $data = collect($csv)->except(['identifier:ID', ':LABEL'])->toArray();
        $labels = str_replace(';', ':', $csv[':LABEL']);

        $sets = [];
        foreach ($data as $key => $value) {
            if ($value) {
                $sets[] = "n.$key = '$value'";
            }
        }
        $sets = implode(', ', $sets);

        $id = $relationship->related_object_identifier;
        return "MERGE (n:{$labels} {identifier: \"{$id}\" }) ON CREATE SET {$sets} ON MATCH SET {$sets} RETURN n";
    }

    public static function getMergeLinkQuery(RegistryObject $record, RegistryObject $to, $relationship)
    {
        // flip the relation if match
        $relation_type = $relationship->relation_type;
        if (in_array($relation_type, array_keys(static::$flippableRelation))) {
            $flipped = static::$flippableRelation[$relation_type];
            return 'MATCH (b {roId:"'.$to->id.'"}) MATCH (a {roId:"'.$record->id.'"}) MERGE (b)-[:`'.$flipped.'`]->(a)';
        }

        return 'MATCH (a {roId:"'.$record->id.'"}) MATCH (b {roId:"'.$to->id.'"}) MERGE (a)-[:`'.$relation_type.'`]->(b)';
    }

    public static function getMergeLinkRelatedInfoQuery(
        RegistryObject $record,
        RegistryObject\IdentifierRelationship $relationship
    ) {

        $id = $relationship->related_object_identifier;

        // flip the relation if match
        $relation_type = $relationship->relation_type;
        if (in_array($relation_type, array_keys(static::$flippableRelation))) {
            $flipped = static::$flippableRelation[$relation_type];
            return 'MATCH (b:RelatedInfo {identifier:"'.$id.'"}) MATCH (a {roId:"'.$record->id.'"}) MERGE (b)-[:`'.$flipped.'`]->(a)';
        }

        return 'MATCH (a {roId:"'.$record->id.'"}) MATCH (b:RelatedInfo {identifier:"'.$id.'"}) MERGE (a)-[:`'.$relationship->relation_type.'`]->(b)';
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

    /**
     * Return the relationships as part of a grants network
     *
     * TODO refactor to make this cleaner
     *
     * @param $id
     * @return array
     */
    public static function getGrantsNetwork($id)
    {
        $nodes = [];
        $links = [];
        $client = static::db();

        // going down
        $result = $client->run('
            MATCH (n)<-[r:identicalTo|:isPartOf|:hasPart|:isOutputOf|:isProductOf|:isFundedBy*1..]-(n2)
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

        // going up
        $result = $client->run('
            MATCH (n)-[r:identicalTo|:isPartOf|:hasPart|:isOutputOf|:isProductOf|:isFundedBy*1..]->(n2)
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
            ->setDefaultTimeout(10)
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

            $labels = collect($rel['labels'])
                ->flatten(2)
                ->unique()
                ->map(function($label){
                    // fix label having bad character by wrapping with `` eg. direct:`AU-NLA`
                    return "`$label`";
                })->toArray();
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