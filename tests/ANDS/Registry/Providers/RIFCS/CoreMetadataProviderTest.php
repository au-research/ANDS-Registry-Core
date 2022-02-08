<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\RegistryObject;
use RegistryTestClass;

class CoreMetadataProviderTest extends RegistryTestClass
{

    public function testGetIndexableArray()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        DatesProvider::process($record);
        $index = CoreMetadataProvider::getIndexableArray($record);
        $fields = ['id', 'slug', 'key', 'group', 'status', 'class', 'type', 'data_source_key', 'data_source_id'];
        foreach ($fields as $field) {
            $this->assertContains($field, array_keys($index));
            $this->assertNotNull($index[$field]);
        }
        $this->assertEquals($index['data_source_id'], $record->dataSource->id);
        $this->assertEquals($index['data_source_key'], $record->dataSource->key);

        // date fields
        foreach (['record_modified_timestamp', 'record_created_timestamp'] as $field) {
            $this->assertContains($field, array_keys($index));
            $this->assertNotNull($index[$field]);
        }
    }
}
