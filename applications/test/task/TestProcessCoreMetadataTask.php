<?php


namespace ANDS\Test;


use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;

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
    public function test_it_should_populate_title_and_slug()
    {
        $task = $this->getProcessCoreMetadataTask();
        $this->runPrerequisite($task->parent());
        $task->run();

        $record = RegistryObject::where('key', 'AUTestingRecords3h-dataset-31')->first();
        $this->assertTrue(is_string($record->title) && !empty($record->title));
        $this->assertTrue(is_string($record->slug) && !empty($record->slug));
    }

    public function getProcessCoreMetadataTask()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecords3'
        ])->setCI($this->ci)->initialiseTask();
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $task = $importTask->getTaskByName("ProcessCoreMetadata");
        return $task;
    }

    public function runPrerequisite($importTask)
    {
        $populateImportTask = $importTask->getTaskByName("PopulateImportOptions");
        $populateImportTask->run();
        $validateTask = $importTask->getTaskByName("ValidatePayload");
        $validateTask->run();
//        $processTask = $importTask->getTaskByName("ProcessPayload");
//        $processTask->run();
        $ingestTask = $importTask->getTaskByName("Ingest");
        $ingestTask->run();
    }

    public function setUp()
    {
        require_once(API_APP_PATH.'vendor/autoload.php');

        // TODO delete the core metadata from the database and set the records array to test
    }
}