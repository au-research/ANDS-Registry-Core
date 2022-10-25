<?php

namespace Mycelium;

use ANDS\File\Storage;
use ANDS\Mycelium\MyceliumImportPayloadProvider;
use ANDS\RecordData;
use ANDS\RegistryObject;
use RegistryTestClass;

class MyceliumImportPayloadProviderTest extends RegistryTestClass
{

    public function testGet_noPrimaryKey()
    {
        $record = $this->stub(RegistryObject::class);
        $rifcs = Storage::disk('test')->get('rifcs/collection_minimal.xml');
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => $rifcs
        ]);
        $payload = MyceliumImportPayloadProvider::get($record);
        $this->assertEquals($record->id, $payload['registryObjectId']);
        $this->assertEquals(base64_encode($rifcs), $payload['rifcs']);
        $this->assertEquals($record->title, $payload['title']);
        $this->assertEquals($record->datasource->id, $payload['dataSource']['id']);
        $this->assertEquals([], $payload['additionalRelations']);
    }

    public function testPrimaryKeyRelations_noPrimaryKeyExists()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $record->datasource->setDataSourceAttribute('create_primary_relationships', DB_TRUE);
        $record->datasource->setDataSourceAttribute('primary_key_1', 'random-key-doesntexist');

        $payload = MyceliumImportPayloadProvider::get($record);
        $this->assertEquals([], $payload['additionalRelations']);
    }

    public function testPrimaryKeyRelations_noRelationExist()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $pk = $this->stub(RegistryObject::class, ['class' => 'party']);
        $record->datasource->setDataSourceAttribute('create_primary_relationships', DB_TRUE);
        $record->datasource->setDataSourceAttribute('primary_key_1', $pk->key);

        $payload = MyceliumImportPayloadProvider::get($record);
        $this->assertEquals([], $payload['additionalRelations']);
    }

    public function testPrimaryKeyRelations_relationExist()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $pk = $this->stub(RegistryObject::class, ['class' => 'party']);
        $record->datasource->setDataSourceAttribute('create_primary_relationships', DB_TRUE);
        $record->datasource->setDataSourceAttribute('primary_key_1', $pk->key);
        $record->datasource->setDataSourceAttribute('collection_rel_1', 'isManagedBy');

        $payload = MyceliumImportPayloadProvider::get($record);
        $this->assertCount(1, $payload['additionalRelations']);
        $this->assertEquals($pk->key, $payload['additionalRelations'][0]['toKey']);
        $this->assertEquals('isManagedBy', $payload['additionalRelations'][0]['relationType']);
        $this->assertEquals('PRIMARY-KEY', $payload['additionalRelations'][0]['origin']);
    }

    public function testPrimaryKeyRelationsMultiple()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'party']);
        $pk1 = $this->stub(RegistryObject::class, ['class' => 'party']);
        $pk2 = $this->stub(RegistryObject::class, ['class' => 'party']);
        $record->datasource->setDataSourceAttribute('create_primary_relationships', DB_TRUE);
        $record->datasource->setDataSourceAttribute('primary_key_1', $pk1->key);
        $record->datasource->setDataSourceAttribute('party_rel_1', 'hasAssociationWith');
        $record->datasource->setDataSourceAttribute('primary_key_2', $pk2->key);
        $record->datasource->setDataSourceAttribute('party_rel_2', 'hasAssociationWith');

        $payload = MyceliumImportPayloadProvider::get($record);
        $this->assertCount(2, $payload['additionalRelations']);
    }

}
