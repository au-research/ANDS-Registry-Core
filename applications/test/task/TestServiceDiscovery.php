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
                'ds_id' => 50,
                'batch_id' => 'TESTBATCHID'
            ])
        ])->skipLoadingPayload()->initialiseTask();

        // all IMOS records
        $ids = RegistryObject::where('data_source_id', 50)
            ->where('class', 'collection')->pluck('registry_object_id');
        if (count($ids) == 0) {
            return;
        }

        $importTask
            ->setTaskData('importedRecords', $ids)
            ->setTaskData('imported_collection_ids', $ids);
        $task = $importTask->getTaskByName("ServiceDiscovery");
//        $task->run();
//
//        dd($task->getMessage());

        // TODO: test file generation
    }
}
