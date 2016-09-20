<?php


namespace ANDS\Test;

use ANDS\API\Task\ImportSubTask\ImportSubTask;
use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;


/**
 * Class TestIngestTask
 * @package ANDS\Test
 */
class TestIngestTask extends UnitTest
{

    /** @test **/
    public function test_it_should_ingest_the_one_record()
    {
        $task = $this->getIngestTask();
        $this->runPrerequisite($task->parent());
        $task->run();

        $record1 = RegistryObject::where('key', 'AUTestingRecords3h-dataset-31')->first();
        $this->assertInstanceOf($record1, RegistryObject::class);

        // TODO: check more stuffs
    }

    /** @test **/
    public function test_it_should_create_a_new_draft_if_published_record_existed()
    {
        // if we have a published record
        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id=209&batch_id=AUTestingRecordsImport'
        ])->setCI($this->ci)->initialiseTask();
        $importTask->enableRunAllSubTask()->run();

        // now we have a PUBLISHED record
        $record = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertEquals('PUBLISHED', $record->status);

        // we import the exact same record with draft status
        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id=209&batch_id=AUTestingRecordsImport&targetStatus=DRAFT'
        ])->setCI($this->ci)->initialiseTask();
        $importTask->enableRunAllSubTask()->run();

        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertTrue($record);
        $this->assertEquals('DRAFT', $record->status);

        $record = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertTrue($record);
        $this->assertEquals('PUBLISHED', $record->status);
    }

    /** @test **/
    public function test_it_should_create_a_publish_record_when_a_draft_already_exists()
    {
        // if we have a DRAFT record
        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id=209&batch_id=AUTestingRecordsImport&targetStatus=DRAFT'
        ])->setCI($this->ci)->initialiseTask();
        $importTask->enableRunAllSubTask()->run();

        // now we have a DRAFT record
        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertEquals('DRAFT', $record->status);

        // import in a new published record
        $importTask = new ImportTask();
        $importTask->init([
            'params'=>'ds_id=209&batch_id=AUTestingRecordsImport&targetStatus=PUBLISHED'
        ])->setCI($this->ci)->initialiseTask();
        $importTask->enableRunAllSubTask()->run();


        // now we have a DRAFT record
        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertEquals('DRAFT', $record->status);

        // now we have a DRAFT record
        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'PUBLISHED');
        $this->assertEquals('PUBLISHED', $record->status);
    }

    /**
     * Helper
     * Returns an IngestTask for use each test
     * @return ImportSubTask
     */
    public function getIngestTask()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecords3'
        ])->setCI($this->ci)->initialiseTask();
        $task = $importTask->getTaskByName("Ingest");
        return $task;
    }

    /**
     * Helper
     * Run all prerequisite task before Ingest
     *
     * @param $importTask
     */
    public function runPrerequisite($importTask)
    {
        $populateImportTask = $importTask->getTaskByName("PopulateImportOptions");
        $populateImportTask->run();
        $validateTask = $importTask->getTaskByName("ValidatePayload");
        $validateTask->run();
        $processTask = $importTask->getTaskByName("ProcessPayload");
        $processTask->run();
    }

    public function tearDown()
    {
        RegistryObjectsRepository::completelyEraseRecord('AUTestingRecords3h-dataset-31');
        RegistryObjectsRepository::completelyEraseRecord('AUTestingRecords3h-dataset-33');
        RegistryObjectsRepository::completelyEraseRecord('AUTestingRecords3h-dataset-36');
//        RegistryObjectsRepository::completelyEraseRecord('minh-test-record-pipeline');
    }
}