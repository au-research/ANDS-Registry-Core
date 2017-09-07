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
        $this->assertEquals(80, count($links));
    }

    /** @test **/
    public function test_get_links_for_record() {
     $collectionkey = 'AODN/71127e4d-9f14-4c57-9845-1dce0b541d8d';
    //$collectionkey = 'AIMS/e4cdfaf2-bbb1-44c7-8a07-cf9ffdab747f';
    $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);

    $links = ServiceDiscovery::getServiceLinksForRegistryObject($record);

    $this->assertEquals(11, count($links));
    }


    /** @test **/
    public function test_get_links_for_record_ids() {
        $ro_ids = array(336,357,366);
        $links = ServiceDiscovery::getServiceByRegistryObjectIds($ro_ids);

        $this->assertEquals(13, count($links));
    }

    /** @test **/
    public function test_ge_record_via_link(){
        $url = "http://imosmest.aodn.org.au:80/geonetwork/srv/en/metadata.show";

        $links = ServiceDiscovery::getServicesBylinks($url);
        $json = ServiceDiscovery::getLinkasJson($links);
        //echo($json);
        $this->assertEquals(8, count($links));

    }
    /** @test **/
    public function test_get_links_as_json(){
        $ro_ids = array(919,931,925);
        $links = ServiceDiscovery::getServiceByRegistryObjectIds($ro_ids);
        $json = ServiceDiscovery::getLinkasJson($links);
        //echo($json);
    }

}