<?php


namespace ANDS\Providers\RIFCS;


use ANDS\DataSource;
use ANDS\RecordData;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;
use Carbon\Carbon;

class DatesProviderTest extends \RegistryTestClass
{
    protected $ds = [
        'key' => 'automated-test',
        'title' => 'Automatically Generated Records'
    ];

    protected $tKey = 'automated-test';

    /** @test */
    function a_record_with_2_data_modified_date_is_the_latest()
    {
        // given a record
        /** @var RegistryObject */
        $record = $this->stub(RegistryObject::class, ['title' => 'test record']);

        // with 2 record data, with 1 added the latest
        $latestTimestamp = Carbon::now()->timestamp;
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'timestamp' => $latestTimestamp
        ]);

        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'timestamp' => Carbon::now()->addDay(-1)->timestamp
        ]);

        // the modified date is equivalent to the latest timestamp
        $modifiedDate = DatesProvider::getModifiedAt($record);
        $this->assertEquals($modifiedDate->timestamp, $latestTimestamp);
    }

    /** @test */
    function a_record_with_2_data_created_date_is_the_earliest()
    {
        // given a record
        /** @var RegistryObject */
        $record = $this->stub(RegistryObject::class, ['title' => 'test record']);

        // with 2 record data, with 1 added the latest
        $latestTimestamp = Carbon::now()->timestamp;
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'timestamp' => $latestTimestamp
        ]);

        $earliestTimestamp = Carbon::now()->addDay(-1)->timestamp;
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'timestamp' => $earliestTimestamp
        ]);

        // the modified date is equivalent to the latest timestamp
        $modifiedDate = DatesProvider::getCreatedAt($record);
        $this->assertEquals($modifiedDate->timestamp, $earliestTimestamp);
    }

    /** @test */
    public function process_a_record_will_put_modified_and_created_at_the_right_spot()
    {
        // given a record
        /** @var RegistryObject */
        $record = $this->stub(RegistryObject::class, ['title' => 'test record']);

        // with 2 record data, with 1 added the latest
        $latestTimestamp = Carbon::now()->timestamp;
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'timestamp' => $latestTimestamp
        ]);

        $earliestTimestamp = Carbon::now()->addDay(-1)->timestamp;
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'timestamp' => $earliestTimestamp
        ]);

        DatesProvider::process($record);

        $record = $record->fresh();
        $this->assertNotNull($record->modified_at);
        $this->assertNotNull($record->created_at);

        $this->assertEquals($record->modified_at, Carbon::createFromTimestamp($latestTimestamp));
        $this->assertEquals($record->created_at, Carbon::createFromTimestamp($earliestTimestamp));
    }

    /** @test */
    function it_set_synced_at_when_touch_sync()
    {
        /** @var RegistryObject */
        $record = $this->stub(RegistryObject::class, ['title' => 'test record']);
        DatesProvider::touchSync($record);
        $this->assertNotNull($record->fresh()->synced_at);
    }

    /** @test */
    function it_set_synced_at_when_touch_delete()
    {
        /** @var RegistryObject */
        $record = $this->stub(RegistryObject::class, ['title' => 'test record']);
        DatesProvider::touchDelete($record);
        $this->assertNotNull($record->fresh()->deleted_at);
    }

    /** @test TODO stubs**/
//    public function it_should_get_the_correct_pub_date()
//    {
//        $key = "AUTestingRecords2ScholixRecords44";
//        $this->ensureKeyExist($key);
//        $record = RegistryObject::where('key', $key)->first();
//        $publicationDate = DatesProvider::getPublicationDate($record);
//        $this->assertEquals("2001-03-05", $publicationDate);
//    }

    /** @test TODO stubs**/
//    public function it_should_get_the_correct_publication_date()
//    {
//        $key = "AUTCollectionToTestSearchFields37";
//        $this->ensureKeyExist($key);
//        $record = RegistryObjectsRepository::getPublishedByKey($key);
//        $publicationDate = DatesProvider::getPublicationDate($record);
//        $this->assertEquals("2001-12-12", $publicationDate);
//    }

}