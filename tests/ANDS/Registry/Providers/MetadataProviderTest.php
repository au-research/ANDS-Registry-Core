<?php

namespace ANDS\Registry\Providers;

use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\RegistryObject;


class MetadataProviderTest extends \RegistryTestClass
{

    /** @test
     * @throws \Exception
     */
    function it_should_have_descriptions()
    {
        // given a collection record
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        $descriptions = MetadataProvider::getDescriptions($record);

        $this->assertGreaterThan(10, sizeof($descriptions));
        // TODO test all functions!!

    }


    /** @test
     * @throws \Exception
     */
    function it_should_return_recordData()
    {
        // given a collection record
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        $data= MetadataProvider::getSelective($record, ["relationships", "recordData"]);

        $this->assertGreaterThan(1, sizeof($data));
        // TODO test all functions!!
        $this->assertEquals($data['relationships'], "relationships Information is no longer provided by this sevice!");

    }
}
