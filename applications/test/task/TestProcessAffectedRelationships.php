<?php


namespace ANDS\Test;


use ANDS\API\Task\ImportTask;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository as Repo;

/**
 * Class TestProcessAffectedRelationships
 * @package ANDS\Test
 */
class TestProcessAffectedRelationships extends UnitTest
{
    /** @test **/
    public function test_it_should_process_the_affected_records_correctly()
    {
        // php index.php test task TestProcessAffectedRelationships test_it_should_process_the_affected_records_correctly

        $importTask = new ImportTask();
        $importTask->init([
            'ds_id' => 205,
            'runAll' => 1
        ])->skipLoadingPayload()->initialiseTask();

        $publishedIDs = [574584];
        //$publishedIDs = RegistryObject::where('data_source_id', 213)->where('status', 'PUBLISHED')->get()->pluck('registry_object_id')->toArray();
        $affectedIDs = RelationshipProvider::getAffectedIDsFromIDs($publishedIDs);
        foreach($affectedIDs as $id) {
            $record = Repo::getRecordByID($id);
            var_dump((string)$record->class.":".(string)$record->title);
        }

        $importTask->setTaskData('affectedRecords', $affectedIDs);

        $task = $importTask->getTaskByName("ProcessAffectedRelationships");
        $task->run();

        $this->assertEquals($task->getStatus(), "COMPLETED");
        $this->assertFalse($task->hasError());
    }
}