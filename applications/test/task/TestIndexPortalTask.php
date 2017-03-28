<?php

namespace ANDS\Test;


use ANDS\API\Task\ImportSubTask\ImportSubTask;
use ANDS\API\Task\ImportTask;
use ANDS\DataSource;
use ANDS\Payload;
use ANDS\Repository\DataSourceRepository;
use ANDS\Util\XMLUtil;

/**
 * Class TestIndexPortalTask
 * @package ANDS\Test
 */
class TestIndexPortalTask extends UnitTest
{

    /** @test **/
    private $ds_id = 209;
    private $batch_id = "4F413B589394B33E746A2B4164D4008CB301054B";
    private $harvest_id = 131;
    private $pipeline = 'default';

    /** @test * */
    public function test_it_should_be()
    {
        $importTask = new ImportTask();

        //dd($importTask);
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id='. $this->ds_id .'&batch_id='.$this->batch_id.'&harvest_id='.$this->harvest_id.'&pipeline='.$this->pipeline
        ])->setCI($this->ci)->initialiseTask();
        $importTask->setTaskData('targetStatus','PUBLISHED');
        $importTask->setTaskData("importedRecords", [820191, 820192, 820193, 820194, 820176, 820195, 820196, 820197, 820198, 820199, 820200, 820201, 820202, 820203, 820204, 820205, 820206, 820207, 820208, 820209, 820210, 820189, 820211, 820212, 820213, 820178, 820214, 820187, 820188, 820215, 820216, 820217, 820218, 820219, 820220, 820221, 820222, 820223, 820224, 820225, 820226, 820186, 820227, 820228, 820984, 820987, 820990, 820993, 820996, 820999, 821002,821005, 821008, 821011, 821014,821017,821020, 821023]);

//        $importTask->setTaskData('importedRecords', [820200]);
        

        $task = $importTask->getTaskByName("IndexPortal");
        $task->run_task();

        dd($task->getMessage());

//        $task = $importTask->getTaskByName('IndexRelationship');
//        $task->run_task();
        return $task;
    }

}