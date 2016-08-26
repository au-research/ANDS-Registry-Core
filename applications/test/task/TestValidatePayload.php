<?php

namespace ANDS\Test;


use ANDS\API\Task\ImportSubTask\PopulateImportOptions;
use ANDS\API\Task\ImportSubTask\ValidatePayload;
use ANDS\API\Task\ImportTask;

class TestValidatePayload extends UnitTest
{
    /** @test * */
    public function test_it_should_be()
    {
        $task = $this->getImportTask();
        $this->assertEquals("ValidatePayload", $task->name);
        $this->assertEquals("PENDING", $task->status);
    }

    /** @test **/
    public function test_it_should_load_payload_to_parent_task()
    {
        $task = $this->getImportTask();
        $task->loadPayload();
        $payload = $task->parent()->getPayloads();
        $this->assertTrue(is_array($payload));
        $this->assertTrue(count($payload) > 0);
        $this->assertTrue(count(array_first($payload)) > 0);
    }


    /** @test **/
    public function test_it_should_validate_rifcs_xml()
    {
        $task = $this->getImportTask();
        $task->loadPayload()->run_task();
        //@todo check validated file generated
        //@todo check parent payload updated to be validated form
    }

    private function getImportTask()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'params' => 'ds_id=209&batch_id=593EB384AFFE59EAEB2CADE99E39454361C1C0AC'
        ])->loadParams()->loadSubTasks();
        $task = $importTask->getTaskByName("ValidatePayload");
        return $task;
    }

    public function setUp()
    {
        $this->ci->load->model('registry/data_source/data_sources', 'ds');
        require_once(API_APP_PATH.'vendor/autoload.php');
    }

}