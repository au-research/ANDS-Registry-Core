<?php


use ANDS\Registry\Providers\ServiceDiscovery\ServiceDiscovery;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Links;
use ANDS\Repository\RegistryObjectsRepository;

class ServiceDiscoveryTest extends \RegistryTestClass
{
    /** @test **/
    public function test_get_links_for_datasource() {

        $links = ServiceDiscovery::getServiceLinksForDatasource(12);
//        $this->assertEquals(1756, count($links));
        $links = ServiceDiscovery::processLinks($links);
        $links = ServiceDiscovery::formatLinks($links);
        $this->assertNotEmpty($links);
    }

//    /** @test **/
//    public function test_get_links_for_record() {
//     $collectionkey = 'AIMS/0419a746-ddc1-44d2-86e7-e5c402473956';
//    //$collectionkey = 'AIMS/e4cdfaf2-bbb1-44c7-8a07-cf9ffdab747f';
//    $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);
//
//    $links = ServiceDiscovery::getServiceLinksForRegistryObject($record);
//
//    $this->assertEquals(6, count($links));
//
//        $links = ServiceDiscovery::processLinks($links);
//       $links = ServiceDiscovery::formatLinks($links);
//        echo(json_encode($links));
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