<?php

class DataCiteClientTest extends PHPUnit_Framework_TestCase
{

    /** @test */
    public function test_construct_object()
    {
        $client = $this->getClient();
        $this->assertEquals(
            $client->getDataciteUrl(), getenv('DATACITE_URL')
        );
    }

    /** @test */
    public function it_should_return_a_doi()
    {
        $client = $this->getClient();
        $get = $client->get("10.5072/00/56610ec83d432");
        $this->assertEquals($get, "https://devl.ands.org.au/minh/");

        $this->assertFalse($client->hasError());
    }

    /** @test */
    public function it_should_return_good_xml_for_a_doi()
    {
        $client = $this->getClient();

        $actual = new DOMDocument;
        $metadata = $client->getMetadata("10.5072/00/56610ec83d432");


        $actual->loadXML($metadata);

        $this->assertFalse($client->hasError());
        $this->assertEquals("resource", $actual->firstChild->tagName);
    }

    /** @test */
    public function test_mint_a_new_doi()
    {
        $client = $this->getClient();
        $xml = file_get_contents(__DIR__."/sample.xml");

        $response = $client->mint(
            "10.5072/00/56610ec83d432", "https://devl.ands.org.au/minh/", $xml
        );

        $this->assertTrue($response);
    }

    /** @test */
    public function it_should_set_datacite_url()
    {
        $client = $this->getClient();
        $client->setDataciteUrl('https://mds.datacite.org/');
        $this->assertEquals(
            $client->getDataciteUrl(), 'https://mds.datacite.org/'
        );
    }

    /** @test */
    public function it_should_update_a_doi_with_new_xml()
    {
        //run update with new xml
        //get the new xml and make sure it's the same
        //put old xml back?
    }

    /** @test */
    public function it_should_activate_a_doi_and_then_deactivate()
    {
        //make sure the DOI is activated
        //deactivate it
        //make sure it's deactivated
        //activate it
        //make sure it's activated in the status
    }

    /**
     * not a test
     * a helper method to return a new client instance
     */
    private function getClient()
    {
        $dotenv = new Dotenv\Dotenv(__DIR__.'/../');
        $dotenv->load();

        $username = getenv('DATACITE_USERNAME');
        $password = getenv('DATACITE_PASSWORD');

        $client = new ANDS\API\DOI\DataCiteClient($username, $password);
        $client->setDataciteUrl(getenv('DATACITE_URL'));

        return $client;
    }

}