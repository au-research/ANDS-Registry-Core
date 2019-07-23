<?php


use ANDS\Util\Config;
use Dotenv\Dotenv;


class FabricaClientTest extends PHPUnit_Framework_TestCase
{
    /** @var \ANDS\DOI\FabricaClient */
    private $fabricaClient;
    /** @var  \ANDS\DOI\Model\Client */
    private $trustedClient;
    /** @var  \ANDS\DOI\Repository\ClientRepository */
    private $repo;

    private $trustedClient_symbol;
    private $trustedClient_AppId;
    private $trustedClientName;
    private $trustedClientContactName;
    private $trustedClientContactEmail;

    /** @test */
    public function it_should_get_the_client()
    {
        $this->assertInstanceOf(\ANDS\DOI\FabricaClient::class, $this->fabricaClient);
        $this->assertInstanceOf(\ANDS\DOI\Repository\ClientRepository::class, $this->repo);
    }

    /** @test */
    public function it_should_get_all_UnAssigned_prefixes(){
        //This test is currently failing as there are no unassigned prefixes available on the test system  01/03/2019
        $unAssignedPrefixes = $this->fabricaClient->getUnAssignedPrefixes('test');
        $this->assertEquals(200, $this->fabricaClient->responseCode);
      // $this->assertGreaterThan(4, sizeof($unAssignedPrefixes['data']));

    }

    /** @test */
    public function it_should_get_more_from_unassigned_prefixes()
    {
        $this->markTestSkipped("this test is currently not working as there are no unassigned prefixes available on the test system");
        $cc = 1;
        // this test is currently not working as there are no unassigned prefixes available on the test system
        $unAssignedPrefixes = $this->fabricaClient->claimNumberOfUnassignedPrefixes($cc, 'test');
        // $this->assertEquals(201, $this->fabricaClient->responseCode);
        // $this->assertEquals($cc, sizeof($unAssignedPrefixes));
    }

    /** @test */
    public function it_should_get_all_Unalocated_prefixes(){
        $unAllocatedPrefixes = $this->fabricaClient->getUnalocatedPrefixes('test');
        $this->assertEquals(200, $this->fabricaClient->responseCode);
        $unAllocatedPrefixeArray = [];
        foreach($unAllocatedPrefixes['data'] as $data){
            $unAllocatedPrefixeArray[] = $data['relationships']['prefix']['data']['id'];
        }
        $this->assertGreaterThan(2, sizeof($unAllocatedPrefixeArray));
    }


    /** @test */
    public function it_should_get_all_provider_prefixes(){
        $providerPrefixes = $this->fabricaClient->getProviderPrefixes('test');
        $this->assertEquals(200, $this->fabricaClient->responseCode);
        $providerPrefixesArray = [];
        foreach($providerPrefixes['data'] as $data){
            $providerPrefixesArray[] = $data['relationships']['prefix']['data']['id'];
        }
        $this->assertGreaterThan(0, sizeof($providerPrefixes));
    }

    /** @test */
    public function it_should_sync_unallocated_prefixes()
    {
        $resultArray = $this->fabricaClient->syncUnallocatedPrefixes('test');
        $this->assertGreaterThan(2, sizeof($resultArray));
    }

    /** @test */
    public function it_should_sync_all_provider_prefixes()
    {
        $resultArray = $this->fabricaClient->syncProviderPrefixes('test');
        $this->assertGreaterThan(0, sizeof($resultArray));
    }


    
    /** @test */
    public function it_should_get_all_clients()
    {
        $clients = $this->fabricaClient->getClients('test');
        $this->assertEquals(200, $this->fabricaClient->responseCode);
        $this->assertGreaterThan(10, sizeof($clients['data']));
    }


    /** @test */
    public function it_should_compare_and_sync_prod_and_test_clients()
    {
        $this->markTestSkipped("This is not a test");
        $this->fabricaClient->syncProdTestClients();
    }
    
    /** @test */
    public function it_should_assign_a_non_assigned_prefix_to_a_client()
    {
        // TODO: This test is failing in app.test environment and should be looked at again
        // It was failing due to having unallocated prefixes in the Database that we no longer have at DataCite
        $this->markTestSkipped("Tests rely on a single prefix so shouldn't play with prefix assign unless we must do");
       // $this->markTestSkipped("Test is failing in DOI app.test. Behaviour is inconsistent. Dev needs another look");

        // when there are unallocated prefix
        $unAllocatedPrefix = $this->repo->getOneUnallocatedPrefix('prod');
        if (!$unAllocatedPrefix) {
            $this->markTestSkipped("There are no unallocated prefixes available");
        }
        $newPrefix = $unAllocatedPrefix->prefix_value;

        // we try to add the prefix and update it to fabrica
        $this->trustedClient->addClientPrefix($newPrefix,'prod');
        $this->fabricaClient->updateClientPrefixes($this->trustedClient, 'prod');

        $this->assertFalse($this->fabricaClient->hasError(), "update client prefixes failed. Reason: ". $this->fabricaClient->getErrorMessage());

        // when we obtain the fabricaInfo for that client
        $fabricaInfo = $this->fabricaClient->getClientPrefixesByDataciteSymbol($this->trustedClient->datacite_symbol, 'prod');
        $this->assertEquals(200, $this->fabricaClient->responseCode);

        // the prefix is now assigned to the client
        $this->assertContains($newPrefix, json_encode($fabricaInfo));
    }

    /** @test */
    public function it_should_claim_1_and_sync_unalocated_prefixes_in_db(){
        //this test is not working as there are no unassigned prefixes on test datacite for us to claim
        $this->markTestSkipped("Tests rely on a single prefix so shouldn't play with prefix assign unless we must do");
        $cc = 1;
        $oldUnalloc = $this->repo->getUnalocatedPrefixes('test');
        $unAssignedPrefixes = $this->fabricaClient->claimNumberOfUnassignedPrefixes($cc, 'test');
        // $this->assertEquals(201, $this->fabricaClient->responseCode);
        //$this->assertEquals($cc, sizeof($unAssignedPrefixes));
        $this->fabricaClient->syncUnallocatedPrefixes('test');
        $this->assertEquals(200, $this->fabricaClient->responseCode);
        //$newUnalloc = $this->repo->getUnalocatedPrefixes('test');
        //$this->assertGreaterThan(sizeof($oldUnalloc), sizeof($newUnalloc));
    }

    /** @test */
    public function it_should_get_prefix_info_from_dataite_for_a_client()
    {
        $fabricaInfo = $this->fabricaClient->getClientPrefixesByDataciteSymbol($this->trustedClient->datacite_symbol,'prod');
        $this->assertContains("prefixes", json_encode($fabricaInfo));
        $this->assertEquals(200, $this->fabricaClient->responseCode);

    }
    /** @test */
    public function it_should_get_prefix_info_from_test_dataite_for_a_client()
    {
        $fabricaInfo = $this->fabricaClient->getClientPrefixesByDataciteSymbol($this->trustedClient->datacite_symbol,'test');
        $this->assertContains("prefixes", json_encode($fabricaInfo));
        $this->assertEquals(200, $this->fabricaClient->responseCode);

    }
    /** @test  **/
    public function it_should_find_client_by_symbol_remote()
    {
        $trustedCient = $this->fabricaClient->getClientByDataCiteSymbol($this->trustedClient->datacite_symbol,'prod');
        $this->assertEquals(200, $this->fabricaClient->responseCode);
        $this->assertEquals("ands.centre-0", $trustedCient['data']['id']);
    }

    /** @test  **/
    public function it_should_find_test_client_by_symbol_remote()
    {
        $trustedCient = $this->fabricaClient->getClientByDataCiteSymbol($this->trustedClient->datacite_symbol,'test');
        $this->assertEquals(200, $this->fabricaClient->responseCode);
        $this->assertEquals("ands.centre-0", $trustedCient['data']['id']);
    }

    /** @test  **/
    public function it_should_add_a_new_client_to_datacite()
    {
        $this->markTestSkipped("Can't add a client that already exists in datacite");
        $this->fabricaClient->addClient($this->trustedClient, "test");
        $this->assertEquals(201, $this->fabricaClient->responseCode);
        $this->assertFalse($this->fabricaClient->hasError());
    }
// WE SHOULD'T DELETE CLIENTS ON DATACITE (IT WORKS THOUGH)
//    /** @test **/
//    public function it_should_delete_a_client_on_datacite(){
//        var_dump($this->trustedClient->datacite_symbol);
//        $this->fabricaClient->deleteClient($this->trustedClient);
//        $this->assertFalse($this->fabricaClient->hasError());
//    }

    /** @test  **/
    public function it_should_update_the_test_client_on_datacite()
    {
        $params = [
            'client_id' => $this->trustedClient->client_id,
            'client_name' => urldecode("UPDATED CLIENT NAME"),
            'client_contact_name' => urldecode("UPDATED CONTACT NAME"),
        ];

        $this->trustedClient = $this->repo->updateClient($params);

        $this->fabricaClient->updateClient($this->trustedClient,'test');
        $this->assertFalse($this->fabricaClient->hasError());
        $params = [
            'client_id' => $this->trustedClient->client_id,
            'client_name' => $this->trustedClientName,
            'client_contact_name' => $this->trustedClientContactName,
        ];

        $this->trustedClient = $this->repo->updateClient($params);

        $this->fabricaClient->updateClient($this->trustedClient,'test');
        $this->assertFalse($this->fabricaClient->hasError());
    }

    /** @test  **/
    public function it_should_create_clientInfo_from_local_client_object()
    {
        $clientInfo = $this->fabricaClient->getClientInfo($this->trustedClient, 'test');
        $this->assertContains($this->trustedClient->datacite_symbol, $clientInfo);
    }

    /** @test  **/
   public function it_should_get_list_of_client_dois()
    {
        $dois = $this->testFabricaClient->getDOIs('test');
        $this->assertTrue(count($dois) > 0);
    }


    /**
     * @return \ANDS\DOI\FabricaClient
     */
    private function getFabricaClient()
    {
        $username = getenv("DATACITE_FABRICA_USERNAME");
        $password = getenv("DATACITE_FABRICA_PASSWORD");
        $testPassword = getenv("DATACITE_FABRICA_TEST_PASSWORD");
        $this->fabricaClient = new \ANDS\DOI\FabricaClient($username, $password, $testPassword);

        $database = Config::get('database.dois');
        $clientRepository = new \ANDS\DOI\Repository\ClientRepository(
            $database['hostname'], $database['database'], $database['username'],
            $database['password'], $database['port']
        );

        $this->fabricaClient->setClientRepository($clientRepository);
        $this->fabricaClient->setDataciteUrl(getenv("DATACITE_FABRICA_API_URL"));
        $this->repo = $this->fabricaClient->getClientRepository();
    }

    

    private function getTestClient(){

        $this->trustedClientContactName = getenv("DATACITE_CONTACT_NAME");
        $this->trustedClientName = getenv("TEST_DC_CLIENT");
        $this->trustedClientContactEmail = getenv("DATACITE_CONTACT_EMAIL");
        $this->trustedClient_AppId = getenv("TEST_CLIENT_APPID");

        $this->trustedClient = $this->repo->getByAppID($this->trustedClient_AppId);


        if($this->trustedClient == null) {
            $params = [
                'ip_address' => "8.8.8.8",
                'app_id' => $this->trustedClient_AppId,
                'client_name' => urldecode($this->trustedClientName),
                'client_contact_name' => urldecode( $this->trustedClientContactName),
                'client_contact_email' => urldecode($this->trustedClientContactEmail),
                'shared_secret' => getenv("TEST_CLIENT_SHAREDSECRET")
            ];
            $this->trustedClient = $this->repo->create($params);
            $this->trustedClient->save();
        }
        $this->trustedClient_symbol = $this->trustedClient->datacite_symbol;

    }

    private function getTestFabricaClient()
    {
        $database = Config::get('database.dois');
        $username = $this->trustedClient_symbol;
        $password = getenv("DATACITE_FABRICA_PASSWORD");
        $testPassword = getenv("DATACITE_FABRICA_TEST_PASSWORD");
        $this->testFabricaClient = new \ANDS\DOI\FabricaClient($username, $password, $testPassword);

        $this->testFabricaClient->setClientRepository(new \ANDS\DOI\Repository\ClientRepository(
            $database['hostname'], $database['database'], $database['username'],
            $database['password'], $database['port']
        ));

        $this->testFabricaClient->setDataciteUrl(getenv("DATACITE_FABRICA_API_URL"));

    }

//    private function removeTestClient(){
//        $client = $this->repo->getByAppID($this->trustedClientName."APP_ID");
//        $this->repo->deleteClientById($client->client_id);
//    }
    
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->getFabricaClient();
        $this->getTestClient();
        $this->getTestFabricaClient();
    }

//    /**
//     *
//     */
//    public function tearDown()
//    {
//        parent::tearDown();
//        $this->removeTestClient();
//    }
}
