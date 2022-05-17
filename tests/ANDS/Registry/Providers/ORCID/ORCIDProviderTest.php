<?php

namespace ANDS\Registry\Providers\ORCID;


use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\RegistryObject;

class ORCIDProviderTest extends \MyceliumTestClass
{
    /** @test */
    public function test_it_has_publicationDate()
    {
        $record = $this->stub(RegistryObject::class, [
            'class' => 'collection',
            'type' => 'dataset',
            'key' => 'AODN979e950f-5197-431b-86e1-07d8cd09e99f'
        ]);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/orcid_collection_2.xml')
        ]);
        $this->myceliumInsert($record);
        $record = $this->ensureIDExist($record->id);
        $orcid = ORCIDProvider::getORCID($record, $this->mockORCIDStub());

        $this->assertArrayHasKey('publication-date', $orcid->toArray());

        $this->myceliumDelete($record);

    }
    public function test_it_only_year_publicationDate()
    {
        $record = $this->stub(RegistryObject::class, [
            'class' => 'collection',
            'type' => 'dataset',
            'key' => 'AODN979e950f-5197-431b-86e1-07d8cd09e99f'
        ]);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/orcid_collection_2.xml')
        ]);
        $this->myceliumInsert($record);
        $record = $this->ensureIDExist($record->id);
        $orcid = ORCIDProvider::getORCID($record, $this->mockORCIDStub());
        $publication_date = ($orcid->toArray());
        $this->assertArrayHasKey('year', $publication_date['publication-date']);
        $this->assertArrayNotHasKey('month',$publication_date['publication-date']);
        $this->assertArrayNotHasKey('day', $publication_date['publication-date']);

        $this->myceliumDelete($record);

    }

    /** @test */
    public function test_it_has_citation_info_contributor()
    {
        $record = $this->stub(RegistryObject::class, [
            'class' => 'collection',
            'type' => 'dataset',
            'key' => 'AODN979e950f-5197-431b-86e1-07d8cd09e99f'
        ]);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/orcid_collection_2.xml')
        ]);
        $this->myceliumInsert($record);
        $record = $this->ensureIDExist($record->id);
        $orcid = ORCIDProvider::getORCID($record, $this->mockORCIDStub());
        $this->assertArrayHasKey('contributors', $orcid->toArray());
        $this->myceliumDelete($record);
    }

    /** @test */
    public function test_it_has_related_object_contributor()
    {
        $record = $this->stub(RegistryObject::class, [
            'class' => 'collection',
            'type' => 'dataset',
            'key' => '13181AUT22a'
        ]);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/orcid_collection_1.xml')
        ]);
        $this->myceliumInsert($record);


        $party = $this->stub(RegistryObject::class, [
            'class' => 'party',
            'type' => 'person',
            'key' => 'PartyOrcid1',
            'title' => 'Thompson, Peter']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $party->id,
            'data' => Storage::disk('test')->get('rifcs/orcid_party_1.xml')
        ]);

        $this->myceliumInsert($party);

        $orcid = ORCIDProvider::getORCID($record, $this->mockORCIDStub());
        $contributors =  $orcid->toArray();
        $this->assertArrayHasKey('contributors', $orcid->toArray());
        $this->assertArrayHasKey('contributor-orcid', $contributors['contributors'][0]);
        $this->myceliumDelete($record);
        $this->myceliumDelete($party);
    }

    private function mockORCIDStub()
    {
        $orcid = new ORCIDRecord();
        $orcid->orcid_id = "0000-0003-0670-6058";
        return $orcid;
    }

    public function test_obtain_orcid()
    {

        $data['orcid'] = "0000-0003-0670-6058";
        $data['name'] = "Sarah Graham";
        $data['access_token'] = "eb0dbd0e-e7ab-4283-9da3-8709c3c2b4a1";
        $data['refresh_token'] = "baae832d-460b-4dff-a405-35da4b7ff21e";
        ORCIDRecordsRepository::firstOrCreate("0000-0003-0670-6058", $data);

        $orcid = ORCIDRecordsRepository::obtain("0000-0003-0670-6058");
        $this->assertInstanceOf( ORCIDRecord::class, $orcid);
        $this->assertEquals("Sarah Graham", $orcid->full_name);
    }
}
