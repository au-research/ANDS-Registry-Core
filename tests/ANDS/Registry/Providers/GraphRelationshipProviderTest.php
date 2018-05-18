<?php

namespace ANDS\Registry\Providers;

use ANDS\RegistryObject;
use GraphAware\Neo4j\Client\ClientInterface;

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
        $stack->push('MERGE (n:test {roId: {roId} }) RETURN n', ['roId' => 'A'], 'a');
        $stack->push('MERGE (n:test {roId: {roId} }) RETURN n', ['roId' => 'B'], 'b');
        $stack->push('MERGE (n:test {roId: {roId} }) RETURN n', ['roId' => 'C'], 'c');
        $stack->push('MATCH (a:test {roId: "A"}) MATCH (b:test {roId: "B"}) MERGE (a)-[:isPartOf]->(b)');
        $stack->push('MATCH (a:test {roId: "A"}) MATCH (c:test {roId: "C"}) MERGE (a)<-[:hasAssociationWith]-(c)');
        $stack->push('MATCH (b:test {roId: "B"}) MATCH (c:test {roId: "C"}) MERGE (b)-[:hasPart]->(c)');
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
        $stack->push('MERGE (n:test {roId: {roId} }) RETURN n', ['roId' => 'A'], 'a');
        $stack->push('MERGE (n:test {roId: {roId} }) RETURN n', ['roId' => 'B'], 'b');
        $stack->push('MERGE (n:test {roId: {roId} }) RETURN n', ['roId' => 'C'], 'c');
        $stack->push('MERGE (n:test {roId: {roId} }) RETURN n', ['roId' => 'A2'], 'a2');
        $stack->push('MATCH (a:test {roId: "A"}) MATCH (b:test {roId: "B"}) MERGE (a)-[:rel]->(b)');
        $stack->push('MATCH (a:test {roId: "A"}) MATCH (a2:test {roId: "A2"}) MERGE (a)-[:identicalTo]->(a2)');
        $stack->push('MATCH (a2:test {roId: "A2"}) MATCH (c:test {roId: "C"}) MERGE (a2)-[:hasPart]->(c)');
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
    }
}
