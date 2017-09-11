<?php

namespace ANDS\Test;

use ANDS\API\Task\ImportSubTask\ImportSubTask;
use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;


/**
 * Class TestServiceDiscovery
 * @package ANDS\Test
 */
class TestServiceDiscovery extends UnitTest
{
    public function test_it_should_do_things() {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => http_build_query([
                'ds_id' => 209,
                'batch_id' => 'DuplicateTest'
            ])
        ])->initialiseTask();

        // all NEII records
        $ids = RegistryObject::where('data_source_id', 12)->pluck('registry_object_id');
        if (count($ids) == 0) {
            return;
        }

        $importTask->setTaskData('importedRecords', $ids);
        $task = $importTask->getTaskByName("ServiceDiscovery");
        $task->run();
        dd($task->getMessage());
    }
}
