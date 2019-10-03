<?php

namespace ANDS\API\Task\ImportSubTask;
use \ANDS\Repository\DataSourceRepository;
use ANDS\API\Task\ImportTask;

class ImportTaskTest extends \RegistryTestClass
{

    /** @test */
    function test_for_the_sake_of_test()
    {
        $batchID = 'FBEA060C6FABCAD76FB3E10286E49543A845B008';
        $this->assertEquals('FBEA060C6FABCAD76FB3E10286E49543A845B008',$batchID);

    }


    /** @not_test but we should start migration the old CI tasks tests here*/
    function not_test_native_pipeline()
    {
        $batchID = 'FBEA060C6FABCAD76FB3E10286E49543A845B008';
        $dataSourceID = 42088;

        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id='.$dataSourceID.'&batch_id='.$batchID
        ])->initialiseTask();
        $task = $importTask->getTaskByName("IngestNativeSchema");
        $task->run();

    }

}