<?php

namespace ANDS\Registry\Providers\RIFCS;


use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\RegistryObject;

class MatchingIdentifierProviderTest extends \MyceliumTestClass
{
    function move_to_integration_it_gets_the_matching_identifier_record_ids()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','key' => 'AUTESTING_ALL_ELEMENTS_TEST']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        $this->myceliumInsert($record);

        $record2 = $this->stub(RegistryObject::class, ['class' => 'collection','key' => 'AUTESTING_DUPLICATE_COLLECTION']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record2->id,
            'data' => Storage::disk('test')->get('rifcs/collection_dupe_identifier.xml')
        ]);
        $this->myceliumInsert($record2);

        $identical_record_ids = MatchingIdentifierProvider::getIndexableArray($record);

        $this->myceliumDelete($record);
        $this->myceliumDelete($record2);

        $this->assertArrayHasKey('identical_record_ids', $identical_record_ids);
        $this->assertNotEmpty($identical_record_ids);
    }
}
