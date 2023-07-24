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


    /** @test */
    public function test_process_toolkit_response()
    {
        $response = Storage::disk('test')->get('servicesDiscovery/toolkit_response.json');
        $serviceProducer = new ServiceProducer(\ANDS\Util\Config::get('app.services_registry_url'));
        $serviceProducer->mockResponse($response);
        $rifcs = $serviceProducer->getRegistryObjects();
        $sC = $serviceProducer->getSummary();
        $this->assertContains("<registryObject", $rifcs);
        $this->assertEquals(2, $sC['number_of_service_created']);

    }

    /** @test */
    public function test_get_rif_from_services_json()
    {

        $this->markTestSkipped("Require better test data");

        $sJson = Storage::disk('test')->get('servicesDiscovery/services.json');
        $serviceProducer = new ServiceProducer(\ANDS\Util\Config::get('app.services_registry_url'));
        $serviceProducer->processServices($sJson);
        $rifcs = $serviceProducer->getRegistryObjects();
        $sC = $serviceProducer->getSummary();
        $this->assertContains("<registryObject", $rifcs);
        $this->assertEquals($sC->number_of_service_created, 24);

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