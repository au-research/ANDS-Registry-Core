<?php

namespace ANDS\API\Task\ImportSubTask;
use \ANDS\Repository\DataSourceRepository;
use ANDS\API\Task\ImportTask;

class ImportTaskTest extends \RegistryTestClass
{
    /** @not_test */
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