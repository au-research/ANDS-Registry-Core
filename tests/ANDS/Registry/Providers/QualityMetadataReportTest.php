<?php


namespace ANDS\Registry\Providers;


use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Providers\Quality\QualityMetadataProvider;
use ANDS\Registry\Providers\Quality\Types;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\RegistryObject;

class QualityMetadataReportTest extends \MyceliumTestClass
{
    /** @test
     * @throws \Exception

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

        $this->checkReport($report);
    }
*/
    /** @test
     * @throws \Exception

    function it_passes_related_object_party_for_collection()
    {
        // given a record with an author (party)
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUT_QUALITY_COLLECTION']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_for_related_quality_md.xml')
        ]);
        $this->myceliumInsert($record);

        // with an author (party)
        $party = $this->stub(RegistryObject::class, ['class' => 'party','type' => 'group','key' => 'AUT_QUALITY_PARTY']);

        $this->stub(RecordData::class, [
            'registry_object_id' => $party->id,
            'data' => Storage::disk('test')->get('rifcs/party_for_related_quality_md.xml')
        ]);

        $this->myceliumInsert($party);
        // author address with lines are present
        CoreMetadataProvider::process($record);
        CoreMetadataProvider::process($party);
        // when get reports
        $report = QualityMetadataProvider::getMetadataReport($record);

        $this->checkType(Types\CheckRelatedParties::class, $report);

        $this->checkReport($report);
    }
*/
    /** @test
     * @throws \Exception

    function it_passes_reverse_related_party_for_collection()
    {
        // given a record
        // given a record with an author (party)
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUT_QUALITY_COLLECTION_2']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_for_reverse_related_party_quality_md.xml')
        ]);
        $this->myceliumInsert($record);

        // with an author (party)
        $party = $this->stub(RegistryObject::class, ['class' => 'party','type' => 'group','key' => 'AUT_QUALITY_PARTY']);

        $this->stub(RecordData::class, [
            'registry_object_id' => $party->id,
            'data' => Storage::disk('test')->get('rifcs/party_for_related_quality_md.xml')
        ]);

        $this->myceliumInsert($party);

        // author address with lines are present
        CoreMetadataProvider::process($record);
        CoreMetadataProvider::process($party);

        // when get reports
        $report = QualityMetadataProvider::getMetadataReport($record);

        $this->checkType(Types\CheckRelatedParties::class, $report);

        $this->checkReport($report);
    }
*/
    /** @test
     * @throws \Exception

    function it_passes_related_object_activity_for_collection()
    {
        // given a record with an author (party)
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUT_QUALITY_COLLECTION']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_for_related_quality_md.xml')
        ]);
        $this->myceliumInsert($record);

        // with an author (party)
        $activity = $this->stub(RegistryObject::class, ['class' => 'activity','type' => 'grant','key' => 'AUT_QUALITY_ACTIVITY']);

        $this->stub(RecordData::class, [
            'registry_object_id' => $activity->id,
            'data' => Storage::disk('test')->get('rifcs/activity_for_related_quality_md.xml')
        ]);

        $this->myceliumInsert($activity);
        // author address with lines are present
        CoreMetadataProvider::process($record);
        CoreMetadataProvider::process($activity);

        // when get reports
        $report = QualityMetadataProvider::getMetadataReport($record);

        $this->checkType(Types\CheckRelatedActivity::class, $report);

        $this->checkReport($report);
    }
*/
    /** @test
     * @throws \Exception

    function it_passes_related_object_service_for_collection()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUT_QUALITY_COLLECTION']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_for_related_quality_md.xml')
        ]);
        $this->myceliumInsert($record);

        // with an author (party)
        $service = $this->stub(RegistryObject::class, ['class' => 'service','type' => 'OGC:WMF','key' => 'AUT_QUALITY_SERVICE']);

        $this->stub(RecordData::class, [
            'registry_object_id' => $service->id,
            'data' => Storage::disk('test')->get('rifcs/service_for_related_quality_md.xml')
        ]);

        $this->myceliumInsert($service);
        // author address with lines are present
        CoreMetadataProvider::process($record);
        CoreMetadataProvider::process($service);

        // when get reports
        $report = QualityMetadataProvider::getMetadataReport($record);

        $this->checkType(Types\CheckRelatedService::class, $report);

        $this->checkReport($report);
    }
*/
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
        foreach (QualityMetadataProvider::getChecksForClass('activity') as $type) {
            $this->checkType($type, $report);
        }

        $this->checkReport($report);
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
        foreach (QualityMetadataProvider::getChecksForClass('party') as $type) {
            $this->checkType($type, $report);
        }

        $this->checkReport($report);
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
        foreach (QualityMetadataProvider::getChecksForClass('service') as $type) {
            $this->checkType($type, $report);
        }

        $this->checkReport($report);
    }

    /** @test
     * @throws \Exception

    function it_checks_for_empty_identifier()
    {
        // given a collection with an empty identifier tag
        $record = $this->stub(RegistryObject::class, ['class' => 'service']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_minimal.xml')
        ]);

        // when get reports
        $report = QualityMetadataProvider::getMetadataReport($record);

        // it should fail for CheckIdentifier
        $actual = collect($report)->where('name', Types\CheckIdentifier::class)->first();
        $this->assertEquals(Types\CheckType::$FAIL, $actual['status'], "CheckIdentifier should fail");
    }*/

    /**
     * Helper method to check the consistency of the report
     *
     * @param $report
     */
    private function checkReport($report)
    {
        // all of them have descriptor and message and none are blanks
        foreach ($report as $check) {
            $this->assertArrayHasKey('descriptor', $check);
            $this->assertArrayHasKey('message', $check);
            $this->assertNotEmpty($check['descriptor']);
            $this->assertNotEmpty($check['message']);
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