<?php


namespace ANDS\Test;


use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;

class TestPopulateAffectedList extends UnitTest
{
    /** @test **/
    public function test_it_should_get_affected_list_of_big_datasource()
    {
        $ids = RegistryObject::where('data_source_id', 16)->where('status', 'PUBLISHED')->get()->pluck('registry_object_id')->toArray();


        $importTask = new ImportTask();
        $importTask->init([
            'params' => http_build_query([
                'ds_id' => 16
            ])
        ])->skipLoadingPayload()->initialiseTask();

        foreach (['party', 'activity', 'collection', 'activity'] as $class) {
            $records = RegistryObject::where('data_source_id', 16)->where('status', 'PUBLISHED')->where('class', $class)->get();
            $importTask->setTaskData("imported_".$class."_ids", $records->pluck('registry_object_id')->toArray());
            $importTask->setTaskData("imported_".$class."_keys", $records->pluck('key')->toArray());
        }

        $importTask->setTaskData('importedRecords', $ids);

        $task = $importTask->getTaskByName("PopulateAffectedList");

        $task->run();

        var_dump($task->getTaskData("benchmark"));
    }

    public function setUpBeforeClass()
    {
        initEloquent();
    }
}