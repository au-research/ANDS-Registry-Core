<?php
namespace ANDS\Mycelium;

use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class RelationshipSearchServiceTest extends \MyceliumTestClass
{
    /** @test */
    public function test_search_multiple_to_type()
    {
        $record2 = $this->stub(RegistryObject::class, ['class' => 'party', 'type' => 'group', 'key' => 'AODN']);
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

        $principalInvestigator = [];
        $search_params = ['from_id' => $record3->id, 'to_class' => 'party', 'relation_type' => ['Chief*Investigator'], 'to_type' => ['party', 'group']];
        $result = RelationshipSearchService::search($search_params);

        $investigatorResult = $result->toArray();
        if (isset($investigatorResult['contents']) && count($investigatorResult['contents']) > 0) {
            foreach ($investigatorResult['contents'] as $investigator) {
                $principalInvestigator[] = $investigator['to_title'];
            }
        }

        $this->assertNotEmpty($principalInvestigator);

        $this->myceliumDelete($record2);
        $this->myceliumDelete($record3);

    }

    /** @test */
    public function test_it_should_not_return_related_reverse()
    {
        //TODO set up test data import to determine which data_sources and records will be useful for testing

        $this->ensureKeyExist("AUTestingRecords3ScholixPublicationRecords7");
        $record = RegistryObjectsRepository::getPublishedByKey("AUTestingRecords3ScholixPublicationRecords7");
        $relatedRecords = RelationshipProvider::get($record);

        $this->assertGreaterThan(1, sizeof($relatedRecords));
    }

}

?>
