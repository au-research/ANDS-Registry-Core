<?php
namespace ANDS\Registry\Providers\RIFCS;


use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\RegistryObject;

class AccessProviderTest extends \MyceliumTestClass
{
    /** @test */
    public function it_should_know_about_landing_page()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUTESTING_ALL_ELEMENTS_TEST']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        $this->myceliumInsert($record);
        $actual = AccessProvider::getLandingPage($record, $record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);
    }

    /** @test
    public function test_directDownload()
    {
        //obtain the directDownload from electronic address with type url and target directDownload
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUTESTING_ALL_ELEMENTS_TEST']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        $this->myceliumInsert($record);
        $actual = AccessProvider::getDirectDownload($record, $record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);

        //obtain the directDownload from electronic address with type url and value contains thredds and fileServer
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);
        $actual = AccessProvider::getDirectDownload($record, $record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);

        //obtain the directDownload from service relationships of type that url contains 'thredds' and 'fileServer'
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);

        $record2 = $this->stub(RegistryObject::class, ['class' => 'service','type' => 'report','key' => 'AUTestingRecords4BemV5oyqsNe2loXTafq114J1C0oaXZ4p4']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record2->id,
            'data' => Storage::disk('test')->get('rifcs/service_quality.xml')
        ]);
        $this->myceliumInsert($record2);

        $actual = AccessProvider::getDirectDownload($record, $record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);
        $this->myceliumDelete($record2);
    }
*/
    /** @test
    public function test_ogc_wms()
    {
        // electronic url and relatedInfo
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);
        $actual = AccessProvider::getOGCWMS($record, $record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);
    }
*/
    /** @test
    public function test_ogc_wcs()
    {
        // electronic url and relatedInfo
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);
        $actual = AccessProvider::getOGCWCS($record, $record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);
    }
*/
    /** @test
    public function test_ogc_wfs()
    {
        // electronic url, relatedInfo and relatedObject
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);

        $record2 = $this->stub(RegistryObject::class, ['class' => 'service','type' => 'report','key' => 'AUTestingRecords4BemV5oyqsNe2loXTafq114J1C0oaXZ4p4']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record2->id,
            'data' => Storage::disk('test')->get('rifcs/service_quality.xml')
        ]);
        $this->myceliumInsert($record2);

        $actual = AccessProvider::getOGCWFS($record, $record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);
        $this->myceliumDelete($record2);
    }
*/
    /** @test
    public function test_thredds()
    {
        // electronic url
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);

        $actual = AccessProvider::getTHREDDS($record,$record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);
    }*/

    /** @test
    public function test_thredds_wms() {

        // electronic url
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);

        $actual = AccessProvider::getTHREDDSWMS($record, $record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);
    }
*/

    /** @test
    public function test_get_overall()
    {
        //  relatedObject
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);

        $record2 = $this->stub(RegistryObject::class, ['class' => 'service','type' => 'report','key' => 'AUTestingRecords4BemV5oyqsNe2loXTafq114J1C0oaXZ4p4']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record2->id,
            'data' => Storage::disk('test')->get('rifcs/service_quality.xml')
        ]);
        $this->myceliumInsert($record2);

        $actual = AccessProvider::get($record);
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey("THREDDS", $actual);
        $this->myceliumDelete($record);
        $this->myceliumDelete($record2);
    }*/

    /** @test
    public function test_geoserver()
    {
        // electronic url
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);

        $actual = AccessProvider::getGeoServer($record, $record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);
    }
*/
    /** @test
    public function test_landingpage_geoserver_and_wfs()
    {
        // electronic url
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);

        $actual = AccessProvider::get($record);
        $this->assertArrayHasKey("landingPage", $actual);
        $this->assertArrayHasKey("GeoServer", $actual);
        $this->assertArrayHasKey("OGC:WFS", $actual);
    }
*/
/**
    public function test_thredds_opendap()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);


        $record2 = $this->stub(RegistryObject::class, ['class' => 'service','type' => 'report','key' => 'AUTestingRecords4BemV5oyqsNe2loXTafq114J1C0oaXZ4p4']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record2->id,
            'data' => Storage::disk('test')->get('rifcs/service_quality.xml')
        ]);
        $this->myceliumInsert($record2);

        $actual = AccessProvider::getTHREDDSOPeNDAP($record, $record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);
        $this->myceliumDelete($record2);

    }
 */
/**
    public function test_contact_custodian()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection', 'type' => 'dataset', 'key' => 'AUTala.org.au/dr6006']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_contact_custodian.xml')
        ]);
        $this->myceliumInsert($record);

        $actual = AccessProvider::getContactCustodian($record, $record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);

    }*/
/**
    public function test_other()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection', 'type' => 'dataset', 'key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_access_provider_other.xml')
        ]);
        $this->myceliumInsert($record);

        $actual = AccessProvider::getOther($record, $record->getCurrentData()->data);
        $this->assertNotEmpty($actual);
        $this->myceliumDelete($record);
    }

    public function test_ogcwps()
    {
        // electronic url
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);

        $actual = AccessProvider::get($record);
        $this->assertArrayHasKey("OGC:WPS", $actual);
        $this->assertNotEmpty("OGC:WPS", $actual);
        $this->myceliumDelete($record);
    }
 * */
}
