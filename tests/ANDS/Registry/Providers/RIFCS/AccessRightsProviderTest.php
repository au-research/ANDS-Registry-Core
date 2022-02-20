<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\File\Storage;

class AccessRightsProviderTest extends \RegistryTestClass
{

    /** @test */
    public function test_it_gets_access_rights_by_tag()
    {
        // given a record
        /** @var RegistryObject */
        //test secret tag open
        $record = $this->stub(RegistryObject::class,
            ['class' => 'collection', 'type' => 'dataset', 'key' => 'AUTESTING_MINIMAL_COLLECTION']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_minimal.xml')
        ]);
        $this->stub(RegistryObject\Tag::class, [
            'key' => $record->key,
            'tag' =>  RegistryObject\Tag::$SECRET_TAG_ACCESS_OPEN
        ]);
        $access_rights_metadata = AccessRightsProvider::getIndexableArray($record);
        $this->assertNotNull($access_rights_metadata);
        $this->assertSame($access_rights_metadata['access_rights'], 'open');

        //test secret tag conditional
        $record = $this->stub(RegistryObject::class,
            ['class' => 'collection', 'type' => 'dataset', 'key' => 'AUTESTING_MINIMAL_COLLECTION']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_minimal.xml')
        ]);
        $this->stub(RegistryObject\Tag::class, [
            'key' => $record->key,
            'tag' =>  RegistryObject\Tag::$SECRET_TAG_ACCESS_CONDITIONAL
        ]);
        $access_rights_metadata = AccessRightsProvider::getIndexableArray($record);
        $this->assertNotNull($access_rights_metadata);
        $this->assertSame($access_rights_metadata['access_rights'], 'conditional');

        //test secret tag restricted
        $record = $this->stub(RegistryObject::class,
            ['class' => 'collection', 'type' => 'dataset', 'key' => 'AUTESTING_MINIMAL_COLLECTION']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_minimal.xml')
        ]);
        $this->stub(RegistryObject\Tag::class, [
            'key' => $record->key,
            'tag' =>  RegistryObject\Tag::$SECRET_TAG_ACCESS_RESTRICTED
        ]);
        $access_rights_metadata = AccessRightsProvider::getIndexableArray($record);
        $this->assertNotNull($access_rights_metadata);
        $this->assertSame($access_rights_metadata['access_rights'], 'restricted');
    }
    /** @test */
    public function test_it_gets_access_rights_by_licence()
    {
        // given a record
        /** @var RegistryObject */
        //test rights by licence
        $record = $this->stub(RegistryObject::class,
            ['class' => 'collection', 'type' => 'dataset', 'key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);

        $access_rights_metadata = AccessRightsProvider::getIndexableArray($record);
        $this->assertNotNull($access_rights_metadata);
        $this->assertSame($access_rights_metadata['access_rights'], 'open');

    }
    /** @test */
    public function test_it_gets_access_rights_by_direct_download()
    {
        // given a record
        /** @var RegistryObject */
        //test rights by licence
        $record = $this->stub(RegistryObject::class,
            ['class' => 'collection', 'type' => 'dataset', 'key' => 'AUTESTING_ALL_ELEMENTS_TEST']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        $access_rights_metadata = AccessRightsProvider::getIndexableArray($record);
        $this->assertNotNull($access_rights_metadata);
        $this->assertSame($access_rights_metadata['access_rights'], 'open');

    }

    /** @test */
    public function test_it_gets_default_access_rights()
    {
        // given a record no tags, rights licence or direct download
        /** @var RegistryObject */
        //test no rights
        $record = $this->stub(RegistryObject::class,
            ['class' => 'collection', 'type' => 'dataset', 'key' => 'AUTESTING_MINIMAL_COLLECTION']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_minimal.xml')
        ]);

        $access_rights_metadata = AccessRightsProvider::getIndexableArray($record);
        $this->assertNotNull($access_rights_metadata);
        $this->assertSame($access_rights_metadata['access_rights'], 'Other');

    }

    /** @test */
    public function test_it_gets_access_methods()
    {
        // given a record
        /** @var RegistryObject */
        //test access_methods
        $record = $this->stub(RegistryObject::class,
            ['class' => 'collection', 'type' => 'dataset', 'key' => 'AUTESTING_ALL_ELEMENTS_TEST']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        $access_rights_metadata = AccessRightsProvider::getIndexableArray($record);
        $this->assertNotNull($access_rights_metadata);
        $expected = ["directDownload","landingPage"];
        $this->assertSame($access_rights_metadata['access_methods'], $expected);

    }
}
