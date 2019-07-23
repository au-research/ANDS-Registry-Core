<?php

use ANDS\DOI\Model\Client;
use ANDS\DOI\Model\Prefix;
use ANDS\DOI\Repository\ClientRepository;
use ANDS\Util\Config;
use Dotenv\Dotenv;

class ClientRepositoryTest extends PHPUnit_Framework_TestCase
{

    private $repo;
    private $testClient;

    /** @test */
    public function it_should_be_able_to_get_a_client_via_id()
    {
        $client = $this->repo->getByID($this->testClient->client_id);
        $this->assertNotNull($client);
        $this->assertEquals($client->client_name, "PHPUNIT_TEST");
    }

    /** @test **/
    public function it_should_be_able_to_get_a_client_via_appid() {
        $client = $this->repo->getByAppID("PHPUNIT_TEST_APP_ID");
        $this->assertNotNull($client);
        $this->assertEquals( "PHPUNIT_TEST", $client->client_name);
    }

    /** @test */
    public function it_should_authenticate_the_right_user_if_has_shared_secret()
    {
        $authenticate = $this->repo->authenticate("PHPUNIT_TEST_APP_ID","PHPUNIT_TEST_SHARED_SECRET");
        $this->assertNotFalse($authenticate);
    }

    /** @test */
    public function it_does_not_authenticate_if_wrong_shared_secret()
    {
        $authenticate = $this->repo->authenticate("PHPUNIT_TEST_APP_ID", "randompasswordthatdoesnotmatch");
        $this->assertFalse($authenticate);
    }

    /** @test */
    public function it_authenticates_user_if_ip_match_and_no_shared_secret_provided()
    {
        $client = $this->repo->authenticate("PHPUNIT_TEST_APP_ID", null, "8.8.8.8");
        $this->assertInstanceOf(Client::class, $client);
    }

    /** @test */
    public function it_does_not_authenticates_user_if_ip_match_fail()
    {
        $authenticate = $this->repo->authenticate("PHPUNIT_TEST_APP_ID", null, "9.9.9.9");
        $this->assertFalse($authenticate);
    }

    /** @test **/
    public function it_should_authenticate_user_if_sharedsecret_match_and_ip_mismatch()
    {
        $client = $this->repo->authenticate("PHPUNIT_TEST_APP_ID","PHPUNIT_TEST_SHARED_SECRET", "9.9.9.9");
        $this->assertInstanceOf(Client::class, $client);
        $this->assertTrue(true);
    }

    /** @test **/
    public function it_should_generate_new_client_with_datacite_symbol()
    {
        $client = $this->repo->create([
            "client_name" => "test client"
        ]);
        $this->assertNotNull($client->datacite_symbol);
        $client->delete();
    }

    /** @test **/
    public function it_should_generate_datacite_symbol_for_test_client()
    {
        $this->assertNotNull($this->testClient);

        $this->testClient->datacite_symbol = "";
        $this->testClient->save();

        $client = $this->repo->getByID($this->testClient->client_id);
        $this->assertEquals("", $client->datacite_symbol);

        $this->repo->generateDataciteSymbol($client);
        $this->assertNotNull($client->datacite_symbol);
    }

    /** @test **/
    public function it_should_create_a_new_test_client()
    {
        $this->createTestClient();
        $this->assertNotNull($this->testClient->datacite_symbol);
    }

    /** @test **/
    public function it_should_add_a_prefix_to_test_client()
    {


        $unalloc = $this->repo->getUnalocatedPrefixes();
        if(sizeof($unalloc) < 1)
        {
            $this->markTestSkipped("No unallocated prefixes to test with");
        }
        $testPrefix = $unalloc[0]->prefix_value;

        $this->assertFalse($this->testClient->hasPrefix($testPrefix));

        $this->testClient->addClientPrefix($testPrefix, true);

        $this->assertTrue($this->testClient->hasPrefix($testPrefix));

        $this->testClient->removeClientPrefix($testPrefix);

        $this->assertFalse($this->testClient->hasPrefix($testPrefix));
    }

    /** @test **/
    public function it_should_add_a_domain_to_test_client()
    {

        $this->testClient->removeClientDomains();
        $this->testClient->addDomains("fish.com, apple.tree, ands.org");
        $this->testClient->addDomain("coinbit.io");
        $this->testClient->addDomain("ands.org.au");
        $this->testClient->addDomain("catfish.com");
        $first = false;
        $domains_str = "";
        foreach ($this->testClient->domains as $domain) {
            if(!$first)
                $domains_str .= ",";
            $domains_str .= $domain->client_domain;
            $first = false;
        }

        $this->assertContains("fish.com", $domains_str);
        $this->assertContains("ands.org.au", $domains_str);
        $this->assertContains("catfish.com", $domains_str);
        $this->assertContains("coinbit.io", $domains_str);
    }

    /** @test  **/
    public function it_should_update_a_client(){
        $params = [
            'client_id' => urldecode($this->testClient->client_id),
            'client_contact_name' => urldecode("UPDATED"),
            'client_name' => urldecode("UPDATED"),
            'client_contact_email' => urldecode("UPDATED@UPDATED"),
        ];

        $this->repo->updateClient($params);

        $this->testClient = $this->repo->getByAppID("PHPUNIT_TEST_APP_ID");

        $this->assertEquals("UPDATED", $this->testClient->client_name);
        $this->assertEquals("UPDATED", $this->testClient->client_contact_name);
        $this->assertEquals("UPDATED@UPDATED", $this->testClient->client_contact_email);

        $params = [
            'client_id' => urldecode($this->testClient->client_id),
            'client_contact_name' => urldecode("PHPUNIT_TEST"),
            'client_name' => urldecode("PHPUNIT_TEST"),
            'client_contact_email' => urldecode("PHPUNIT_TEST@PHPUNIT_TEST"),
        ];

        $this->repo->updateClient($params);

        $this->testClient = $this->repo->getByAppID("PHPUNIT_TEST_APP_ID");

        $this->assertEquals("PHPUNIT_TEST", $this->testClient->client_name);
        $this->assertEquals("PHPUNIT_TEST", $this->testClient->client_contact_name);
        $this->assertEquals("PHPUNIT_TEST@PHPUNIT_TEST", $this->testClient->client_contact_email);

    }

    /** @test  **/
    public function should_return_unallocated_prefixes()
    {
        $unalloc = $this->repo->getUnalocatedPrefixes();
        $this->assertNotEmpty($unalloc);
    }

    /**
     * Helper method to return a new ClientRepository for each test
     *
     * @return ClientRepository
     */
    private function getClientRepository() {
        $database = Config::get('database.dois');
        $this->repo = new ClientRepository(
            $database['hostname'], $database['database'], $database['username'],
            $database['password'], $database['port']
        );
        return $this->repo;
    }

    private function createTestClient(){

        $this->testClient = $this->repo->getByAppID("PHPUNIT_TEST_APP_ID");
        if($this->testClient == null) {
            $params = [
                'ip_address' => "8.8.8.8",
                'app_id' => "PHPUNIT_TEST_APP_ID",
                'client_name' => urldecode("PHPUNIT_TEST"),
                'client_contact_name' => urldecode("PHPUNIT_TEST"),
                'client_contact_email' => urldecode("PHPUNIT_TEST@PHPUNIT_TEST"),
                'shared_secret' => "PHPUNIT_TEST_SHARED_SECRET"
            ];
            $this->testClient = $this->repo->create($params);
        }
    }


    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->getClientRepository();
        $this->createTestClient();
    }
}