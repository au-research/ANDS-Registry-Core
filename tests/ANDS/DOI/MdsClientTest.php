<?php

use ANDS\DOI\MdsClient;
use ANDS\DOI\Validator\XMLValidator;

class MdsClientTest extends PHPUnit_Framework_TestCase
{

    private $testDoiId;
    private $testPrefix;
    /** @var MdsClient */
    private $client;

    /** @test */
    public function test_mint_a_new_doi()
    {
        $xml = file_get_contents(__DIR__ . "/assets/sample.xml");
        $doi = $this->testPrefix.'/TEST_DOI_'.uniqid();
        $xml = XMLValidator::replaceDOIValue($doi, $xml);
        $response = $this->client->mint(
            $doi, "https://devl.ands.org.au/minh/", $xml
        );
        $this->assertTrue($response);
    }

    /** @test */
    public function test_construct_object()
    {
        $this->assertEquals(
            $this->client->getDataciteUrl(), getenv('DATACITE_TEST_URL')
        );
    }

    /** @test */
    public function it_should_return_good_xml_for_a_doi()
    {
        $actual = new DOMDocument;
        $metadata = $this->client->getMetadata($this->testDoiId);
        $actual->loadXML($metadata);
        $this->assertFalse($this->client->hasError());
        $this->assertEquals("resource", $actual->firstChild->tagName);
    }

    /** @test **/
    public function it_should_mint_a_schema_version_4_doi()
    {
        $xml = file_get_contents(__DIR__ . "/assets/datacite-example-full-v4.0.xml");
        $doi = $this->testPrefix.'/TEST_DOI_'.uniqid();
        $xml = XMLValidator::replaceDOIValue($doi, $xml);
        $response = $this->client->mint(
            $doi, "https://devl.ands.org.au/minh/", $xml
        );
        $this->assertTrue($response);
    }

    /** @test **/
    public function it_should_mint_a_schema_version_4_2_doi()
    {
        $xml = file_get_contents(__DIR__ . "/assets/datacite-example-full-v4.2.xml");
        $doi = $this->testPrefix.'/TEST_DOI_'.uniqid();
        $xml = XMLValidator::replaceDOIValue($doi, $xml);
        $response = $this->client->mint(
            $doi, "https://devl.ands.org.au/minh/", $xml
        );
        $this->assertTrue($response);
    }

    /** @test */
    public function it_should_set_datacite_url()
    {
        $this->client->setDataciteUrl('https://mds.test.datacite.org/');
        $this->assertEquals(
            $this->client->getDataciteUrl(), 'https://mds.test.datacite.org/'
        );
    }

    /** @test */
    public function it_should_update_a_doi_with_new_xml()
    {
        // given a doi, updates it's xml
        $replace = file_get_contents(__DIR__ . "/assets/replace_sample.xml");

        $replace = XMLValidator::replaceDOIValue($this->testDoiId, $replace);
        $response = $this->client->update($replace);

        // it should have the new xml
        $this->assertTrue($response);
        $this->assertXmlStringEqualsXmlString(
            strtolower($this->client->getMetadata($this->testDoiId)),
            strtolower($replace)
        );
    }

    /** @test */
    public function it_should_activate_a_doi_and_then_deactivate()
    {
        // given an activated doi
        $xml = $this->client->getMetadata($this->testDoiId);
        $this->client->activate($xml);

        // deactivation successful
        $this->assertTrue($this->client->deActivate($this->testDoiId));

        // activation successful
        $this->assertTrue($this->client->activate($xml));
    }

    /**
     * Not a test
     * A helper method to setup a new client instance
     */
    private function setUpClient()
    {
        $username = getenv('DATACITE_USERNAME');
        $password = getenv('DATACITE_PASSWORD');
        $testPassword = getenv('DATACITE_TEST_PASSWORD');
        $this->client = new MdsClient($username, $password, $testPassword);
        $this->client->setDataciteUrl(getenv('DATACITE_TEST_URL'));
    }

    public function setUpTestDOI(){

        // set up the metadata
        if(!$this->isTestDOIExists()){
            $this->createTestDOI();
        }

        if (!$this->isTestDOIExists()) {
            $this->markTestSkipped("DOI Creation Failed. Check DataCite");
        }

        // setup the url (make sure)
        $this->client->clearResponse();
        $this->client->updateURL($this->testDoiId, "https://devl.ands.org.au/leo/");
    }

    public function isTestDOIExists(){
        $this->client->getMetadata($this->testDoiId);
        return !$this->client->hasError();
    }

    public function createTestDOI(){
        $xml = file_get_contents(__DIR__ . "/assets/sample.xml");
        $this->testDoiId = $this->testPrefix.'/TEST_DOI_'.uniqid();
        $xml = XMLValidator::replaceDOIValue($this->testDoiId, $xml);
        $this->client->mint(
            $this->testDoiId, "https://devl.ands.org.au/minh/", $xml
        );
    }
    
    public function setUp(){
        parent::setUp();
        $this->testPrefix = getenv("TEST_DOI_PREFIX");
        $this->setUpClient();
        $this->setUpTestDOI();
    }
}