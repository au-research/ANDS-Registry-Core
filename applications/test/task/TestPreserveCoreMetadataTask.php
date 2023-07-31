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
class TestPreserveCoreMetadataTask extends UnitTest
{
    /** @test **/

    private $publishedID = 567974;
    private $draftID = 569801;

    public function test_it_should_copy_harvest_id()
    {
        $task = $this->getPreserveCoreMetadataTask();

        $publishedRecord = RegistryObject::find($this->publishedID);
        $draftRecord = RegistryObject::find($this->draftID);
        
        $draftHarvestID = $draftRecord->getRegistryObjectAttributeValue("harvest_id");

        $task->parent()->setTaskData("importedRecords",  [$this->publishedID]);

        // $this->assertNotEquals($draftRecord->getRegistryObjectAttributeValue("harvest_id"), $oldHarvestID);

        $task->run();

        $publishedRecord = RegistryObject::find($this->publishedID);
        $newHarvestID = $publishedRecord->getRegistryObjectAttributeValue("harvest_id");

        $this->assertEquals($draftHarvestID, $newHarvestID);
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
    public function getPreserveCoreMetadataTask()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecords3'
        ])->setPipeline('PublishingWorkflow')->initialiseTask();
        $task = $importTask->getTaskByName("PreserveCoreMetadata");
        $task->parent()->setTaskData("targetStatus", "PUBLISHED");
        return $task;
    }


}