<?php

use ANDS\DOI\MdsClient;
use ANDS\DOI\DOIServiceProvider;
use ANDS\DOI\Model\Client;
use ANDS\DOI\Model\Doi;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Repository\DoiRepository;
use ANDS\Util\Config;
use Dotenv\Dotenv;

class DoiRepositoryTest extends PHPUnit_Framework_TestCase
{

    private $testPrefix;
    /** @test */
    public function it_should_be_able_to_get_a_doi()
    {
        $repo = $this->getDoiRepository();

        $this->assertNotNull($repo->getFirst());
    }

    /** @test */
    public function it_should_be_able_to_get_a_doi_via_id()
    {
        // mint a DOI, make sure it exists in the database
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $result = $service->mint(
            "https://devl.ands.org.au/minh/", $this->getTestXML()
        );
        $this->assertTrue($result);

        // get the DOI
        $response = $service->getResponse();
        $doiID = $response['doi'];

        // check repository
        $repo = $this->getDoiRepository();
        $doi = $repo->getByID($doiID);

        $this->assertNotNull($doi);
        $this->assertSame($doi->doi_id, $doiID);
        $this->assertEquals($doi->publisher, "ANDS");
    }

    /** @test **/
    public function it_should_create_and_update_doi_correctly()
    {
        $repo = $this->getDoiRepository();

        $doiID = $this->testPrefix.'/TEST_DOI_'.uniqid();
        $doi = $repo->doiCreate([
            'doi_id' => $doiID,
            'publisher' => 'ANDS',
            'publication_year' => 2016,
            'status' => 'REQUESTED',
            'identifier_type' => 'DOI',
            'created_who' => 'SYSTEM',
            'url' => 'http://devl.ands.org.au/minh'
        ]);

        $this->assertTrue($doi);

        // update it
        $doi = $repo->getByID($doiID);
        $repo->doiUpdate($doi, ['url' => 'http://devl.ands.org.au/minh2']);

        // check that it's updated
        $doi = $repo->getByID($doiID);
        $this->assertEquals($doi->url, 'http://devl.ands.org.au/minh2');

        // delete the DOI
        $doi = $repo->getByID($doiID);
        $doi->delete();

        // assert it's gone
        $doi = $repo->getByID($doiID);
        $this->assertNull($doi);
    }

    /**
     * Helper method for getting the sample XML for testing purpose
     *
     * @return string
     */
    private function getTestXML()
    {
        return file_get_contents(__DIR__ . "/../assets/sample.xml");
    }

    /**
     * Helper method for getting the test DOI Client for fast authentication
     *
     * @return mixed
     */
    private function getTestClient()
    {

        $client = Client::where('app_id', getenv('TEST_CLIENT_APPID'))->first();
        $client->addClientPrefix($this->testPrefix);
        return $client;
    }


    /**
     * Helper method to return a new DoiRepository for each test
     *
     * @return DoiRepository
     */
    private function getDoiRepository() {
        $database = Config::get('database.dois');
        return $doiRepository = new DoiRepository(
            $database['hostname'], $database['database'], $database['username'],
            $database['password'], $database['port']
        );
    }

    /**
     * Helper method to create a DOIManager for every test
     *
     * @return DOIServiceProvider
     */
    private function getServiceProvider()
    {
        $database = Config::get('database.dois');

        $clientRepository = new ClientRepository(
            $database['hostname'], $database['database'], $database['username'],
            $database['password'], $database['port']
        );

        $doiRepository = new DoiRepository(
            $database['hostname'], $database['database'], $database['username'],
            $database['password'], $database['port']
        );

        $this->testPrefix = getenv("TEST_DOI_PREFIX");

        $dataciteClient = new MdsClient(
            getenv("DATACITE_USERNAME"),
            getenv("DATACITE_PASSWORD"),
            getenv("DATACITE_TEST_PASSWORD")
        );
        $dataciteClient->setDataciteUrl(getenv("DATACITE_TEST_URL"));

        $serviceProvider = new DOIServiceProvider(
            $clientRepository, $doiRepository, $dataciteClient
        );

        return $serviceProvider;
    }
}