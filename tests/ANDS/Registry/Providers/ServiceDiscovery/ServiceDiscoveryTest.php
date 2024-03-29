<?php


use ANDS\Registry\Providers\ServiceDiscovery\ServiceDiscovery;
use ANDS\Registry\Providers\ServiceDiscovery\ServiceProducer;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Links;
use ANDS\Repository\RegistryObjectsRepository;

class ServiceDiscoveryTest extends \RegistryTestClass
{
    public function setUp()
    {
        parent::setUp();
        ini_set('memory_limit', '1024M');
    }

    /** @test **/
    public function test_get_links_for_datasource() {

        $this->markTestSkipped("Big integration tests, should only be ran during development");

        \ANDS\Cache\Cache::file()->forget('testLinksAODN');
        $links = \ANDS\Cache\Cache::file()->rememberForever('testLinksAODN', function() {
            $links = ServiceDiscovery::getServiceLinksForDatasource(10);
            $links = ServiceDiscovery::processLinks($links);
            $links = ServiceDiscovery::formatLinks($links);
            return $links;
        });
        $this->assertTrue(\ANDS\Cache\Cache::file()->has('testLinksAODN'));
        $links = \ANDS\Cache\Cache::file()->get('testLinksAODN');

        $service_discovery_service_url = \ANDS\Util\config::get('app.services_registry_url');
        $serviceProduce = new ServiceProducer($service_discovery_service_url);
        $serviceProduce->processServices(json_encode($links));
        $serviceCount = $serviceProduce->getServiceCount();

        $this->assertNotEmpty($links);
    }


    /** @test **/
    public function test_get_links_for_datasource_61() {
        $this->markTestSkipped("Big integration tests, should only be ran during development");


        $links = ServiceDiscovery::getServiceLinksForDatasource(61);
        $links = ServiceDiscovery::processLinks($links);
        $links = ServiceDiscovery::formatLinks($links);

       // $service_discovery_service_url = get_config_item('SERVICES_DISCOVERY_SERVICE_URL');
      //  $serviceProduce = new ServiceProducer($service_discovery_service_url);
      //  $serviceProduce->processServices(json_encode($links));
      //  $serviceCount = $serviceProduce->getServiceCount();

        $this->assertNotEmpty($links);
    }


    /** @test */
    function it_gets_base_url()
    {
        $url = "http://www.cmar.csiro.au/geoserver/wms?&CQL_FILTER=SURVEY_NAME%20%3D%20%27ALBA196909%27";
        $baseUrl = ServiceDiscovery::getBaseUrl($url);
        $this->assertEquals($baseUrl, "https://www.cmar.csiro.au/geoserver/wms");
    }

//    /** @test **/
//    public function test_get_links_for_record() {
//
//        $this->markTestSkipped("Should only be ran during development");
//     //$collectionkey = 'AUTestingRecords2ExampleCollectionForLargeNumberRelations31ServiceDiscovery';
//    $collectionkey = 'AODN/ec86f035-d4c9-4ff3-e044-00144fdd4fa6AUT3de';
//    $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
//    //dd($record);
//
//    $links = ServiceDiscovery::getServiceLinksForRegistryObject($record);
//
//    $this->assertEquals(26, count($links));
//
//        $links = ServiceDiscovery::processLinks($links);
//       $links = ServiceDiscovery::formatLinks($links);
//        echo(json_encode($links));
//    }

//        /** @test **/
//    public function test_links_via_url(){
//        $url = "https://test.ands.org.au/mock/get/AUTestingRecords_WMS_Response_7_v1.3.0";
//
//        $links = ServiceDiscovery::getServicesBylinks($url);
//        $links = ServiceDiscovery::processLinks($links);
//        $links = ServiceDiscovery::formatLinks($links);
//        $this->assertEquals(1, count($links));
//
//    }


//
//
//    /** @test **/
//    public function test_get_links_for_record_ids() {
//        $ro_ids = array(72321);
//        $links = ServiceDiscovery::getServiceByRegistryObjectIds($ro_ids);
//
//        $this->assertEquals(5, count($links));
//        //echo(json_encode($links));
//        $links = ServiceDiscovery::processLinks($links);
//        //echo(json_encode($links));
//        $links = ServiceDiscovery::formatLinks($links);
//        echo(json_encode($links));
//    }
//
//    /** @test **/
//    public function test_ge_record_via_link(){
//        $url = "http://imosmest.aodn.org.au:80/geonetwork/srv/en/metadata.show";
//
//        $links = ServiceDiscovery::getServicesBylinks($url);
//        $json = ServiceDiscovery::getLinkasJson($links);
//        //echo($json);
//        $this->assertEquals(8, count($links));
//
//    }
//    /** @test **/
//    public function test_get_links_as_json(){
//        //$ro_ids = array(919,931,925);
//        $ro_ids = array(1202582,1202607,1202675);
//        $links = ServiceDiscovery::getServiceByRegistryObjectIds($ro_ids);
//        $links = ServiceDiscovery::processLinks($links);
//        $json = ServiceDiscovery::formatLinks($links);
//        echo($json);
//    }

}