<?php


namespace ANDS\Test;


use ANDS\API\Task\ImportSubTask\ImportSubTask;
use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class TestProcessCoreMetadataTask
 * @package ANDS\Test
 */
class TestProcessCoreMetadataTask extends UnitTest
{
    /** @test **/
    public function test_it_should_process_core_metadata()
    {
        $task = $this->getProcessCoreMetadataTask();
        $this->runPrerequisite($task->parent());
        $task->run();

        $record = RegistryObject::where('key', 'AUTestingRecords3h-dataset-31')->first();
        $this->assertEquals("collection", $record->class);
        $this->assertEquals("dataset", $record->type);
        $this->assertEquals("AUTestingRecords3", $record->group);
    }

    /** @test **/
    public function test_it_should_process_core_2()
    {
        $record = RegistryObject::where('key', 'http://research.unisa.edu.au/codddllection/96136')->first();
        $task = $this->getProcessCoreMetadataTask();
        $this->runPrerequisite($task->parent());
        $task->parent()->setTaskData("importedRecords", [$record->id]);
        $task->run();
        dd($task);
        $this->assertTrue(true);
    }

    /** @test **/
    public function test_it_should_populate_title_and_slug()
    {
        $task = $this->getProcessCoreMetadataTask();
        $this->runPrerequisite($task->parent());
        $task->run();

        $record = RegistryObject::where('key', 'AUTestingRecords3h-dataset-31')->first();
        $this->assertTrue(is_string($record->title) && !empty($record->title));
        $this->assertTrue(is_string($record->slug) && !empty($record->slug));
    }

    /**
     * Helper
     * Return a ProcessCoreMetadata Task for use each test
     * @return ImportSubTask
     */
    public function getProcessCoreMetadataTask()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecords3'
        ])->initialiseTask();
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $task = $importTask->getTaskByName("ProcessCoreMetadata");
        return $task;
    }

    /**
     * Helper
     * Run all prerequisite task before this process task
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
        $ingestTask = $importTask->getTaskByName("Ingest");
        $ingestTask->run();
    }

    public function setUpBeforeClass()
    {
        initEloquent();
    }

    public function tearDownAfterClass()
    {
        RegistryObjectsRepository::completelyEraseRecord('AUTestingRecords3h-dataset-31');
        RegistryObjectsRepository::completelyEraseRecord('AUTestingRecords3h-dataset-33');
        RegistryObjectsRepository::completelyEraseRecord('AUTestingRecords3h-dataset-36');
    }
}