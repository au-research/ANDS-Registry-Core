<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\File\Storage;
use ANDS\RecordData;
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
        $this->assertArrayHasKey('id', $index);
        $this->assertArrayHasKey('slug', $index);
        $this->assertArrayHasKey('key', $index);
        $this->assertArrayHasKey('title', $index);
        $this->assertArrayHasKey('display_title', $index);
        $this->assertArrayHasKey('description', $index);
        $this->assertArrayHasKey('identifier_type', $index);
        $this->assertArrayHasKey('identifier_value', $index);
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
