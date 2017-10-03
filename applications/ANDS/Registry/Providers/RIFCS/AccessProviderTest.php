<?php
namespace ANDS\Registry\Providers\RIFCS;


use ANDS\Registry\Providers\MetadataProvider;

class AccessProviderTest extends \RegistryTestClass
{

    /** @test */
    public function it_should_know_about_landing_page()
    {
        $record = $this->ensureKeyExist("AUTCollection1az");
        $actual = AccessProvider::getLandingPage($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);
    }

    /** @test */
    public function test_directDownload()
    {
        $record = $this->ensureKeyExist("AUTCollection1azDD");
        $actual = AccessProvider::getDirectDownload($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        $record = $this->ensureKeyExist("IMOS/1f46d763-7635-43b5-8491-b76c840b5f42sdf");
        $actual = AccessProvider::getDirectDownload($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        $record = $this->ensureKeyExist("AUTNCI/f3617_1034_0143_5106df");
        $actual = AccessProvider::getDirectDownload($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        $record = $this->ensureKeyExist("AUTNCI/f3617_1034_0143_5106asdfd");
        $actual = AccessProvider::getDirectDownload($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        $record = $this->ensureKeyExist("AUTNCI/f3617_1034_0143_5106asdadsffd");
        $actual = AccessProvider::getDirectDownload($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);
    }

    /** @test */
    public function test_ogc_wms()
    {
        // electronic url
        $record = $this->ensureKeyExist("AuTb931b8b1ba754fd666df3b7512a2cab293f4eaa3");
        $actual = AccessProvider::getOGCWMS($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        // relatedInfo test, supports
        $record = $this->ensureKeyExist("AAUTIMOS/77c095e5-7f76-4f83-a5f1-0b3967955904");
        $actual = AccessProvider::getOGCWMS($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        // relatedObject test
        $record = $this->ensureKeyExist("AAUTIMOS/77c095e5-7f76-4f83-a5f1-0b3967955904aff");
        $actual = AccessProvider::getOGCWMS($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);
    }

    /** @test */
    public function test_ogc_wcs()
    {
        // electronic url
        $record = $this->ensureKeyExist("AuTb931b8b1ba754fd666df3b7512a2cab293f4eaa3ab");
        $actual = AccessProvider::getOGCWCS($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        // relatedInfo test, isPresentedBy
        $record = $this->ensureKeyExist("AAUTIMOS/77c095e5-7f76-4f83-a5f1-0b3967955904C");
        $actual = AccessProvider::getOGCWCS($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        // relatedObject test
        $record = $this->ensureKeyExist("AAUTIMOS/77c095e5-7f76-4f83-a5f1-0b3967955904affC");
        $actual = AccessProvider::getOGCWCS($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);
    }

    /** @test */
    public function test_ogc_wfs()
    {
        // electronic url
        $record = $this->ensureKeyExist("AuTb931b8b1ba754fd666df3b7512a2cab293f4eaa3a");
        $actual = AccessProvider::getOGCWFS($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        // relatedInfo test, isPresentedBy
        $record = $this->ensureKeyExist("AAUTIMOS/77c095e5-7f76-4f83-a5f1-0b3967955904f");
        $actual = AccessProvider::getOGCWFS($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        // relatedObject test
        $record = $this->ensureKeyExist("AAUTIMOS/77c095e5-7f76-4f83-a5f1-0b396sdfdf");
        $actual = AccessProvider::getOGCWFS($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);
    }

    /** @test */
    public function test_thredds()
    {
        $keys = [
            "NCI/f3617_1034_0143_5106",
            "AuTb9sdfdfdfs333fsdfddd",
            "AuTb9sdfdfdfs333fsdf",
            "AUTsIMOS/42f34079-73e0-4532-9364-ea7af9958c1c",
            "IMOS/bc6e10a6-4dda-41c0-8639-5c96411efc5aAUTabcr",
            "IMOS/bc6e10a6-4dda-41c0-8639-5c96411efc5aAUT"
        ];

        foreach ($keys as $key) {
            $record = $this->ensureKeyExist($key);
            $actual = AccessProvider::getTHREDDS($record, MetadataProvider::get($record));
            $this->assertNotEmpty($actual);
        }
    }

    /** @test */
    public function test_thredds_wms() {
        $keys = [
            //"IMOS/bc6e10a6-4dda-41c0-8639-5c96411efc5aAUT",
            "AUTsIMOS/42f34079-73e0-4532-9364-ea7af9958c1cr",
            "IMOS/bc6e10a6-4dda-41c0-8639-5c96411efc5aAUTabcr"
        ];

        foreach ($keys as $key) {
            $record = $this->ensureKeyExist($key);
            $actual = AccessProvider::getTHREDDSWMS($record, MetadataProvider::get($record));
            $this->assertNotEmpty($actual);
        }
    }


    /** @test */
    public function test_get_overall()
    {
        $record = $this->ensureKeyExist("NCI/f3617_1034_0143_5106");
        $actual = AccessProvider::get($record);
        $this->assertArrayHasKey("THREDDS", $actual);
    }

    /** @test */
    public function test_geoserver()
    {
        // eletronic url
        $record = $this->ensureKeyExist("AuTb931b8b1ba754fd666df3b7512a2cab293f4eaa3ge");
        $actual = AccessProvider::getGeoServer($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        // relatedObject supports
        $record = $this->ensureKeyExist("AAUTIMOS/77c095e5-7f76-4f83-a5f1-0b3967955904affgE");
        $actual = AccessProvider::getGeoServer($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        // reverse related
        $record = $this->ensureKeyExist("AUTestingRecordsReverseRelationshipsCollection1");
        $actual = AccessProvider::getGeoServer($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);

        // relatedInfo supports
        $record = $this->ensureKeyExist("AAUTIMOS/77c095e5-7f76-4f83-a5f1-0b3967955904age");
        $actual = AccessProvider::getGeoServer($record, MetadataProvider::get($record));
        $this->assertNotEmpty($actual);
    }

    /** @test */
    public function test_landingpage_geoserver_and_wfs()
    {
        $record = $this->ensureKeyExist("AIMS/0419a746-ddc1-44d2-86e7-e5c402473956");
        $actual = AccessProvider::get($record);

        $this->assertArrayHasKey("landingPage", $actual);
        $this->assertArrayHasKey("GeoServer", $actual);
        $this->assertArrayHasKey("OGC:WFS", $actual);
    }

    public function test_thredds_opendap()
    {
        $keys = [
            "IMOS/bc6e10a6-4dda-41c0-8639-5c96411efc5aAUTabdr"
        ];

        foreach ($keys as $key) {
            $record = $this->ensureKeyExist($key);
            $actual = AccessProvider::getTHREDDSOPeNDAP($record, MetadataProvider::get($record));
            $this->assertNotEmpty($actual);
        }
    }

}
