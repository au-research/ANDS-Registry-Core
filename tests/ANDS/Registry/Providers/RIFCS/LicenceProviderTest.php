<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\File\Storage;

class LicenceProviderTest extends \RegistryTestClass
{

    /** @test */
    public function test_it_gets_the_licence_rights()
    {
        // given a record
        /** @var RegistryObject */
        //test get of licence rights
        $record = $this->stub(RegistryObject::class,
            ['class' => 'collection', 'type' => 'dataset', 'key' => 'AUTESTING_ALL_ELEMENTS_TEST']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        $rights = LicenceProvider::get($record);
        $this->assertNotNull($rights);
    }

    /** @test */
    public function test_it_gets_the_licence_class()
    {
        // given a record
        /** @var RegistryObject */
        //test license_class
        $record = $this->stub(RegistryObject::class,
            ['class' => 'collection', 'type' => 'dataset', 'key' => 'AUTESTING_ALL_ELEMENTS_TEST']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        $license_class = LicenceProvider::getIndexableArray($record);
        $this->assertNotNull($license_class);
        $this->assertSame($license_class['license_class'], 'open licence');
    }
}
