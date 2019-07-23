<?php

use ANDS\DOI\DOIServiceProvider;
use ANDS\DOI\Formatter\XMLFormatter;
use ANDS\DOI\Model\Client;
use ANDS\DOI\Model\Doi;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\DOI\Repository\DoiRepository;
use ANDS\DOI\Validator\XMLValidator;
use Dotenv\Dotenv;
use ANDS\DOI\MdsClient;

class DOIServiceProviderTest extends PHPUnit_Framework_TestCase
{
    private $testClient;
    private $testPrefix;

    /** @test */
    public function it_should_be_able_to_create_a_service_provider()
    {
        $sp = $this->getServiceProvider();
        $this->assertNotNull($sp);
    }

    /** @test */
    public function it_should_authenticate_a_real_user()
    {
        $sp = $this->getServiceProvider();
        $authenticate = $sp->authenticate(
            getenv("TEST_CLIENT_APPID"), getenv("TEST_CLIENT_SHAREDSECRET")
        );
        $this->assertTrue($authenticate);
        $this->assertNotNull($sp->getAuthenticatedClient());
        $this->assertTrue($sp->isClientAuthenticated());
    }

    /** @test * */
    public function it_should_not_authenticate_a_fake_user()
    {
        $sp = $this->getServiceProvider();
        $authenticate = $sp->authenticate("asdf");
        $this->assertFalse($authenticate);
        $this->assertNull($sp->getAuthenticatedClient());
        $this->assertFalse($sp->isClientAuthenticated());
    }

    /** @test * */
    public function it_should_authenticate_a_test_user()
    {
        $sp = $this->getServiceProvider();
        $authenticate = $sp->authenticate(
            "TEST".getenv("TEST_CLIENT_APPID"), getenv("TEST_CLIENT_SHAREDSECRET")
        );

        $client = $sp->getAuthenticatedClient();
        $this->assertTrue($authenticate);
        $this->assertNotNull($sp->getAuthenticatedClient());
        $this->assertTrue($sp->isClientAuthenticated());
        $this->assertEquals("test", $client['mode']);
    }

    /** @test **/
    public function it_should_disallow_minting_if_client_is_not_authenticated()
    {
        $service = $this->getServiceProvider();
        $result = $service->mint(
            "http://devl.ands.org.au/minh/", $this->getTestXML(),false
        );
        $this->assertFalse($result);
    }

    /** @test * */
    public function it_should_allow_a_client_to_mint()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $doi = $this->testPrefix.'/TEST_DOI_'.uniqid();

        $xml = XMLValidator::replaceDOIValue($doi, $this->getTestXML());
        $this->assertTrue($service->isClientAuthenticated());
        // dd($xml);
        $result = $service->mint(
            "http://devl.ands.org.au/minh/", $xml,false
        );

        $this->assertTrue($result);
    }

    /** @test * */
    public function it_should_allow_minting_a_new_doi_and_return_good_message()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $doi= $this->testPrefix.'/TEST_DOI_'.uniqid();

        $xml = XMLValidator::replaceDOIValue($doi, $this->getTestXML());  
        $result = $service->mint(
            "https://devl.ands.org.au/minh/", $xml,false
        );

        $this->assertTrue($result);

        $response = $service->getResponse();

        $this->assertEquals("MT001", $response['responsecode']);

        // test formater as well
        $formatter = new XMLFormatter();
        $message = $formatter->format($response);

        $sxml = new SimpleXMLElement($message);
        $this->assertEquals("MT001", (string)$sxml->responsecode);
        $this->assertEquals($response['doi'], (string)$sxml->doi);
    }

    /** @test * */
    public function it_should_disallow_minting_if_not_authorized()
    {
        $service = $this->getServiceProvider();
        $result = $service->authenticate(uniqid());
        $this->assertFalse($result);

        $response = $service->getResponse();
        $formatter = new XMLFormatter();
        $message = $formatter->format($response);
        $sxml = new SimpleXMLElement($message);

        $this->assertEquals("MT009", (string)$sxml->responsecode);
        $this->assertEquals("failure", (string)$sxml->attributes()->type);
    }


    /** @test **/
    public function it_should_not_allow_minting_a_new_doi_and_return_error_message()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $doi= $this->testPrefix.'/TEST_DOI_'.uniqid();
        $xml = XMLValidator::replaceDOIValue($doi, $this->getInvalidTestXML());
        $result = $service->mint(
            "https://devl.ands.org.au/minh/", $xml,false
        );

        $this->assertFalse($result);

        $response = $service->getResponse();

        $this->assertEquals("MT006", $response['responsecode']);
    }

    /** @test * */
    public function it_should_disallow_minting_of_url_not_in_top_domain()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $doi= $this->testPrefix.'/TEST_DOI_'.uniqid();

        $xml = XMLValidator::replaceDOIValue($doi, $this->getTestXML());
        $result = $service->mint(
            "https://google.com/", $xml,false
        );
        $this->assertFalse($result);

        $response = $service->getResponse();
        $formatter = new XMLFormatter();
        $message = $formatter->format($response);
        $sxml = new SimpleXMLElement($message);

        $this->assertEquals("MT014", (string)$sxml->responsecode);
        $this->assertEquals("failure", (string)$sxml->attributes()->type);


    }

    /** @test * */
    public function it_should_not_activate_an_active_doi()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $doi= $this->testPrefix.'/TEST_DOI_'.uniqid();
        $xml = XMLValidator::replaceDOIValue($doi, $this->getTestXML());          //mint a DOI and make sure it's activated
        $service->mint(
            "https://devl.ands.org.au/minh/", $xml,false
        );

        $response = $service->getResponse();
        $doi = $response['doi'];

        // activate the already activated DOI
        $this->assertFalse($service->activate($doi));
    }

    /** @test * */
    public function it_should_activate_an_inactive_doi()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $doi= $this->testPrefix.'/TEST_DOI_'.uniqid();
        $xml = XMLValidator::replaceDOIValue($doi, $this->getTestXML());          //mint a DOI and make sure it's activated
        $result = $service->mint(
            "https://devl.ands.org.au/minh/", $xml,false
        );
        $this->assertTrue($result);

        $response = $service->getResponse();

        $doi = $response['doi'];
        $service->activate($doi);
        // deactivate it
        $result = $service->deactivate($doi);
        $this->assertTrue($result);
        // this DOI should now be activated
        $this->assertTrue($service->activate($doi));

    }

    /** @test * */
    public function it_should_deactivate_an_active_doi()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());

        //mint a DOI and make sure it's activated
        $doi= $this->testPrefix.'/TEST_DOI_'.uniqid();
        $xml = XMLValidator::replaceDOIValue($doi, $this->getTestXML());
        $result = $service->mint(
            "https://devl.ands.org.au/minh/", $xml,false
        );
        $this->assertTrue($result);

        $response = $service->getResponse();

        $doi = $response['doi'];

        $this->assertTrue($service->deactivate($doi));

    }

    /** @test * */
    public function it_should_return_status_of_a_doi()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());

        $doi= $this->testPrefix.'/TEST_DOI_'.uniqid();
        $xml = XMLValidator::replaceDOIValue($doi, $this->getTestXML());

        //mint a DOI and make sure it's activated
        $result = $service->mint(
            "https://devl.ands.org.au/minh/", $xml,true
        );

        $this->assertTrue($result);

        $response = $service->getResponse();

        // status is true
        $this->assertTrue($service->getStatus($doi));


        $this->assertEquals("MT019", $service->getResponse()['responsecode']);

        // should be active
        $this->assertEquals("ACTIVE", $service->getResponse()['verbosemessage']);

        // deactivate it
        $result = $service->deactivate($doi);



        // status should still be true
        $this->assertTrue($service->getStatus($doi));
        
        $this->assertEquals("MT019", $service->getResponse()['responsecode']);

        // the response contains INACTIVE
        $this->assertEquals("INACTIVE", $service->getResponse()['verbosemessage']);

    }



    /** @test * */
    public function it_should_allow_current_client_doi_access()
    {
        $service = $this->getServiceProvider();
        $client = $this->getTestClient();
        $service->setAuthenticatedClient($client);

        $doi= $this->testPrefix.'/TEST_DOI_'.uniqid();

        $this->assertTrue($service->isDoiAuthenticatedClients($doi, $client->client_id));

    }

    /** @test * */
    public function it_should_not_allow_current_client_doi_access()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $this->assertFalse($service->isDoiAuthenticatedClients("10.656565/343333333"));

    }

    /** @test * */
    public function it_should_add_doi_before_validate()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());


        //mint a DOI and make sure it's activated
        $result = $service->mint(
            "https://devl.ands.org.au/minh/", file_get_contents(__DIR__ . "/assets/sample_without_doi.xml"),false
        );


        $this->assertTrue($result);

    }


    /** @test * */
    public function it_should_add_identifier_before_validate()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());


        //mint a DOI and make sure it's activated
        $result = $service->mint(
            "https://devl.ands.org.au/minh/", file_get_contents(__DIR__ . "/assets/sample_without_identifier.xml"),false
        );


        $this->assertTrue($result);

    }

    /** @test * */
    public function it_should_add_identifier_before_validate_before_update()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());

        $xml = file_get_contents(__DIR__ . "/assets/sample_without_identifier_update.xml");
        $doi= $this->testPrefix.'/TEST_DOI_'.uniqid();
        //update a DOI and make sure that the xml provided changes to correct DOI
        $result = $service->update($doi, null, $xml);

        $this->assertTrue($result);

    }

    /** @test * */
    public function it_should_not_add_client_id_to_new_doiValues()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());
        $new_doi = $service->getNewDOI();
        $client_id_str = str_pad($this->getTestClient()->client_id, 2,0,STR_PAD_LEFT)."/";
        $this->assertNotContains($client_id_str, $new_doi);

    }

    /** @test * */
    public function it_should_add_or_change_doi_before_validate_before_update()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());

        $xml = file_get_contents(__DIR__ . "/assets/sample_wrong_doi.xml");
        $doi= $this->testPrefix.'/TEST_DOI_'.uniqid();
        //update a DOI and make sure that the xml provided changes to correct DOI
        $result = $service->update($doi, null, $xml);

        $this->assertTrue($result);

    }

    /** @test **/
    public function it_should_add_doi_and_work_with_utf8()
    {
        $service = $this->getServiceProvider();
        $service->setAuthenticatedClient($this->getTestClient());

        //mint a DOI and make sure it's activated
        $result = $service->mint(
            "https://devl.ands.org.au/minh/", file_get_contents(__DIR__ . "/assets/sample_utf8.xml"),false
        );
        $this->assertTrue($result);
    }

    /**
     * Helper method for getting the sample XML for testing purpose
     *
     * @return string
     */
    private function getTestXML()
    {
        return file_get_contents(__DIR__ . "/assets/sample.xml");
    }

    /**
     * Helper method for getting the sample XML for testing purpose
     *
     * @return string
     */
    private function getTestXMLWrongDoi()
    {
        return file_get_contents(__DIR__ . "/assets/sample_wrong_doi.xml");
    }



    /**
     * Helper method for getting the sample XML for testing purpose
     *
     * @return string
     */
    private function getInvalidTestXML()
    {
        return file_get_contents(__DIR__ . "/assets/sample_invalid.xml");
    }



    /**
     * Helper method for getting the test DOI Client for fast authentication
     *
     * @return mixed
     */
    private function getTestClient()
    {
        if ($this->testClient == null) {
            $this->testClient = Client::where('app_id', getenv('TEST_CLIENT_APPID'))->first();
            $this->testClient->addClientPrefix(getenv("TEST_DOI_PREFIX"), 'test');
            $this->testClient->mode = 'test';
        }
        return $this->testClient ;
    }

    /**
     * Helper method to create a DOIManager for every test
     *
     * @return DOIServiceProvider
     */
    private function getServiceProvider()
    {
        $this->testPrefix = getenv("TEST_DOI_PREFIX");

        $clientRepository = new ClientRepository(
            getenv("DATABASE_URL"),
            getenv("DATABASE"),
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );

        $doiRepository = new DoiRepository(
            getenv("DATABASE_URL"),
            getenv("DATABASE"),
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );

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