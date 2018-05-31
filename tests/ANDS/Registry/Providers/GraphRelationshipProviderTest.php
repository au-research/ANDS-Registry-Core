<?php

namespace ANDS\Registry\Providers;

use ANDS\RegistryObject;
use GraphAware\Neo4j\Client\ClientInterface;
use GraphAware\Neo4j\Client\Stack;

class GraphRelationshipProviderTest extends \RegistryTestClass
{

    /** @var ClientInterface */
    private $client = null;

    /** @test */
    function it_should_build_a_client()
    {
        $this->assertInstanceOf(ClientInterface::class, $this->client);
    }

    /** @test */
    function it_should_be_able_to_search_for_nodes()
    {
        // given A isPartOf B
        $stack = $this->client->stack();
        $stack = $this->addNodes($stack, [
            'collection' => ['A', 'B']
        ]);
        $stack = $this->addRelations($stack, [
            ['A', 'isPartOf', 'B']
        ]);
        $stack->push('MERGE (n:test {roId: {roId} }) RETURN n', ['roId' => 'A'], 'a');
        $stack->push('MERGE (n:test {roId: {roId} }) RETURN n', ['roId' => 'B'], 'b');
        $stack->push('MATCH (a:test {roId: "A"}) MATCH (b:test {roId: "B"}) MERGE (a)-[:isPartOf]->(b)');
        $this->client->runStack($stack);

        // search for A isPartOf B works
        $result = $this->client->run('MATCH p = (a:test {id: "A"})-[]-(b:test {id: "B"}) RETURN p');

        foreach ($result->records() as $record) {
            $path = $record->get('p');
            $this->assertEquals(2, count($path->nodes()));
            $this->assertEquals(1, count($path->relationships()));
            $relationship = array_first($path->relationships());
            $this->assertEquals('isPartOf', $relationship->type());
        }
    }

    /** @test */
    function it_should_be_able_to_get_direct_relationship()
    {
        // given A isPartOf B, C hasAssociationWith A, B hasPart C
        $stack = $this->client->stack();
        $stack = $this->addNodes($stack, [
            'collection' => ['A', 'A2', 'B', 'C']
        ]);
        $stack = $this->addRelations($stack, [
            ['A', 'isPartOf', 'B'],
            ["C", "hasAssociationWith", "A"],
            ["B", "hasPart", "C"]
        ]);
        $results = $this->client->runStack($stack);

        $graph = GraphRelationshipProvider::getByID("A");

        $a = $results->get('a')->firstRecord()->get('n');
        $b = $results->get('b')->firstRecord()->get('n');
        $c = $results->get('c')->firstRecord()->get('n');

        // nodes should include a, b and c
        $nodes = $graph['nodes'];
        $this->assertCount(3, $nodes);
        $ids = collect($nodes)->pluck('properties')->pluck('roId');
        $this->assertContains("A", $ids);
        $this->assertContains("B", $ids);
        $this->assertContains("C", $ids);

        // links should include a->b, c->a and b->c
        $links = $graph['links'];

        $this->assertCount(3, $links);

        // links should include a->b
        $a2b = collect($links)->filter(function($item) use ($a, $b){
            return $item['startNode'] == $a->identity() && $item['endNode'] == $b->identity();
        })->first();
        $this->assertEquals('isPartOf', $a2b['type']);

        // c->a
        $c2a = collect($links)->filter(function($item) use ($a, $c){
            return $item['startNode'] == $c->identity() && $item['endNode'] == $a->identity();
        })->first();
        $this->assertEquals('hasAssociationWith', $c2a['type']);

        // b->c
        $b2c = collect($links)->filter(function($item) use ($b, $c){
            return $item['startNode'] == $b->identity() && $item['endNode'] == $c->identity();
        })->first();
        $this->assertEquals('hasPart', $b2c['type']);
    }

    /** @test */
    function get_identical_relationships()
    {
        // given a->b, a identical to a2, a2->c
        $stack = $this->client->stack();
        $stack = $this->addNodes($stack, [
            "collection" => ["A", "A2", "B", "C"]
        ]);
        $stack = $this->addRelations($stack, [
            ["A", "rel", "B"],
            ["A", "identicalTo", "A2"],
            ["A2", "hasPart", "C"]
        ]);
        $results = $this->client->runStack($stack);

        $a2 = $results->get('a2')->firstRecord()->get('n');
        $c = $results->get('c')->firstRecord()->get('n');

        $graph = GraphRelationshipProvider::getByID("A");
        $links = $graph['links'];
        // links should include a2->c
        $a22c = collect($links)->filter(function($item) use ($a2, $c){
            return $item['startNode'] == $a2->identity() && $item['endNode'] == $c->identity();
        })->first();
        $this->assertEquals("hasPart", $a22c['type']);
    }

    /** @test */
    function get_grants_relationships()
    {
        // given a:collection<-:produces->b:activity->:isFundedBy->c:party
        $stack = $this->client->stack();
        $stack = $this->addNodes($stack, [
            'collection' => ['A'],
            'activity' => ['B'],
            'party' => ['C']
        ]);
        $stack = $this->addRelations($stack, [
            ["A", "isProductOf", "B"],
            ["B", "isFundedBy", "C"]
        ]);
        $results = $this->client->runStack($stack);

        $b = $results->get('b')->firstRecord()->get('n');
        $c = $results->get('c')->firstRecord()->get('n');

        $graph = GraphRelationshipProvider::getByID("A");
        $links = $graph['links'];

        // links should include b->c
        $b2c = collect($links)->filter(function($item) use ($b, $c){
            return $item['startNode'] == $b->identity() && $item['endNode'] == $c->identity();
        })->first();
        $this->assertEquals("isFundedBy", $b2c['type']);
    }

    /** @test */
    function get_identical_grants_relationships()
    {
        //given a:collection-:identicalTo->a2:collection<-produces-b:activity-:identicalTo->b2:activity<-funds-c:party
        $stack = $this->client->stack();
        $stack = $this->addNodes($stack, [
            'collection' => ['A', 'A2'],
            'activity' => ['B', 'B2'],
            'party' => ['C']
        ]);
        $stack = $this->addRelations($stack, [
            ["A", "identicalTo", "A2"],
            ["A2", "isProductOf", "B"],
            ["B", "identicalTo", "B2"],
            ["B2", "isFundedBy", "C"]
        ]);
        $results = $this->client->runStack($stack);

        $b2 = $results->get('b2')->firstRecord()->get('n');
        $c = $results->get('c')->firstRecord()->get('n');

        $graph = GraphRelationshipProvider::getByID("A");
        $links = $graph['links'];

        // links should include b2<-c
        $b22c = collect($links)->filter(function($item) use ($b2, $c){
            return $item['startNode'] == $b2->identity() && $item['endNode'] == $c->identity();
        })->first();
        $this->assertEquals("isFundedBy", $b22c['type']);
    }

    /** @test */
    function get_supernode()
    {
        // given a party relates to 100 collection
        $stack = $this->client->stack();
        $stack = $this->addNode($stack, 'party', 'P');
        for ($i = 0; $i < 100; $i++) {
            $stack = $this->addNode($stack, 'collection', "C$i");
            $stack = $this->addRelation($stack, "C$i", "hasAssociationWith", "P");
        }
        // and 5 activities
        for ($i = 0; $i < 5; $i++) {
            $stack = $this->addNode($stack, 'activity', "A$i");
            $stack = $this->addRelation($stack, "A$i", "isFundedBy", "P");
        }
        $this->client->runStack($stack);

        // when viewing the relationships
        $graph = GraphRelationshipProvider::getByID('P');
        $nodes = $graph['nodes'];
        $links = $graph['links'];

        // it should show those 5 activites
        $ids = collect($nodes)->pluck('properties')->pluck('roId');
        for ($i = 0; $i < 5; $i++) {
            $this->assertContains("A$i", $ids);
        }

        // it should only show a cluster of those 100 collections as relationship
        $cluster = collect($nodes)->filter(function($item) {
            return in_array("cluster", $item['labels']);
        })->first();
        $this->assertNotNull($cluster);

        $clusterLink = collect($links)->filter(function($item) use ($cluster){
            return $item['endNode'] == $cluster['id'];
        })->first();
        $this->assertNotNull($clusterLink);
    }

    /** @test */
    function get_supernode_group_by_relation_and_class()
    {
        // given a party relates to 25 collection and 25 activity with the same relation
        $stack = $this->client->stack();
        $stack = $this->addNode($stack, 'party', 'P');
        for ($i = 0; $i < 25; $i++) {
            $stack = $this->addNode($stack, 'collection', "C$i");
            $stack = $this->addRelation($stack, "C$i", "hasAssociationWith", "P");
        }
        for ($i = 0; $i < 25; $i++) {
            $stack = $this->addNode($stack, 'activity', "A$i");
            $stack = $this->addRelation($stack, "A$i", "hasAssociationWith", "P");
        }
        $this->client->runStack($stack);

        // when obtaining graph data
        $graph = GraphRelationshipProvider::getByID("P");
        $nodes = $graph['nodes'];

        // it should display 2 cluster, 1 for each class
        $clusters = collect($nodes)->filter(function($item) {
            return in_array('cluster', $item['labels']);
        })->toArray();
        $this->assertCount(2, $clusters);
    }

    /** @test */
    function it_should_display_all_nodes_under_threshold_when_clustered()
    {
        // given a party relatesTo 25 collections, and relatesTo 1 party, and relatesTo 1 service
        $stack = $this->client->stack();
        $stack = $this->addNode($stack, 'party', 'P');
        for ($i = 0; $i < 25; $i++) {
            $stack = $this->addNode($stack, 'collection', "C$i");
            $stack = $this->addRelation($stack, "C$i", "relatesTo", "P");
        }
        $stack = $this->addNode($stack, 'party', 'P2');
        $stack = $this->addRelation($stack, 'P2', 'relatesTo', 'P');

        $stack = $this->addNode($stack, 'service', 'S');
        $stack = $this->addRelation($stack, 'P', 'relatesTo', 'S');

        $results = $this->client->runStack($stack);

        // when obtaining graph data
        $graph = GraphRelationshipProvider::getByID("P");
        $nodes = $graph['nodes'];
        $links = $graph['links'];

        // there should be 1 cluster
        $clusters = collect($nodes)->filter(function($item) {
            return in_array('cluster', $item['labels']);
        })->toArray();
        $this->assertCount(1, $clusters);

        // there should be a standalone P2
        $P2 = collect($nodes)->filter(function($item){
            return array_key_exists('roId', $item['properties']) && $item['properties']['roId'] == 'P2';
        })->first();
        $this->assertNotNull($P2);

        // and there should be a relationship between P and P2
        $p = $results->get('p')->firstRecord()->get('n');
        $p2 = $results->get('p2')->firstRecord()->get('n');
        $P2P2 = collect($links)->filter(function($item) use ($p, $p2){
            return $item['startNode'] == $p2->identity() && $item['endNode'] == $p->identity();
        })->first();
        $this->assertNotNull($P2P2);

        // there should be a standalone S
        $S = collect($nodes)->filter(function($item){
            return array_key_exists('roId', $item['properties']) && $item['properties']['roId'] == 'S';
        })->first();
        $this->assertNotNull($S);

        // and there should be a relationship between P and S
        $p = $results->get('p')->firstRecord()->get('n');
        $s = $results->get('s')->firstRecord()->get('n');
        $p2s = collect($links)->filter(function($item) use ($p, $s){
            return $item['startNode'] == $p->identity() && $item['endNode'] == $s->identity();
        })->first();
        $this->assertNotNull($p2s);
    }

    /** @test */
    function it_should_process_direct_relationship()
    {
        // given 2 records
        $a = $this->stub(RegistryObject::class, ['title' => 'A', 'key' => 'a']);
        $b = $this->stub(RegistryObject::class, ['title' => 'B', 'key' => 'b']);

        // they are related
        $this->stub(RegistryObject\Relationship::class, ['registry_object_id' => $a->id, 'related_object_key' => $b->key]);

        // when process
        GraphRelationshipProvider::process($a);

        // will have the a2b link
        $graph = GraphRelationshipProvider::getByID($a->id);
        $links = $graph['links'];
        $this->assertCount(1, $links);
    }

    /** @test */
    function it_should_process_relatedInfo_relationships()
    {
        $a = $this->stub(RegistryObject::class, ['title' => 'A', 'key' => 'a']);

    }

    /**
     * Helper method to mass add relations
     *
     * @param $stack
     * @param $payload
     * @return Stack
     */
    private function addRelations($stack, $payload)
    {
        foreach ($payload as $relation) {
            $stack = $this->addRelation($stack, $relation[0], $relation[1], $relation[2]);
        }
        return $stack;
    }

    /**
     * Helper method to mass add nodes
     *
     * @param $stack
     * @param $payload
     * @return Stack
     */
    private function addNodes($stack, $payload)
    {
        foreach ($payload as $label => $content) {
            foreach ($content as $id) {
                $stack = $this->addNode($stack, $label, $id);
            }
        }
        return $stack;
    }

    /**
     * Private helper function to quick add Node to a stack
     *
     * @param Stack $stack
     * @param $label
     * @param $id
     * @param $tag
     * @return Stack
     */
    private function addNode(Stack $stack, $label, $id, $tag = null)
    {
        $labels = ['test', $label];
        if ($tag === null) {
            $tag = strtolower($id);
        }
        $labels = implode(":", $labels);
        $stack->push("MERGE (n:{$labels} {roId: {roId} }) RETURN n", ['roId' => $id], $tag);
        return $stack;
    }

    /**
     * Private helper function to quick add Relation to a Stack
     *
     * @param Stack $stack
     * @param $from
     * @param $relation
     * @param $to
     * @return Stack
     */
    private function addRelation(Stack $stack, $from, $relation, $to)
    {
        $stack->push('MATCH (a:test {roId: "'.$from.'"}) MATCH (b:test {roId: "'.$to.'"}) MERGE (a)-[:'.$relation.']->(b)', [
            'from' => $from,
            'to' => $to,
            'relation' => $relation
        ]);
        return $stack;
    }

    public function setUp()
    {
        parent::setUp();
        try {
            $this->client = GraphRelationshipProvider::db();
            $this->client->getLabels();
        } catch (\Exception $e) {
            $this->markTestSkipped("Neo4j connection failed: ". $e->getMessage());
        }
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->clear();
    }

    private function clear()
    {
        $this->client->run("MATCH (n:test) OPTIONAL MATCH (n)-[r]-() DELETE n, r");
        if ($this->dataSource) {
            $this->client->run("MATCH (n {data_source_id:\"{$this->dataSource->id}\"}) OPTIONAL MATCH (n)-[r]-() DELETE n, r");
        }
    }
}
