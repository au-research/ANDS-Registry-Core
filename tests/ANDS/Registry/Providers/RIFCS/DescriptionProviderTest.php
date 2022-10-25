<?php

namespace ANDS\Registry\Providers\RIFCS;


use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\RegistryObject;

class DescriptionProviderTest extends \RegistryTestClass
{
    /** @test */
    function it_get_the_brief_description()
    {
        $record = $this->ensureKeyExist("AUTCollection1");
        $descriptions = DescriptionProvider::get($record);
        $this->assertNotNull($descriptions['brief']);
        $this->assertRegexp('/brief/', $descriptions['brief']);
    }

    /** @test */
    function it_get_the_full_description()
    {
        $record = $this->ensureKeyExist("AUTCollection1");
        $descriptions = DescriptionProvider::get($record);
        $this->assertNotNull($descriptions['full']);
        $this->assertRegexp('/full/', $descriptions['full']);
    }

    /** @test */
    function it_get_the_primary_description()
    {
        $record = $this->ensureKeyExist("AUTCollection1");
        $descriptions = DescriptionProvider::get($record);
        $this->assertNotNull($descriptions['primary_description']);
        $this->assertRegexp('/brief/', $descriptions['primary_description']);
    }

    public function test_getIndexableArray() {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        $index = DescriptionProvider::getIndexableArray($record);
        $this->assertNotEmpty($index);
        $this->assertArrayHasKey('description_type', $index);
        $this->assertArrayHasKey('description_value', $index);
        $this->assertGreaterThan(1, $index['description_type']);
        $this->assertSameSize($index['description_type'], $index['description_value']);
    }
}
