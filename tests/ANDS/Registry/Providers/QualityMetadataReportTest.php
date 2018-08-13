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

        // various CheckType are passing
        $types = [
            Types\CheckIdentifier::$name,
            Types\CheckLocation::$name,
            Types\CheckCitationInfo::$name,
            Types\CheckRelatedService::$name,
            Types\CheckRelatedOutputs::$name,
            Types\CheckSubject::$name,
            Types\CheckCoverage::$name
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

        $this->checkType(Types\CheckRelatedParties::$name, $report);
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

        $this->checkType(Types\CheckRelatedActivity::$name, $report);
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


        $this->checkType(Types\CheckRelatedService::$name, $report);
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
        $types = [
            Types\CheckIdentifier::$name,
            Types\CheckLocationAddress::$name,
            Types\CheckRelatedParties::$name,
            Types\CheckRelatedService::$name,
            Types\CheckRelatedOutputs::$name,
            Types\CheckSubject::$name,
            Types\CheckDescription::$name,
            Types\CheckExistenceDate::$name
        ];
        foreach ($types as $type) {
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