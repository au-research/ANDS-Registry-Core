<?php

/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 13/8/18
 * Time: 10:36 AM
 */
use ANDS\File\Storage;
use ANDS\Registry\Providers\ServiceDiscovery\ServiceProducer;
use ANDS\Registry\Providers\ServiceDiscovery\ServiceDiscovery;

class ServiceProducerTest extends \RegistryTestClass
{

    /** @test **/
    public function test_get_rif_from_url_and_type() {

        $serviceProducer = new ServiceProducer(\ANDS\Util\Config::get('app.services_registry_url'));
        $serviceProducer->getServicebyURL("http://acef.tern.org.au/geoserver/wms" , "WMS");
        $rifcs = $serviceProducer->getRegistryObjects();
        $sC = $serviceProducer->getServiceCount();
        $this->assertContains("<registryObject", $rifcs);
        $this->assertEquals($sC, 1);
    }

    /** @test */
    public function test_get_rif_from_services_json()
    {
        $this->markTestSkipped("Require better test data");

        $sJson = Storage::disk('test')->get('servicesDiscovery/services.json');
        $serviceProducer = new ServiceProducer(\ANDS\Util\Config::get('app.services_registry_url'));
        $serviceProducer->processServices($sJson);
        $rifcs = $serviceProducer->getRegistryObjects();
        $sC = $serviceProducer->getServiceCount();
        $this->assertContains("<registryObject", $rifcs);
        $this->assertEquals($sC, 24);

    }

    /** @test **/
    public function test_get_links_for_datasource_79() {

        $this->markTestSkipped("Big integration tests, should only be ran during development");
        $links = \ANDS\Cache\Cache::file()->rememberForever('testLinksAODN', function() {
            $links = ServiceDiscovery::getServiceLinksForDatasource(79);
            $links = ServiceDiscovery::processLinks($links);
            $links = ServiceDiscovery::formatLinks($links);
            return $links;
        });
        $serviceProducer = new ServiceProducer(\ANDS\Util\Config::get('app.services_registry_url'));
        $serviceProducer->processServices(json_encode($links));
        $serviceCount = $serviceProducer->getServiceCount();
        $this->assertGreaterThan(0, $serviceCount);


       // $this->assertNotEmpty($links);
    }

    public function setUp()
    {
        if (\ANDS\Util\Config::get('app.services_registry_url') === null) {
            $this->markTestSkipped("Service Registry URL not configured");
        }
    }

}