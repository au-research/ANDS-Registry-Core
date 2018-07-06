<?php


namespace ANDS\Registry\Providers;


use ANDS\Cache\Cache;
use ANDS\Registry\RelationshipView;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use GraphAware\Common\Result\Result;
use GraphAware\Common\Result\ResultCollection;
use GraphAware\Common\Type\Node;
use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\Formatter\Type\Relationship;
use GraphAware\Neo4j\Client\Stack;

class GraphRelationshipProvider implements RegistryContentProvider
{

    // TODO make this configurable
    protected static $threshold = 20;
    protected static $limit = 100;
    protected static $enableIdentical = true;
    protected static $enableCluster = true;
    protected static $enableGrantsNetwork = true;
    protected static $enableInterlinking = true;
    protected static $defaultTimeout = 60;
    protected static $reverseLinklimit = 200;

    /**
     * normalise the relationships to only display 1 way when any of these relationships are encountered
     *
     * @var array
     * */
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

        // CYPHER queries are pushed onto a stack for processing
        $stack = $client->stack();

        // make sure the current node exists
        $stack->push(static::getMergeNodeQuery($record));

        // delete outgoing relationships, if any is matched
        $stack->push("MATCH (n:RegistryObject {roId:\"{$record->id}\"}) OPTIONAL MATCH (n)-[r]->() DELETE r");

        // direct relationships (after process relationships)
        $relationships = $record->relationships;
        foreach ($relationships as $relationship) {
            // add to node
            $to = RegistryObjectsRepository::getPublishedByKey($relationship->related_object_key);
            if ($to) {
                $stack->push(static::getMergeNodeQuery($to));
                $stack->push(static::getMergeLinkQuery($record, $to, $relationship));
            }
        }

        // reverse links, limitted to prevent supernode breaking the stack limit
        $reverses = RelationshipView::where('to_key', $record->key)
            ->limit(static::$reverseLinklimit)->get();
        foreach ($reverses as $reverse) {
            $to = RegistryObjectsRepository::getPublishedByKey($reverse->from_key);
            if ($to) {
                $stack->push(static::getMergeNodeQuery($to));
                $stack->push(static::getMergeLinkQuery($to, $record, $reverse));
            }
        }

        // related info relationships (after process identifier and process relationships)
        $identifierRelationships = $record->identifierRelationships;
        foreach ($identifierRelationships as $relationship) {

            /** @var RegistryObject\IdentifierRelationship $relationship */
            if ($relationship->resolvesToRecord) {
                // it resolves to a record
                $to = $relationship->getToRecord();
                if (RegistryObjectsRepository::isPublishedStatus($to->status)) {
                    $stack->push(static::getMergeNodeQuery($to));
                    $stack->push(static::getMergeLinkQuery($record, $to, $relationship));
                }
            } else {
                // it resolves to an identifier, make the appropriate links
                $stack->push(static::getMergeRelatedInfoNodeQuery($relationship));
                $stack->push(static::getMergeLinkRelatedInfoQuery($record, $relationship));
            }
        }

        // find identical records and establish identicalTo relations (after process identifier)
        $duplicates = $record->getDuplicateRecords();
        foreach ($duplicates as $duplicate) {

            // make sure the duplicate record exists
            $stack->push(static::getMergeNodeQuery($duplicate));

            // this record is identical to all duplicates
            $stack->push("MATCH (n:RegistryObject {roId:\"{$record->id}\"}) MATCH (i:RegistryObject {roId:\"{$duplicate->id}\"}) MERGE (n)-[:identicalTo]->(i)");
        }

        // insert into neo4j instance

        /** @var Result|ResultCollection $result */
        $result = retry(function() use ($client, $stack){
             return $client->runStack($stack);
        }, 5, 3);
        // retries for 3 times with a delay of 5 seconds in between.
        // This is due to neo4j can run into DEADLOCK issue when multiple threads are updating the same node properties

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

    /**
     * Gives the CYPHER query to ensure a RegistryObject node exists with all relevant properties
     *
     * @param RegistryObject $record
     * @return string
     */
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

    /**
     * Returns the CYPHER query to ensure a RelatedInfo node exists with relevant all properties
     *
     * @param RegistryObject\IdentifierRelationship $relationship
     * @return string
     */
    public static function getMergeRelatedInfoNodeQuery(RegistryObject\IdentifierRelationship $relationship)
    {
        $csv = $relationship->toCSV();
        $data = collect($csv)->except(['identifier:ID', ':LABEL'])->toArray();
        $labels = str_replace(';', ':', $csv[':LABEL']);

        // formats the SETS
        // n.key = 'key', n.group = 'group'
        $sets = [];
        foreach ($data as $key => $value) {
            if ($value) {
                $sets[] = "n.$key = '$value'";
            }
        }
        $sets = implode(', ', $sets);

        $id = $relationship->related_object_identifier;

        // settings properties are cheap on neo4j
        return "MERGE (n:{$labels} {identifier: \"{$id}\" }) ON CREATE SET {$sets} ON MATCH SET {$sets} RETURN n";
    }

    /**
     * Returns a CYPHER query for a link generation between 2 registryObject, with the relationship
     * flips the relationship if applicable
     *
     * @param RegistryObject $record
     * @param RegistryObject $to
     * @param $relationship
     * @return string
     */
    public static function getMergeLinkQuery(RegistryObject $record, RegistryObject $to, $relationship)
    {
        // flip the relation if flippable
        $relation_type = $relationship->relation_type;
        if (in_array($relation_type, array_keys(static::$flippableRelation))) {
            $flipped = static::$flippableRelation[$relation_type];
            return 'MATCH (b:RegistryObject {roId:"'.$to->id.'"}) MATCH (a:RegistryObject {roId:"'.$record->id.'"}) MERGE (b)-[:`'.$flipped.'`]->(a)';
        }

        return 'MATCH (a:RegistryObject {roId:"'.$record->id.'"}) MATCH (b:RegistryObject {roId:"'.$to->id.'"}) MERGE (a)-[:`'.$relation_type.'`]->(b)';
    }

    /**
     * Returns a CYPHER query for a link generation between a registryObject and an IdentifierRelationship
     * flips the relationship if applicable
     *
     * @param RegistryObject $record
     * @param RegistryObject\IdentifierRelationship $relationship
     * @return string
     */
    public static function getMergeLinkRelatedInfoQuery(
        RegistryObject $record,
        RegistryObject\IdentifierRelationship $relationship
    ) {

        $id = $relationship->related_object_identifier;

        // flip the relation if match
        $relation_type = $relationship->relation_type;
        if (in_array($relation_type, array_keys(static::$flippableRelation))) {
            $flipped = static::$flippableRelation[$relation_type];
            return 'MATCH (b:RelatedInfo {identifier:"'.$id.'"}) MATCH (a:RegistryObject {roId:"'.$record->id.'"}) MERGE (b)-[:`'.$flipped.'`]->(a)';
        }

        return 'MATCH (a:RegistryObject {roId:"'.$record->id.'"}) MATCH (b:RelatedInfo {identifier:"'.$id.'"}) MERGE (a)-[:`'.$relationship->relation_type.'`]->(b)';
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function get(RegistryObject $record)
    {
        return static::getByID($record->id);
    }

    /**
     * Return all the relevant nodes and links from getting all relationships for a given node
     * direct relationships (include primary links)
     * identical records (via identicalTo relationship)
     * grants network
     * cluster
     * relationships of result set
     *
     * Returns array of node[] and link[]
     *
     * TODO refactor, this function is a bit too long
     *
     * @param $id
     * @return array
     */
    public static function getByID($id)
    {
        $client = static::db();
        $nodes = [];
        $links = [];

        $node = static::getNodeByID($id);

        // node does not exist, returns default no nodes and links
        if (!$node) {
            return ['nodes' => [], 'links' => []];
        }

        // the direct relations CYPHER query is reused in various places
        $directQuery = "MATCH (n:RegistryObject)-[r]-(direct) WHERE n.roId={id}";
        if (static::$enableIdentical) {
            $directQuery = "MATCH (n:RegistryObject)-[:identicalTo*0..]-(identical:RegistryObject) WHERE n.roId={id}
            WITH collect(identical.roId)+collect(n.roId) AS identicalIDs
            MATCH (n:RegistryObject)-[r]-(direct) WHERE n.roId IN identicalIDs";
        }

        $over = [];
        $under = [];
        if (static::$enableCluster) {
            $counts = static::getCountsByRelationshipsType($id, $directQuery);

            $over = collect($counts)->filter(function($item) {
                return $item['count'] > static::$threshold;
            })->values()->toArray();

            $under = collect($counts)->filter(function($item) {
                return $item['count'] <= static::$threshold;
            })->values()->toArray();

            // get all underThreshold relations that have been clustered
            if (count($under) > 0) {
                $underRelationship = static::getUnderRelationships($id, $directQuery, $under);
                $nodes = collect($nodes)->merge($underRelationship['nodes'])->unique()->toArray();
                $links = collect($links)->merge($underRelationship['links'])->unique()->toArray();
            }

            // get all the over threshold relationshipo and form a cluster for each ones
            if (count($over) > 0) {
                $clusterRelationships = static::getClusterRelationships($node, $over);
                $nodes = collect($nodes)->merge($clusterRelationships['nodes'])->unique()->toArray();
                $links = collect($links)->merge($clusterRelationships['links'])->unique()->toArray();
                $overThresholdRelationships = collect($over)->pluck('relation')->unique()->toArray();
                $directQuery .= ' AND NOT TYPE(r) IN ["'. implode('","', $overThresholdRelationships).'"]';
            }
        }

        // add current node
        $nodes[] = static::formatNode($node);

        // add direct relationships
        $result = $client->run(
            "$directQuery
            RETURN * LIMIT ".static::$limit.";",[
                'id' => (string) $id
            ]);
        foreach ($result->records() as $record) {
            $nodes[] = static::formatNode($record->get('n'));
            $nodes[] = static::formatNode($record->get('direct'));
            $links[] = static::formatRelationship($record->get('r'));
        }

        // add nodes and links from grants network
        if (static::$enableGrantsNetwork) {
            $grantsNetwork = static::getGrantsNetwork($id);
            $nodes = collect($nodes)->merge($grantsNetwork['nodes'])->unique()->toArray();
            $links = collect($links)->merge($grantsNetwork['links'])->unique()->toArray();
        }

        /**
         * These are nodes in the graph (commonly Grants Network)
         * that is already included in the cluster
         * hence duplicating the display of these nodes
         */
        if (static::$enableCluster && static::$enableGrantsNetwork && count($over) > 0) {
            foreach ($over as $cluster) {
                $clusterDuplicateNodes = collect($nodes)
                    ->filter(function($node){
                        return !in_array('cluster', $node['labels']);
                    })
                    ->filter(function($node) use ($cluster) {
                        // has the same class and type as the cluster
                        return $node['properties']['class'] === $cluster['class']
                            && $node['properties']['type'] === $cluster['type'];
                    })
                    ->filter(function($node) use ($links, $cluster){
                        // has exact 1 link
                        // because we want to keep those with multiple discernable relations to other nodes within the graph
                        // the link from these type of nodes that are more than 1 could be a path in the grants network
                        $linkFound = collect($links)
                            ->filter(function($link) use ($cluster, $node){
                                return $link['startNode'] === $node['id'] || $link['endNode'] === $node['id'];
                            })
                            ->count();
                        return $linkFound === 1;
                    });

                // remove cluster duplicate nodes and their relevant links
                $ids = $clusterDuplicateNodes->pluck('id')->toArray();
                $nodes = collect($nodes)
                    ->filter(function($node) use ($ids){
                        return !in_array($node['id'], $ids);
                    })->toArray();
                $links = collect($links)
                    ->filter(function($link) use ($ids){
                        return !(in_array($link['startNode'], $ids) || in_array($link['endNode'], $ids));
                    })->toArray();
            }
        }

        // interlinking relationships
        if (static::$enableInterlinking) {
            // uses the roIDs from all the nodes available currently
            $allNodesIDs = collect($nodes)
                ->pluck('properties')
                ->pluck('roId')
                ->filter(function ($item) use ($id){
                    return $item != $id && $item != "";
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
     * Have to go down the path and up the path. This is essential to form the correct path everywhere in the chain
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

    /**
     * Returns all the links between all given roIDs
     *
     * @param $ids
     * @return array
     */
    public static function getRelationshipsBetweenIDs($ids)
    {
        $client = static::db();
        $result = $client->run("MATCH (n:RegistryObject)-[r]-(n2:RegistryObject) WHERE n2.roId IN {$ids} AND n.roId IN {$ids} RETURN * LIMIT 100;");
        $links = [];
        foreach ($result->records() as $record) {
            $links[] = static::formatRelationship($record->get('r'));
        }
        return $links;
    }

    /**
     * Returns clusterable by relations, labels, class and type
     *
     * @param $id
     * @param $directQuery
     * @return array
     */
    public static function getCountsByRelationshipsType($id, $directQuery)
    {
        $client = static::db();

        $result = $client->run(
            "$directQuery
            RETURN labels(direct) as labels, TYPE(r) as relation, direct.class as class, direct.type as type, count(direct) as total;",[
            'id' => (string) $id
        ]);
        $counts = [];
        foreach ($result->records() as $record) {
            $counts[] = [
                'relation' => $record->get('relation'),
                'labels' => $record->get('labels'),
                'class' => $record->get('class'),
                'type' => $record->get('type'),
                'count' => $record->get('total')
            ];
        }
        return $counts;
    }

    /**
     * Returns the Node for a given roID
     *
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

    /**
     * Format a Node to return
     *
     * @param Node $node
     * @return array
     */
    private static function formatNode(Node $node)
    {
        return [
            'id' => $node->identity(),
            'labels' => $node->labels(),
            'properties' => $node->values()
        ];
    }

    /**
     * Formats a Relationship to return
     *
     * @param Relationship $relationship
     * @return array
     */
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
     * By default uses the REST Http protocol
     * Provides the BOLT protocol as backup.
     * Tests have shown that the BOLT protocol is slower in a lot of test cases
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
            ->setDefaultTimeout(static::$defaultTimeout)
            ->build();
    }

    /**
     * Only the BOLT protocol by default
     *
     * @return \GraphAware\Neo4j\Client\ClientInterface
     */
    public static function bolt()
    {
        $config = Config::get('neo4j');

        return ClientBuilder::create()
            ->addConnection(
                'default',
                "bolt://{$config['username']}:{$config['password']}@{$config['hostname']}:7687"
            )
            ->setDefaultTimeout(static::$defaultTimeout)
            ->build();
    }

    /**
     * Returns the cluster relationship for a node
     * given a list of over relation threshold
     * No calls are being made to neo4j here, this is purely formatting
     *
     * @param Node $node
     * @param array $over
     * @return array
     */
    private static function getClusterRelationships(Node $node, $over)
    {
        $nodes = [];
        $links = [];
        foreach ($over as $rel) {

            $key = md5($rel['relation'] . implode(',', $rel['labels']));
            $labels = array_merge(['cluster'], $rel['labels']);

            // add cluster node
            $nodes[$key] = static::formatNode(new \GraphAware\Neo4j\Client\Formatter\Type\Node(
                    $key, $labels, [
                    'count' => $rel['count'],
                    'class' => $rel['class'],
                    'type' => $rel['type']
                ])
            );

            // add cluster relationship
            $links[$key] = static::formatRelationship(new Relationship(
                rand(1, 999999), $rel['relation'], $node->identity(), $key, [
                    'count' => $rel['count'],
                    'class' => $rel['class'],
                    'type' => $rel['type']
                ]
            ));
        }

        return [
            'nodes' => $nodes,
            'links' => $links
        ];
    }

    /**
     * Gets all of the nodes & links of the relationships that are under the threshold
     * This is necessary because the cluster relationships (over threshold) won't contain these
     *
     * @param $id
     * @param $directQuery
     * @param $under
     * @return array
     */
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
                ->toArray();

            /**
             * CYPHER query will look somewhat like:
             * MATCH (n)-[:identicalTo*0..]-(identical) WHERE n.roId={id}
             * WITH collect(identical.roId)+collect(n.roId) AS identicalIDs
             * MATCH (n)-[r]-(direct) WHERE n.roId IN identicalIDs
             * AND TYPE(r) in ["relatesTo"]
             * AND direct:test
             * AND direct:party
             */
            $query = "$directQuery AND TYPE(r) = {relationship} AND labels(direct) = {labels} RETURN * LIMIT 100";
            $result = $client->run($query, [
                'id' => $id,
                'relationship' => $relationship,
                'labels' => $labels
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