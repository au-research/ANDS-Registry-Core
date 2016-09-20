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
        // insert record so it can be soft deleted to the published status
        $this->importRecord();

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

    /** @test **/
    public function test_it_should_soft_delete_a_record_in_pipeline()
    {
        // insert record so it can be soft deleted to the published status
        $this->importRecord();

        // record should exist in PUBLISHED state at this stage
        $record = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertTrue($record);

        // schedule a processDelete task and run it
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecordsImport'
        ])->setCI($this->ci)->initialiseTask();
        $importTask->setTaskData('deletedRecords', [$record->registry_object_id]);
        $deleteTask = $importTask->getTaskByName("ProcessDelete");
        $deleteTask->run();

        // record in PUBLISHED state should be gone completely
        $record = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertNull($record);
    }

    /** @test **/
    public function test_it_should_delete_a_draft_completely()
    {
        // insert record so it can be deleted completedly to the draft status
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecordsImport&targetStatus=DRAFT'
        ])->setCI($this->ci)->enableRunAllSubTask()->initialiseTask();
        $importTask->run();

        // record should exist in PUBLISHED state at this stage
        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertTrue($record);

        // schedule a processDelete task and run it
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecordsImport'
        ])->setCI($this->ci)->initialiseTask();
        $importTask->setTaskData('deletedRecords', [$record->registry_object_id]);
        $deleteTask = $importTask->getTaskByName("ProcessDelete");
        $deleteTask->run();

        // record in draft state should be gone completely
        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertNull($record);
    }

    /**
     * Helper
     * insert a record so it can be deleted
     */
    private function importRecord()
    {
        // insert the record in pipeline
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecordsImport'
        ])->setCI($this->ci)->enableRunAllSubTask()->initialiseTask();
        $importTask->run();
    }

    public function tearDown()
    {
        // make sure that this record is gone forever
        RegistryObjectsRepository::completelyEraseRecord("minh-test-record-pipeline");
    }
}