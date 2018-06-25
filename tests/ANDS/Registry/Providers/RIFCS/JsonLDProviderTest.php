<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Providers\RIFCS\JsonLDProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

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
}
