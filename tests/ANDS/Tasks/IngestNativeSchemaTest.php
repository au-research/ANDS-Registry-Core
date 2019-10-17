<?php

namespace ANDS\API\Task\ImportSubTask;
use \ANDS\Registry\Schema;
use \ANDS\Repository\DataSourceRepository;
use \ANDS\Registry\ContentProvider\ContentProvider;

use Exception;

class IngestNativeSchemaTest extends \RegistryTestClass
{
    
    /** @test */
    
    public function test_jsonld_provider_type()
    {

        $dataSourceID = 38360;
        $providerClassName = null;

        $native_content_path = __DIR__ ."../../../resources/harvested_contents/open_top.json";

        $data_source = DataSourceRepository::getByID($dataSourceID);

        $providerType = $data_source->getDataSourceAttribute('provider_type');
        $harvestMethod = $data_source->getDataSourceAttribute('harvest_method');

        $contentProvider = ContentProvider::getProvider($providerType['value'], $harvestMethod['value']);

        $fileExtension = $contentProvider->getFileExtension();

        $this->assertEquals('json', $fileExtension);

        $json = file_get_contents($native_content_path);

        $contentProvider->loadContent($json);

        $objects = $contentProvider->getContent();
        foreach($objects as $o){
            $success = IngestNativeSchema::insertNativeObject($o, $dataSourceID);
            $this->assertTrue($success);
        }

    }


    /** @test */

    public function only_dev_test_single_son()
    {

        $dataSourceID = 42618;
        $providerClassName = null;

        $native_content_path = __DIR__ ."../../../resources/harvested_contents/single_json.json";
        //$native_content_path = __DIR__ ."../../../resources/harvested_contents/open_top.json";

        $data_source = DataSourceRepository::getByID($dataSourceID);

        $providerType = $data_source->getDataSourceAttribute('provider_type');
        $harvestMethod = $data_source->getDataSourceAttribute('harvest_method');

        $contentProvider = ContentProvider::getProvider($providerType['value'], $harvestMethod['value']);

        $fileExtension = $contentProvider->getFileExtension();

        $this->assertEquals('json', $fileExtension);

        $json = file_get_contents($native_content_path);

        $contentProvider->loadContent($json);

        $objects = $contentProvider->getContent();
        foreach($objects as $o){
            $success = IngestNativeSchema::insertNativeObject($o, $dataSourceID);
            $this->assertTrue($success);
        }

    }

    /** @test */

    public function test_pure_provider_type()
    {

        $dataSourceID = 36825;
        $providerClassName = null;

        $native_content_path = __DIR__ ."../../../resources/harvested_contents/pure.xml";

        $data_source = DataSourceRepository::getByID($dataSourceID);

        $providerType = $data_source->getDataSourceAttribute('provider_type');
        $harvestMethod = $data_source->getDataSourceAttribute('harvest_method');

        $contentProvider = ContentProvider::getProvider($providerType['value'], $harvestMethod['value']);

        $fileExtension = $contentProvider->getFileExtension();

        $this->assertEquals('tmp', $fileExtension);

        $json = file_get_contents($native_content_path);

        $contentProvider->loadContent($json);

        $objects = $contentProvider->getContent();
        foreach($objects as $o){
            $success = IngestNativeSchema::insertNativeObject($o, $dataSourceID);
            $this->assertTrue($success);
        }

    }


    /** @test */
    public function test_iso_provider_type()
    {
        $dataSourceID = 10550;
        $native_content_path = __DIR__ ."../../../resources/harvested_contents/csw.xml";

        $data_source = DataSourceRepository::getByID($dataSourceID);

        $providerType = $data_source->getDataSourceAttribute('provider_type');
        $harvestMethod = $data_source->getDataSourceAttribute('harvest_method');

        try{
            $contentProvider = ContentProvider::getProvider($providerType['value'], $harvestMethod['value']);
        }
        catch (Exception $e)
        {
            return;
        }

        $fileExtension = $contentProvider->getFileExtension();

        $this->assertEquals('tmp', $fileExtension);

        $xml = file_get_contents($native_content_path);

        $contentProvider->loadContent($xml);

        $objects = $contentProvider->getContent();
        foreach($objects as $o){
            $success = IngestNativeSchema::insertNativeObject($o, $dataSourceID);
            $this->assertTrue($success);
        }


    }

    /**  @test */
    public function testPrefixGen(){

        $uriList  = array(
            "http://bluenet3.antcrc.utas.edu.au/mcp" => "http://bluenet3.antcrc.utas.edu.au/mcp",
            "iso2005gmd" => "http://www.isotc211.org/2005/gmd",
            "iso19115-3" => "http://standards.iso.org/iso/19115/-3/mdb/1.0",
            "http://schema.org/" => "http://schema.org/",
            "https://pure.bond.edu.au" => "https://pure.bond.edu.au"
        );

        foreach($uriList as $prefix=>$uri)
        {
            $this->assertEquals($prefix, Schema::getPrefix($uri));
        }

    }

    function print_load_error($error, $xml)
    {
        $error_msg  = $xml[$error->line - 1] . "\n";
        $error_msg.= str_repeat('-', $error->column) . "^\n";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $error_msg .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $error_msg .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $error_msg .= "Fatal Error $error->code: ";
                break;
        }

        $error_msg .= trim($error->message) .
            "\n  Line: $error->line" .
            "\n  Column: $error->column";

        if ($error->file) {
            $error_msg .= "\n  File: $error->file";
        }

        print("Errors while loading  Test file  Error message:". $error_msg);
    }
}