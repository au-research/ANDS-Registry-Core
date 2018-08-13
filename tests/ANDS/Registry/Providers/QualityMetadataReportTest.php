<?php


namespace ANDS\Registry\Providers;


use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Providers\Quality\Types;
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

        // various CheckType are passing (not all)
        $types = [
            Types\CheckIdentifier::class,
            Types\CheckLocation::class,
            Types\CheckCitationInfo::class,
            Types\CheckRelatedService::class,
            Types\CheckRelatedOutputs::class,
            Types\CheckSubject::class,
            Types\CheckCoverage::class
        ];
        foreach ($types as $type) {
            $this->checkType($type, $report);
        }
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

        $this->checkType(Types\CheckRelatedParties::class, $report);
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

        $this->checkType(Types\CheckRelatedActivity::class, $report);
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

        $this->checkType(Types\CheckRelatedService::class, $report);
    }

    /** @test
     * @throws \Exception
     */
    function it_validates_activities()
    {
        // given an activity
        $record = $this->stub(RegistryObject::class, ['class' => 'activity']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/activity_quality.xml')
        ]);

        // when get reports
        $report = QualityMetadataProvider::getMetadataReport($record);

        // each of the following CheckType should pass
        foreach (QualityMetadataProvider::$activityChecks as $type) {
            $this->checkType($type, $report);
        }
    }

    /** @test
     * @throws \Exception
     */
    function it_validates_parties()
    {
        // given an activity
        $record = $this->stub(RegistryObject::class, ['class' => 'party']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/party_quality.xml')
        ]);

        // when get reports
        $report = QualityMetadataProvider::getMetadataReport($record);

        // each of the following CheckType should pass
        foreach (QualityMetadataProvider::$partyChecks as $type) {
            $this->checkType($type, $report);
        }
    }

    /** @test
     * @throws \Exception
     */
    function it_validates_services()
    {
        // given a service
        $record = $this->stub(RegistryObject::class, ['class' => 'service']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/service_quality.xml')
        ]);

        // when get reports
        $report = QualityMetadataProvider::getMetadataReport($record);

        // each of the following CheckType should pass
        foreach (QualityMetadataProvider::$serviceChecks as $type) {
            $this->checkType($type, $report);
        }
    }

    /**
     * Helper function to quickly check a name type
     *
     * @param $type
     * @param $report
     */
    private function checkType($type, $report) {
        $actual = collect($report)->where('name', $type)->first();
        $this->assertEquals(Types\CheckType::$PASS, $actual['status'], "$type is passing");
    }
}