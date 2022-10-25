<?php

namespace ANDS\Test;

use ANDS\API\Task\ImportSubTask\ImportSubTask;
use ANDS\API\Task\ImportTask;
use ANDS\Repository\RegistryObjectsRepository;

class TestHandleStatusChange extends UnitTest
{
    /** @test **/
    public function test_it_should_clone_to_draft()
    {
        /**
         * if I have a published record
         * I run this task
         * it creates a new draft for me
         */
        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id=209&batch_id=AUTestingRecordsImport'
        ])->initialiseTask();
        $importTask->enableRunAllSubTask()->run();

        // now we have a PUBLISHED record
        $record = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertEquals('PUBLISHED', $record->status);

        // move the record to DRAFT state
        $importTask = new ImportTask();
        $importTask->init([
            'params' => 'ro_id='.$record->registry_object_id.'&targetStatus=DRAFT'
        ])->setPipeline('PublishingWorkflow');

        $importTask->enableRunAllSubTask()->run();

        /*
         * expect the record still exists with the same ID
         * and another record with the same key and in the DRAFT status
         */

        // DRAFT one
        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertTrue($record);
        $this->assertEquals('DRAFT', $record->status);

        $record = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertTrue($record);
        $this->assertEquals('PUBLISHED', $record->status);

        $this->assertTrue(true);
    }

    /** @test **/
    public function test_it_should_change_draft_status_correctly()
    {
        /**
         * if I have a draft
         * change the status to another draft status
         * then it just change the status
         */
        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id=209&batch_id=AUTestingRecordsImport&targetStatus=DRAFT'
        ])->initialiseTask();

        $importTask->enableRunAllSubTask()->run();

        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertTrue($record);

        $importTask = new ImportTask();
        $importTask->init([
            'params' => 'ro_id='.$record->registry_object_id.'&targetStatus=SUBMITTED_FOR_ASSESSMENT'
        ])->setPipeline('PublishingWorkflow');

        $importTask->initialiseTask()->enableRunAllSubTask();

        $this->assertInstanceOf(
            $importTask->getTaskByName("HandleStatusChange"), ImportSubTask::class
        );

        $importTask->run();

        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'SUBMITTED_FOR_ASSESSMENT');
        $this->assertTrue($record);
        $this->assertEquals('SUBMITTED_FOR_ASSESSMENT', $record->status);
    }

    /** @test **/
    public function test_it_should_publish_a_draft_record_when_no_publish_is_there()
    {
        /**
         * if I have a draft
         * change the status to another draft status
         * then it just change the status
         */
        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id=209&batch_id=AUTestingRecordsImport&targetStatus=DRAFT'
        ])->initialiseTask();
        $importTask->enableRunAllSubTask()->run();
        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertTrue($record);

        // handlestatuschange to publish
        $importTask = new ImportTask();
        $importTask->init([
            'params' => 'ro_id='.$record->registry_object_id.'&targetStatus=PUBLISHED'
        ])->setPipeline('PublishingWorkflow');
        $importTask->enableRunAllSubTask()->run();

        $record = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertEquals('PUBLISHED', $record->status);

        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertNull($record);

        $this->assertTrue(true);
    }

    /** @test **/
    public function test_it_should_publish_a_draft_to_replace_the_other_publish_record()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id=209&batch_id=AUTestingRecordsImport&targetStatus=DRAFT'
        ])->initialiseTask();
        $importTask->enableRunAllSubTask()->run();

        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id=209&batch_id=AUTestingRecordsImport&targetStatus=PUBLISHED'
        ])->initialiseTask();
        $importTask->enableRunAllSubTask()->run();

        $publishedRecord = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'PUBLISHED');
        $this->assertEquals('PUBLISHED', $publishedRecord->status);

        $draftRecord = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertEquals('DRAFT', $draftRecord->status);

        // handlestatuschange to publish
        $importTask = new ImportTask();
        $importTask->init([
            'params' => 'ro_id='.$draftRecord->registry_object_id.'&targetStatus=PUBLISHED'
        ])->setPipeline('PublishingWorkflow');
        $importTask->enableRunAllSubTask()->run();

        $publishedRecord = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertEquals('PUBLISHED', $publishedRecord->status);

        $draftRecord = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertNull($draftRecord);
    }

    public function tearDown()
    {
        RegistryObjectsRepository::completelyEraseRecord('minh-test-record-pipeline');
    }
}