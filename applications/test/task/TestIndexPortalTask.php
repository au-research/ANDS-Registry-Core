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
        $importTask->setTaskData("importedRecords", [582611,582572,582550,582640,582667,582420,582421,582422,582423,582424,582425,582947,582948,582949,582950,582951,582374,582376,582377,582382,582383,582384,582303,582305,582347,582348,582349,582426,582427,582428,582429,582430,582431,582432,582433,582434,582435,582436,582437,582450,582451,582452,582454,582459,582509,582313,582316,582325,582326,582327,582973,582974]);
        

        $task = $importTask->getTaskByName("IndexPortal");
        $task->run_task();

        $task = $importTask->getTaskByName('IndexRelationship');
        $task->run_task();
        return $task;
    }

}