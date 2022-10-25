<?php

namespace ANDS\Registry\Providers\RIFCS;


use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\RegistryObject;

class AltmetricsProviderTest extends \MyceliumTestClass
{
    /** @test */
    function it_gets_the_citation_contributor_metadata()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUTestingRecords2AltmetricsRecords12']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/altmetrics_collection_1.xml')
        ]);
        $this->myceliumInsert($record);
        $subset[0] = [ 'type' => "dc.title", 'value' => "Yarra Ranges ICT survey, 2011"];

        $altmetricsData = AltmetricsProvider::get($record);
        $this->assertNotEmpty($altmetricsData);
        $this->assertArraySubset($subset, $altmetricsData);

        $this->myceliumDelete($record);

    }

    /** @test
    function it_gets_the_related_contributor_metadata()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUTestingRecords2AltmetricsRecords12a']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/altmetrics_collection_2.xml')
        ]);
        $this->myceliumInsert($record);

        $party = $this->stub(RegistryObject::class, [
            'class' => 'party',
            'type' => 'person',
            'key' => 'AUTestingRecords2AltmetricsPerson2',
            'title' => 'Thompson, Peter']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $party->id,
            'data' => Storage::disk('test')->get('rifcs/altmetrics_party_2.xml')
        ]);
        $this->myceliumInsert($party);


        $subset[3] = ['type' => "dc.creator",'value' =>"Thompson, Peter"];
        $altmetricsData = AltmetricsProvider::get($record);

        $this->assertNotEmpty($altmetricsData);
        $this->assertArraySubset($subset, $altmetricsData);

        $this->myceliumDelete($record);
        $this->myceliumDelete($party);

    }
    */

    /** @test
    function it_gets_the_reverse_related_collector_metadata()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUTestingRecords2AltmetricsRecords12b']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/altmetrics_collection_3.xml')
        ]);
        $this->myceliumInsert($record);

        $party = $this->stub(RegistryObject::class, [
            'class' => 'party',
            'type' => 'person',
            'key' => 'AUTestingRecords2AltmetricsPerson3',
            'title' => 'Tim Smith']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $party->id,
            'data' => Storage::disk('test')->get('rifcs/altmetrics_party_3.xml')
        ]);
        $this->myceliumInsert($party);


        $subset[3] = ['type' => "dc.creator",'value' =>"Tim Smith"];
        $altmetricsData = AltmetricsProvider::get($record);

        $this->assertNotEmpty($altmetricsData);
        $this->assertArraySubset($subset, $altmetricsData);

        $this->myceliumDelete($record);
        $this->myceliumDelete($party);

    }
*/
}
