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
        // $collectionkey = 'AUTestingRecords3RelatedCollectionDatasetRelObj1';
        $collectionkey = 'NEII/34b50bab-8124-4c78-a1d1-a0a83601ec56';
        $record = RegistryObjectsRepository::getPublishedByKey($collectionkey);

        $links = ServiceDiscovery::getServiceLinksForRegistryObject($record);

        $this->assertEquals(8, count($links));
    }


    /** @test **/
    public function test_get_links_for_record_ids() {
        $ro_ids = array(919,931,925);
        $links = ServiceDiscovery::getServiceByRegistryObjectIds($ro_ids);

        $this->assertEquals(18, count($links));
    }

    /** @test **/
    public function test_ge_record_via_link(){
        $url = "http://maps.eatlas.org.au/maps/ows?service=wms&version=1.1.1&request=GetCapabilities";

        $links = ServiceDiscovery::getRegistryObjectsBylinks($url);

        $this->assertEquals(4, count($links));

    }

    public function test_get_links_as_json(){
        $ro_ids = array(919,931,925);
        $links = ServiceDiscovery::getServiceByRegistryObjectIds($ro_ids);
        $json = ServiceDiscovery::getLinkasJson($links);
        var_dump(json_decode($json));
    }

}