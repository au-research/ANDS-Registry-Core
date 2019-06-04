<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Providers\RIFCS\JsonLDProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Registry\Providers\GrantsConnectionsProvider;

class JsonLDProviderTest extends \RegistryTestClass
{
    /** @test **/
    public function it_should_output_json_encode_object_collection()
    {

        $key = "AUTSchemaOrgCollection1";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);
        $output = JsonLDProvider::process($record);
//        echo $output;
        // TODO add assertions
    }

    /** @test **/
    public function it_should_output_json_encode_object_software()
    {

        $key = "GA/07275e06-056f-1579-e054-00144fdd4fa6";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);
        $output = JsonLDProvider::process($record);
//        echo $output;
        // TODO add assertions
    }

    /** @test **/
    public function it_should_output_json_encode_object_service()
    {

        $key = "GA/fc15ee64-97f1-4171-bc58-36ff77ded053";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);
        $output = JsonLDProvider::process($record);
//        echo $output;
        // TODO add assertions
    }


    /** @test **/
    public function it_should_convert_dcmi_box()
    {
        $dcmiText = "northlimit=4.65; southlimit=3.652; westlimit=100.887; eastLimit=102.066;";
        $output = JsonLDProvider::getGeo($dcmiText);
        self::assertEquals("3.652 100.887 4.65 102.066", $output['box']);

    }

    /** @test **/
    public function it_should_convert_dcmi_point()
    {
        $dcmiText = "north=4.65; east=102.066;";
        $output = JsonLDProvider::getCoordinates($dcmiText);
        self::assertEquals("GeoCoordinates", $output['@type']);
        self::assertEquals("4.65", $output['latitude']);

    }

    /** @test **/
    public function it_should_convert_kmlpolycoords_to_box()
    {
        $dcmiText = "124.035156,-18.082766 127.199219,-15.897502 133.878906,-16.404031 140.031250,-13.858969 144.074219,-12.146298 147.414063,-17.077352 149.523438,-23.018655 150.578125,-29.324321 141.261719,-28.374150 138.273438,-23.475493 134.933594,-26.659391 125.968750,-25.236898 119.992188,-21.199421 124.035156,-18.082766";
        $output = JsonLDProvider::getBoxFromCoords($dcmiText);
        self::assertEquals("GeoShape", $output['@type']);
        self::assertEquals("-29.324321 119.992188 -12.146298 150.578125", $output['box']);

    }

    /** @test **/
    public function it_should_convert_kmlpolycoords_to_point()
    {
        $dcmiText = "124.035156,-18.082766";
        $output = JsonLDProvider::getBoxFromCoords($dcmiText);
        self::assertEquals("GeoCoordinates", $output['@type']);
        self::assertEquals("-18.082766", $output['latitude']);

    }


    /** @test **/
    public function it_should_convert_faulty_kmlpolycoords_to_null()
    {
        $dcmiText = "124.035156,fish";
        $output = JsonLDProvider::getBoxFromCoords($dcmiText);
        self::assertNull($output);
    }

    /** @test **/
    public function it_should_convert_faulty_dcmi_box_to_null()
    {
        $dcmiText = "northlimit=4.65; southlimit=3.652; westlimit=; eastLimit=102.066;";
        $output = JsonLDProvider::getGeo($dcmiText);
        self::assertNull($output);

    }

    /** @test **/
    public function it_should_format_temporal_coverages()
    {
        $coverages = array("dateTo"=>"2019-06-07T03:01:02Z", "dateFrom"=>"2019-05-31T03:01:06Z");
        $output = JsonLDProvider::formatTempCoverages($coverages);
        self::assertEquals("2019-05-31/2019-06-07", $output);
    }

    /** @test **/
    public function it_should_find_a_creator()
    {
        $record = $this->ensureKeyExist("AUTSchemaOrgCollection3");
        $data['recordData'] = $record->getCurrentData()->data;
        $output = JsonLDProvider::getCreator($record, $data);
        self::assertEquals("AUTParty6Has_Collector", $output[0]['name']);
    }

    /** @test **/
    public function it_should_find_a_funder()
    {
        $record = $this->ensureKeyExist("SchemaDotOrgJLDRecords1");
        $data['recordData'] = $record->getCurrentData()->data;
        $output = JsonLDProvider::getFunder($record, $data);
        self::assertEquals("The Great Funder", $output[0]['name']);
    }

    /** @test **/
    public function it_should_find_a_publication()
    {
        $record = $this->ensureKeyExist("AUTSchemaOrgCollection1");
        $data['recordData'] = $record->getCurrentData()->data;
        $output = JsonLDProvider::getRelatedPublications($record);
        dd($output);
        self::assertEquals("https://dx.doi.org/10.1371/journal.pone.0180842", $output[0]['identifier_value']);
    }


}
