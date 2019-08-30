<?php

use ANDS\File\Storage;
use Guzzle\Http\Client;

class SolrDependencyTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    function solr_url_is_defined()
    {
        $url = \ANDS\Util\Config::get('app.solr_url');
        $this->assertNotEmpty($url);
    }

    /** @test */
    function solr_is_reachable_with_guzzle()
    {
        // http://localhost:8983/solr/admin/cores?action=status&wt=json
        $response = $this->getGuzzleClient()->get("admin/cores?action=status&wt=json")->send();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->json());
    }

    /** @test */
    function solr_is_reachable_with_curl()
    {
        // http://localhost:8983/solr/admin/cores?action=status&wt=json
        $url = \ANDS\Util\Config::get('app.solr_url');
        $url .= "admin/cores?action=status&wt=json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $HTTPCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->assertNotEmpty($content);
        $this->assertEquals(200, $HTTPCode);
    }

    /** @test */
    function index_has_all_the_relevant_collections()
    {
        $client = new Client(\ANDS\Util\Config::get('app.solr_url'));
        $response = $client->get('admin/collections?action=LIST&wt=json')->send();
        $collections = $response->json()['collections'];

        $this->assertContains('portal', $collections);
        $this->assertContains('concepts', $collections);
        $this->assertContains('relations', $collections);
    }

    /** @test */
    function it_can_index_basic_portal_document_correctly()
    {
        // given a client
        $client = $this->getGuzzleClient();

        // given a random document
        $id = uniqid();
        $document = [
            'id' => $id,
            'slug' => "this-is-a-test-slug-1234",
            'type' => "test"
        ];

        // when it index a document
        $add = $client->post('portal/update/?wt=json', [
            'Content-Type' => 'application/json'
        ], json_encode(['add' => ["doc" => $document]]))->send();
        $this->assertEquals(200, $add->getStatusCode());
        $this->assertEquals("0", $add->json()['responseHeader']['status']);

        // and commit
        $commit = $client->post('portal/update/?wt=json&commit=true', [
            'Content-Type' => 'application/json'
        ])->send();
        $this->assertEquals(200, $commit->getStatusCode());

        // it can now find the document
        $get = $client->get("portal/select?wt=json&q=id:$id")->send()->json();
        $this->assertEquals(1, $get['response']['numFound']);
        $this->assertEquals($id, $get['response']['docs'][0]['id']);
    }

    /** @test */
    function solr_can_index_and_search_portal_document_with_spatial_data_correctly()
    {
        $client = $this->getGuzzleClient();

        // a document with spatial_coverage_extents_wkt
        $document = [
            "id" => uniqid(),
            "type" => "test",
            "spatial_coverage_extents" => "112 -44 154 -10 ",
            "spatial_coverage_polygons" => "154,-10 154,-44 112,-44 112,-10 154,-10",
            "spatial_coverage_centres" => "133,-27",
            "spatial_coverage_extents_wkt" => "POLYGON((154 -10, 154 -44, 112 -44, 112 -10, 154 -10))",
            "spatial_coverage_area_sum" => 1428.0,
        ];

        // when adding that document correctly
        $add = $client->post('portal/update/?wt=json&commit=true', [
            'Content-Type' => 'application/json'
        ], json_encode(['add' => ["doc" => $document]]))->send();
        $this->assertEquals(200, $add->getStatusCode());
        $this->assertEquals("0", $add->json()['responseHeader']['status']);

        // it can now find the document with the IsWithin param
        $get = $client->get("portal/select?wt=json&q=+spatial_coverage_extents_wkt:\"IsWithin(POLYGON((154 -10, 154 -44, 112 -44, 112 -10, 154 -10)))\"")->send()->json();
        $this->assertGreaterThan(0, $get['response']['numFound']);

        // given a different polygon it will not be able to find
        $get = $client->get("portal/select?wt=json&q=+spatial_coverage_extents_wkt:\"IsWithin(POLYGON((1 1, 1 -44, 2 -44, 3 -10, 1 1)))\"")->send()->json();
        $this->assertEquals(0, $get['response']['numFound']);
    }

    /** @test */
    function it_can_index_a_complicated_portal_document_without_errors()
    {
        $client = $this->getGuzzleClient();

        $document = json_decode(Storage::disk('test')->get('solr/full.json'), true);
        $add = $client->post('portal/update/?wt=json&commit=true', [
            'Content-Type' => 'application/json'
        ], json_encode(['add' => ["doc" => $document]]))->send();
        $this->assertEquals(200, $add->getStatusCode());
        $this->assertEquals("0", $add->json()['responseHeader']['status']);
    }

    /** @test */
    function it_can_index_a_basic_concepts_document()
    {
        $client = $this->getGuzzleClient();

        $document = [
            "id" => uniqid(),
            "type" => "test",
            "iri" => uniqid()
        ];
        $add = $client->post('concepts/update/?wt=json&commit=true', [
            'Content-Type' => 'application/json'
        ], json_encode(['add' => ["doc" => $document]]))->send();
        $this->assertEquals(200, $add->getStatusCode());
        $this->assertEquals("0", $add->json()['responseHeader']['status']);
    }

    // TODO test copyfields
    // TODO test temporal fields
    // TODO test relations collection
    // TODO test cross-core search query

    /**
     * Helper method to get the current GuzzleClient for the current SOLR url
     *
     * @return Client
     */
    private function getGuzzleClient()
    {
        return new Client(\ANDS\Util\Config::get('app.solr_url'), [
            'request.options' => [
                'exceptions' => false,
            ]
        ]);
    }

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        restore_error_handler();
    }

    protected function tearDown()
    {
        parent::tearDown();

        // delete all document that matches type:test
        $client = new Client(\ANDS\Util\Config::get('app.solr_url'));
        $client->post('portal/update?commit=true', [
            'Content-Type' => 'application/json'
        ], json_encode([
            "delete" => ["query" => "type:test"]
        ], true))->send();

        $client->post('concepts/update?commit=true', [
            'Content-Type' => 'application/json'
        ], json_encode([
            "delete" => ["query" => "type:test"]
        ], true))->send();
    }
}
