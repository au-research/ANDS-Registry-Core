<?php

namespace ANDS\Test;


use ANDS\API\Task\ImportTask;
use ANDS\Repository\DataSourceRepository;

/**
 * Class TestImportTask
 * @package ANDS\Test
 */
class TestImportTask extends UnitTest
{
    /** @test * */
    public function test_it_should_generate_a_subtask()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'params' => 'ds_id=2&batch_id=1234d'
        ]);
        $importTask->loadParams();
        $this->assertEquals($importTask->dataSourceID, "2");
        $this->assertEquals($importTask->batchID, "1234d");
    }

    /** @test * */
    public function test_it_should_accepts_payload()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'params' => 'ds_id=2&batch_id=1234d'
        ]);
        $importTask->setPayload("key", "xmlcontent");
        $this->assertEquals("xmlcontent", $importTask->getPayload("key"));
    }

    /** @test * */
    public function test_it_should_load_default_subtasks_correctly()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'params' => 'ds_id=2&batch_id=1234d'
        ]);
        $importTask->loadSubTasks();
        $defaultSubTasks = $importTask->getDefaultImportSubtasks();
        $this->assertEquals(
            count($defaultSubTasks), count($importTask->getSubtasks())
        );
    }

    /** @test * */
    public function test_it_should_load_existing_subtasks_correctly()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'params' => 'ds_id=2&batch_id=1234d'
        ]);
        $sampleTaskState = [
            ["name" => "PopulateImportOptions", "status" => "COMPLETED"],
            ["name" => "ValidatePayload", "status" => "PENDING"],
            ["name" => "ProcessPayload", "status" => "PENDING"]
        ];
        $importTask->setTaskData('subtasks', $sampleTaskState);
        $importTask->loadSubTasks();

        $task = $importTask->getNextTask();
        $this->assertEquals($task->name, "ValidatePayload");
    }

    /** @test * */
    public function test_it_should_get_next_subtask_reliably()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'params' => 'ds_id=2&batch_id=593EB384AFFE59EAEB2CADE99E39454361C1C0AC'
        ]);
        $sampleTaskState = [
            ["name" => "PopulateImportOptions", "status" => "COMPLETED"],
            ["name" => "ValidatePayload", "status" => "COMPLETED"],
            ["name" => "ProcessPayload", "status" => "COMPLETED"]
        ];
        $importTask->setTaskData('subtasks', $sampleTaskState);
        $importTask->loadSubTasks();
        $task = $importTask->getNextTask();
        $this->assertNull($task);
    }

    /** @test * */
    public function test_it_should_construct_test_object()
    {
        $importTask = new ImportTask();
        $importTask
            ->init([
                'params' => 'ds_id=2&batch_id=593EB384AFFE59EAEB2CADE99E39454361C1C0AC'
            ])
            ->initialiseTask();
        $task = $importTask->constructTaskObject($importTask->getNextTask()->toArray());
        $this->assertEquals($importTask, $task->getParentTask());
    }

    /** @test * */
    public function test_it_should_run_the_first_task_found()
    {
        $dataSource = DataSourceRepository::getByKey('AUTestingRecords');
        $importTask = new ImportTask();
        $importTask
            ->init([
                'params' => 'ds_id=' . $dataSource->data_source_id . '&batch_id=1234d'
            ])
            ->initialiseTask()
            ->run();
        $taskArray = $importTask->toArray();
        $this->assertEquals("PUBLISHED",
            $taskArray["data"]["dataSourceDefaultStatus"]);
    }

    /** @test * */
    public function test_it_should_run_all_tasks_when_specified()
    {
        $dataSource = DataSourceRepository::getByKey('AUTestingRecords');
        $importTask = new ImportTask();
        $importTask
            ->init([
                'params' => 'ds_id=' . $dataSource->data_source_id . '&batch_id=1234d'
            ])
            ->enableRunAllSubTask()
            ->initialiseTask();

        $importTask->run();
        $taskArray = $importTask->toArray();
        foreach ($taskArray['subtasks'] as $subtask) {
            $this->assertEquals("COMPLETED", $subtask['status']);
        }
    }

    public function setUp()
    {
        $importTask = new ImportTask();
        $importTask->bootEloquentModels();
    }
}