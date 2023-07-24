<?php

namespace ANDS\Test;

use ANDS\API\Task\ImportSubTask\ImportSubTask;
use ANDS\API\Task\ImportTask;
use ANDS\Util\XMLUtil;


/**
 * Class TestProcessPayload
 * @package ANDS\Test
 */
class TestProcessPayload extends UnitTest
{

    /** @test */
    public function test_it_should_strip_duplicate()
    {
        $this->ci->config->set_item('harvested_contents_path', TEST_APP_PATH . 'core/data/');
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => http_build_query([
                'ds_id' => 209,
                'batch_id' => 'DuplicateTest'
            ])
        ])->initialiseTask();
        $task = $importTask->getTaskByName("ProcessPayload");

        // TODO: refactor runPrerequisite?
        $this->runPrerequisite($importTask);
        $task->run();

        // there should be only 1 registryObject in this entire payload
        $payload = array_first($task->parent()->getPayloads());
        $processedXML = $payload->getContentByStatus('processed');
        $this->assertEquals(
            1, XMLUtil::countElementsByName($processedXML, "registryObject")
        );

        //clean up
        unlink(TEST_APP_PATH . 'core/data/209/DuplicateTest/1.xml.validated');
        unlink(TEST_APP_PATH . 'core/data/209/DuplicateTest/1.xml.processed');
        unlink(TEST_APP_PATH . 'core/data/209/DuplicateTest/2.xml.validated');
        unlink(TEST_APP_PATH . 'core/data/209/DuplicateTest/2.xml.processed');
        unlink(TEST_APP_PATH . 'core/data/209/DuplicateTest/3.xml.validated');
        unlink(TEST_APP_PATH . 'core/data/209/DuplicateTest/3.xml.processed');
    }

    // TODO: test checkPayloadHarvestability

    /**
     * Helper
     * Return a process task for each test
     *
     * @return ImportSubTask
     */
    public function getProcessTask()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecords'
        ])->initialiseTask();
        $task = $importTask->getTaskByName("ProcessPayload");
        return $task;
    }

    /**
     * Helper
     * Run all prerequisite task before this task can be run
     *
     * @param $importTask
     */
    public function runPrerequisite($importTask)
    {
        $populateImportTask = $importTask->getTaskByName("PopulateImportOptions");
        $populateImportTask->run();
        $importTask->saveSubTaskData($populateImportTask);
        $validateTask = $importTask->getTaskByName("ValidatePayload");
        $validateTask->run();
        $importTask->saveSubTaskData($validateTask);
        $importTask->saveSubTasks();
    }

}