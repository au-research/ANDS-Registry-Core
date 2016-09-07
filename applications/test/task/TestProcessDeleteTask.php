<?php


namespace ANDS\Test;

use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class TestProcessDeleteTask
 * @package ANDS\Test
 */
class TestProcessDeleteTask extends UnitTest
{
    /** @test **/
    public function test_it_should_set_record_as_deleted()
    {
        // record should exist in PUBLISHED state at this stage
        $record = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertTrue($record);

        // record should now be deleted
        $result = RegistryObjectsRepository::deleteRecord($record->registry_object_id);
        $this->assertTrue($result);

        // check that the status of the record is DELETED, (not deleted from the DB)
        $record = RegistryObject::where('key', 'minh-test-record-pipeline')->first();
        $this->assertEquals("DELETED", $record->status);
    }

    public function setUp()
    {
        // insert record so it can be soft deleted
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecordsImport'
        ])->setCI($this->ci)->enableRunAllSubTask()->initialiseTask();
        $importTask->run();
    }

    public function tearDown()
    {
        RegistryObjectsRepository::completelyEraseRecord("minh-test-record-pipeline");
    }
}