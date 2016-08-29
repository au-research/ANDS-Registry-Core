<?php

namespace ANDS\Test;

use ANDS\API\Task\ImportSubTask\PopulateImportOptions;
use ANDS\API\Task\ImportSubTask\ValidatePayload;
use ANDS\API\Task\ImportTask;
use ANDS\Util\XMLUtil;


class TestProcessPayload extends UnitTest
{

    /** @test **/
    public function test_it_should_run()
    {
        $task = $this->getProcessTask();
        $this->runPrerequisite($task->parent());
        $task->run();
        // @todo check something
    }

    /** @test */
    public function test_it_should_run_on_the_third_batch()
    {
        $task = $this->getProcessTask();
        $task->parent()->setBatchID("AUTestingRecords3");
        $this->runPrerequisite($task->parent());
        $task->run();
        // @todo check something
    }

    /** @test */
    public function test_it_should_strip_duplicate()
    {
        $task = $this->getProcessTask();
        $task->parent()->setBatchID("DuplicateTest");
        $this->runPrerequisite($task->parent());
        $task->run();

        // there should be only 1 registryObject in this payload
        $payload = array_first($task->parent()->getPayloads());
        $this->assertEquals(
            1, XMLUtil::countElementsByName($payload, "registryObject")
        );
    }

    public function getProcessTask()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecords'
        ])->setCI($this->ci)->initialiseTask();
        $task = $importTask->getTaskByName("ProcessPayload");
        return $task;
    }

    public function runPrerequisite($importTask)
    {
        $populateImportTask = $importTask->getTaskByName("PopulateImportOptions");
        $populateImportTask->run();
        $validateTask = $importTask->getTaskByName("ValidatePayload");
        $validateTask->run();
    }

    public function setUp()
    {
        require_once(API_APP_PATH.'vendor/autoload.php');
    }
}