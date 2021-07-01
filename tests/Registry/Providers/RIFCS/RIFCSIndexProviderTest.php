<?php

namespace Registry\Providers\RIFCS;

use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\Registry\Providers\TitleProvider;
use ANDS\RegistryObject;

class RIFCSIndexProviderTest extends \RegistryTestClass
{

    public function test_getIndexCollection()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        // processing that should happen prior
        DatesProvider::process($record);
        TitleProvider::process($record);

        $index = RIFCSIndexProvider::get($record);

        $this->assertNotNull($index);
        $this->assertNotEmpty($index);
        $this->arrayHasKey('id');
        $this->arrayHasKey('slug');
        $this->arrayHasKey('key');
        $this->arrayHasKey('title');
        $this->arrayHasKey('display_title');
    }

    public function test_getCoreIndexableValues()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        DatesProvider::process($record);
        $index = RIFCSIndexProvider::getCoreIndexableValues($record);
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

    public function test_getTitleIndexableValues()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        $index = RIFCSIndexProvider::getTitleIndexableValues($record);
        $this->assertEquals("Collection with all RIF v1.6 elements (primaryName)", $index['display_title']);
        $this->assertEquals("Collection with all RIF v1.6 elements (primaryName)", $index['list_title']);
        $this->assertContains("alternativeName", $index['alt_list_title']);
        $this->assertContains("alternativeName", $index['alt_display_title']);
    }

    public function test_isIndexable()
    {
        // PUBLISHED record is indexable
        $this->assertTrue(
            RIFCSIndexProvider::isIndexable($this->stub(RegistryObject::class, ['status' => 'PUBLISHED']))
        );

        // DRAFT record is not indexable
        $this->assertFalse(
            RIFCSIndexProvider::isIndexable($this->stub(RegistryObject::class, ['status' => 'DRAFT']))
        );

        // PUBLISHED record that is an activity is indexable
        $this->assertTrue(
            RIFCSIndexProvider::isIndexable($this->stub(RegistryObject::class, [
                'status' => 'PUBLISHED',
                'class' => 'activity'
            ]))
        );

        // PUBLISHED activity record that belongs to PROV is not indexable
        $this->assertFalse(
            RIFCSIndexProvider::isIndexable($this->stub(RegistryObject::class, [
                'status' => 'PUBLISHED',
                'class' => 'activity',
                'group' => 'Public Record Office Victoria'
            ]))
        );
    }
}
