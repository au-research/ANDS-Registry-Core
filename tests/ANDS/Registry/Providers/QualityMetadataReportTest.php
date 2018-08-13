<?php


namespace ANDS\Registry\Providers;


use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\RegistryObject;

class QualityMetadataReportTest extends \RegistryTestClass
{
    /** @test
     * @throws \Exception
     */
    function it_provides_quality_report_for_collection()
    {
        // given a record
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);

        // when get quality reports
        $report = QualityMetadataProvider::getMetadataReport($record);

        // identifier is passing
        $identifier = collect($report)->where('name', 'identifier')->first();
        $this->assertEquals(QualityMetadataProvider::$PASS, $identifier['status'], 'Identifier is passing');

        // location is passing
        $location = collect($report)->where('name', 'location')->first();
        $this->assertEquals(QualityMetadataProvider::$PASS, $location['status'], 'Location is passing');

        // citationInfo is passing
        $citationInfo = collect($report)->where('name', 'citationInfo')->first();
        $this->assertEquals(QualityMetadataProvider::$PASS, $citationInfo['status'], 'CitationInfo is passing');

        // relatedOutputs is passing
        $relatedOutputs = collect($report)->where('name', 'relatedOutputs')->first();
        $this->assertEquals(QualityMetadataProvider::$PASS, $relatedOutputs['status'], 'RelatedOutputs is passing');
    }

    /** @test
     * @throws \Exception
     */
    function it_passes_related_object_party_for_collection()
    {
        // given a record
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_minimal.xml')
        ]);

        // relates to a party
        $party = $this->stub(RegistryObject::class, ['class' => 'party']);
        $this->stub(RegistryObject\Relationship::class, ['registry_object_id' => $record->id, 'related_object_key' => $party->key]);

        // when get reports
        $report = QualityMetadataProvider::getMetadataReport($record);

        // relatedParties is passing
        $actual = collect($report)->where('name', 'relatedParties')->first();
        $this->assertEquals(QualityMetadataProvider::$PASS, $actual['status'], 'relatedParties is passing');
    }

    /** @test
     * @throws \Exception
     */
    function it_passes_related_object_activity_for_collection()
    {
        // given a record
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_minimal.xml')
        ]);

        // relates to an activity
        $party = $this->stub(RegistryObject::class, ['class' => 'activity']);
        $this->stub(RegistryObject\Relationship::class, ['registry_object_id' => $record->id, 'related_object_key' => $party->key]);

        // when get reports
        $report = QualityMetadataProvider::getMetadataReport($record);

        // relatedActivities is passing
        $actual = collect($report)->where('name', 'relatedActivities')->first();
        $this->assertEquals(QualityMetadataProvider::$PASS, $actual['status'], 'relatedActivities is passing');
    }

    /** @test
     * @throws \Exception
     */
    function it_passes_related_object_service_for_collection()
    {
        // given a record
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_minimal.xml')
        ]);

        // relates to an activity
        $party = $this->stub(RegistryObject::class, ['class' => 'service']);
        $this->stub(RegistryObject\Relationship::class, ['registry_object_id' => $record->id, 'related_object_key' => $party->key]);

        // when get reports
        $report = QualityMetadataProvider::getMetadataReport($record);

        // relatedServices is passing
        $actual = collect($report)->where('name', 'relatedServices')->first();
        $this->assertEquals(QualityMetadataProvider::$PASS, $actual['status'], 'relatedServices is passing');
    }
}