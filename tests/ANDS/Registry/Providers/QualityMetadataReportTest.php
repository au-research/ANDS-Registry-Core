<?php


namespace ANDS\Registry\Providers;


use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Providers\Quality\Types\CheckCitationInfo;
use ANDS\Registry\Providers\Quality\Types\CheckCoverage;
use ANDS\Registry\Providers\Quality\Types\CheckIdentifier;
use ANDS\Registry\Providers\Quality\Types\CheckLocation;
use ANDS\Registry\Providers\Quality\Types\CheckRelatedActivity;
use ANDS\Registry\Providers\Quality\Types\CheckRelatedOutputs;
use ANDS\Registry\Providers\Quality\Types\CheckRelatedParties;
use ANDS\Registry\Providers\Quality\Types\CheckRelatedService;
use ANDS\Registry\Providers\Quality\Types\CheckSubject;
use ANDS\Registry\Providers\Quality\Types\CheckType;
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
            CheckIdentifier::$name,
            CheckLocation::$name,
            CheckCitationInfo::$name,
            CheckRelatedService::$name,
            CheckRelatedOutputs::$name,
            CheckSubject::$name,
            CheckCoverage::$name
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

        $this->checkType(CheckRelatedParties::$name, $report);
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

        $this->checkType(CheckRelatedActivity::$name, $report);
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


        $this->checkType(CheckRelatedService::$name, $report);
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
            CheckIdentifier::$name,
//            CheckLocationAddress::$name,
//            'relatedParties',
//            'relatedService',
//            'relatedCollections',
//            'subject',
//            'description',
//            'existenceDate'
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
        $this->assertEquals(CheckType::$PASS, $actual['status'], "$type is passing");
    }
}