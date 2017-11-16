<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Providers\RIFCS\JsonLDProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class JsonLDProviderTest extends \RegistryTestClass
{
    /** @test **/
    public function it_should_output_json_encode_object()
    {

        $key = "http://hdl.handle.net/2328.1/1191AUT6a";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);
        RelationshipProvider::process($record);
        //dd(RelationshipProvider::get($record));
        $output = JsonLDProvider::process($record);
        echo $output;
    }




}
