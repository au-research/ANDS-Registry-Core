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
    }
}