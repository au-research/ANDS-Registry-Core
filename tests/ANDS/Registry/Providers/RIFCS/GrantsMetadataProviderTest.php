<?php

namespace ANDS\Registry\Providers\RIFCS;


use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\RegistryObject;

class GrantsMetadataProviderTest extends \MyceliumTestClass
{
    /** @test
    function it_gets_the_grant_metadata()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);

        $record2 = $this->stub(RegistryObject::class, ['class' => 'party', 'type' => 'person', 'key' => 'AODN']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record2->id,
            'data' => Storage::disk('test')->get('rifcs/party_funds_activity.xml')
        ]);
        $this->myceliumInsert($record2);

        $record3 = $this->stub(RegistryObject::class, ['class' => 'activity', 'type' => 'project','key' => 'ACTIVITY_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record3->id,
            'data' => Storage::disk('test')->get('rifcs/activity_grant_network.xml')
        ]);
        $this->myceliumInsert($record3);

        $grants_metadata = GrantsMetadataProvider::getIndexableArray($record3);

        $this->assertNotEmpty($grants_metadata);
        $this->assertArrayHasKey('activity_status', $grants_metadata);
        $this->assertArrayHasKey('funding_amount', $grants_metadata);
        $this->assertArrayHasKey('funding_scheme', $grants_metadata);
        $this->assertArrayHasKey('administering_institution', $grants_metadata);
        $this->assertArrayHasKey('institutions', $grants_metadata);
        $this->assertArrayHasKey('funders', $grants_metadata);
        $this->assertArrayHasKey('researchers', $grants_metadata);
        $this->assertArrayHasKey('principal_investigator', $grants_metadata);
        $this->assertArrayHasKey('earliest_year', $grants_metadata);
        $this->assertArrayHasKey('latest_year', $grants_metadata);

        $this->myceliumDelete($record);
        $this->myceliumDelete($record2);
        $this->myceliumDelete($record3);

    }

    function it_gets_the_limited_grant_metadata()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'COLLECTION_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_grant_network.xml')
        ]);
        $this->myceliumInsert($record);

        $record2 = $this->stub(RegistryObject::class, ['class' => 'party', 'type' => 'person', 'key' => 'AODN']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record2->id,
            'data' => Storage::disk('test')->get('rifcs/party_funds_activity.xml')
        ]);
        $this->myceliumInsert($record2);

        $record3 = $this->stub(RegistryObject::class, ['class' => 'activity', 'type' => 'project','key' => 'ACTIVITY_GRANT_NETWORK']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record3->id,
            'data' => Storage::disk('test')->get('rifcs/activity_grant_network2.xml')
        ]);
        $this->myceliumInsert($record3);

        $grants_metadata = GrantsMetadataProvider::getIndexableArray($record3);

        $this->assertNotEmpty($grants_metadata);
        $this->assertArrayHasKey('activity_status', $grants_metadata);
        $this->assertArrayHasKey('administering_institution', $grants_metadata);
        $this->assertArrayHasKey('institutions', $grants_metadata);
        $this->assertArrayHasKey('funders', $grants_metadata);
        $this->assertArrayHasKey('researchers', $grants_metadata);
        $this->assertArrayHasKey('principal_investigator', $grants_metadata);

        //The following array keys should noty exist
        $this->assertArrayNotHasKey('funding_amount', $grants_metadata);
        $this->assertArrayNotHasKey('funding_scheme', $grants_metadata);
        $this->assertArrayNotHasKey('earliest_year', $grants_metadata);
        $this->assertArrayNotHasKey('latest_year', $grants_metadata);

        $this->myceliumDelete($record);
        $this->myceliumDelete($record2);
        $this->myceliumDelete($record3);

    }
*/
}
