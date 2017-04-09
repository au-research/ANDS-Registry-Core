<?php


namespace ANDS\Test;


use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;

class TestPopulateAffectedList extends UnitTest
{
    /** @test **/




    public function test_it_should_get_affected_list_of_big_datasource()
    {

        $importTask = new ImportTask();
        $importTask->init([
            'params' => "class=import&ds_id=211&batch_id=A1A690FE09158B1D8D2FC72E894D7B9984858D24&harvest_id=123&source=harvester"
            ]);
        $importTask->loadParams()->skipLoadingPayload()->initialiseTask();
        $ids = RegistryObject::where('data_source_id', 210)->where('status', 'PUBLISHED')->get()->pluck('registry_object_id')->toArray();
        $importTask->setTaskData('importedRecords', $ids);
        $importTask->setTaskData('imported_collection_identifiers', ["http://imosmest.aodn.org.au:80/geonetwork/srv/en/metadata.show?uuid=03bcf232-4b45-31bb-abeb-8fed01492914AUTbx1",
        "http://imosmest.aodn.org.au:80/geonetwork/srv/en/metadata.show?uuid=03bcf232-4b45-31bb-abeb-8fed01492914AUTbx1",
        "ala.org.au/dr968AUT2",
        "ala.org.au/dr931sdAUT2"]);

        $task = $importTask->getTaskByName("PopulateAffectedList");

        $task->run_task();

        var_dump($importTask->getTaskData("duplicateRecords"));
    }

//    public function test_it_should_get_affected_in_grants_network()
//    {
//        $ids = RegistryObject::where('data_source_id', 316)->where('status', 'PUBLISHED')->get()->pluck('registry_object_id')->toArray();
//
//        $importTask = new ImportTask();
//        $importTask->init([
//            'params' => http_build_query([
//                'ds_id' => 316,
//                'pipeline' => 'UpdateRelationshipWorkflow',
//                'targetStatus' => 'PUBLISHED',
//                'runAll' => true
//            ])
//        ])->skipLoadingPayload()->initialiseTask();
//        $importTask->setTaskData('importedRecords', $ids);
//        $importTask->run();
//
//        dd($importTask->getMessage());
//
//
//        $ids = [3];
//
//        $importTask = new ImportTask();
//        $importTask->init([
//            'params' => http_build_query([
//                'ds_id' => 16
//            ])
//        ])->skipLoadingPayload()->initialiseTask();
//
//        $importTask->setTaskData('importedRecords', $ids);
//
//        $task = $importTask->getTaskByName("PopulateAffectedList");
//
//        $task->run();
//
//        var_dump($importTask->getTaskData("affectedRecords"));
//    }
//
//    public function setUpBeforeClass()
//    {
//        initEloquent();
//    }
}