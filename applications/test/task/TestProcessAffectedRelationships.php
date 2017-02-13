<?php


namespace ANDS\Test;


use ANDS\API\Task\ImportTask;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class TestProcessAffectedRelationships
 * @package ANDS\Test
 */
class TestProcessAffectedRelationships extends UnitTest
{
//    /** @test **/
//    public function test_it_should_process_the_affected_records_correctly()
//    {
//        // php index.php test task TestProcessAffectedRelationships test_it_should_process_the_affected_records_correctly
//
//        $importTask = new ImportTask();
//        $importTask->init([
//            'ds_id' => 205,
//            'runAll' => 1
//        ])->skipLoadingPayload()->initialiseTask();
//
//        $publishedIDs = [582969];
//        initEloquent();
//        $ids = RegistryObject::where('data_source_id', 211)->where('status', 'PUBLISHED')->pluck('registry_object_id')->toArray();
//        $keys = RegistryObject::where('data_source_id', 211)->where('status', 'PUBLISHED')->pluck('key')->toArray();
//
//        $affectedIDs = RelationshipProvider::getAffectedIDsFromIDs($ids, $keys, true);
//
//        var_dump($affectedIDs);
//
//        foreach($keys as $key){
//            $record = RegistryObjectsRepository::getPublishedByKey($key);
//            RelationshipProvider::process($record);
//        }
//
//        $affectedIDs = RelationshipProvider::getAffectedIDsFromIDs($ids, $keys, true);
//        var_dump($affectedIDs);
//
//        $importTask->setTaskData('affectedRecords', $affectedIDs);
//
//        $task = $importTask->getTaskByName("ProcessAffectedRelationships");
//        $task->run();
//
//        $this->assertEquals($task->getStatus(), "COMPLETED");
//        $this->assertFalse($task->hasError());
//    }

    /** @test **/

    public function test_it_should_find_all_duplicate_records()
    {
        // php index.php test task TestProcessAffectedRelationships test_it_should_process_the_affected_records_correctly
        initEloquent();
        $importTask = new ImportTask();
        $importTask->init([
            'ds_id' => 209,
            'runAll' => 1
        ])->skipLoadingPayload()->initialiseTask();

        $ids = [582520];

        $record = RegistryObjectsRepository::getRecordByID(582520);
        $dups = $record->getDuplicateRecords();
        dd($dups);

        $keys = ["AUTestingRecords5parties12323488"];

        $affectedIDs = RelationshipProvider::getAffectedIDsFromIDs($ids, $keys, true);

        var_dump($affectedIDs);

        foreach($affectedIDs as $id ){
            $record = RegistryObjectsRepository::getRecordByID($id);
            $record->findAllDuplicates();
            var_dump($record->title);
        }

    }
}