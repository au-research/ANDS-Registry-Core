<?php

namespace ANDS\API\Task\ImportSubTask;
use \ANDS\Registry\Versions as Versions;
use \ANDS\Registry\Schema;
use \ANDS\API\Task\ImportSubTask\IngestNativeSchema;
use \ANDS\Repository\RegistryObjectsRepository;
use \DOMDocument;
use \ANDS\Repository\DataSourceRepository;
use \ANDS\Registry\ContentProvider\ContentProvider;
use ReflectionClass;
use Exception;

class IngestNativeSchemaTest extends \RegistryTestClass
{
    
    /** @test */
    
    public function test_jsonld_provider_type()
    {

        $dataSourceID = 38099;
        $native_content_path = __DIR__ ."../../../resources/harvested_contents/oakridge.json";

        $data_source = DataSourceRepository::getByID($dataSourceID);

        $providerType = $data_source->getDataSourceAttribute('provider_type');
        $providerClassName = null;

        $providerClassName = ContentProvider::obtain($providerType['value']);

        if($providerClassName == null){
            $harvestMethod = $data_source->getDataSourceAttribute('harvest_method');
            $providerClassName = ContentProvider::obtain($harvestMethod['value']);
        }

        // couldn't find content handler for datasource
        if($providerClassName == null)
            return;

        try{
            $class = new ReflectionClass($providerClassName);
            $contentProvider = $class->newInstanceArgs();
        }
        catch (Exception $e)
        {
            return;
        }

        $fileExtension = $contentProvider->getFileExtension();

        $this->assertEquals('.json', $fileExtension);

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
        $providerClassName = null;

        $providerClassName = ContentProvider::obtain($providerType['value']);

        if($providerClassName == null){
            $harvestMethod = $data_source->getDataSourceAttribute('harvest_method');
            $providerClassName = ContentProvider::obtain($harvestMethod['value']);
        }

        try{
            $class = new ReflectionClass($providerClassName);
            $contentProvider = $class->newInstanceArgs();
        }
        catch (Exception $e)
        {
            return;
        }

        $fileExtension = $contentProvider->getFileExtension();

        $this->assertEquals('.tmp', $fileExtension);

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
            "http://schema.org/" => "http://schema.org/"
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