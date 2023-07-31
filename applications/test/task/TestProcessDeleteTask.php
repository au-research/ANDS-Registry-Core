<?php


namespace ANDS\Test;

use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class TestProcessDeleteTask
 * @package ANDS\Test
 */
class TestProcessDeleteTask extends UnitTest
{
    /** @test **/
    public function test_it_should_set_record_as_deleted()
    {
        // insert record so it can be soft deleted to the published status
        $this->importRecord();

        // record should exist in PUBLISHED state at this stage
        $record = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertTrue($record);

        // record should now be deleted
        $result = RegistryObjectsRepository::deleteRecord($record->registry_object_id);
        $this->assertTrue($result);

        // check that the status of the record is DELETED, (not deleted from the DB)
        $record = RegistryObject::where('key', 'minh-test-record-pipeline')->first();
        $this->assertEquals("DELETED", $record->status);
    }

    /** @test **/
    public function test_it_should_soft_delete_a_record_in_pipeline()
    {
        // insert record so it can be soft deleted to the published status
        $this->importRecord();

        // record should exist in PUBLISHED state at this stage
        $record = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertTrue($record);

        // schedule a processDelete task and run it
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecordsImport'
        ])->initialiseTask();
        $importTask->setTaskData('deletedRecords', [$record->registry_object_id]);
        $deleteTask = $importTask->getTaskByName("ProcessDelete");
        $deleteTask->run();

        // record in PUBLISHED state should be gone completely
        $record = RegistryObjectsRepository::getPublishedByKey('minh-test-record-pipeline');
        $this->assertNull($record);
    }

    /** @test **/
    public function test_it_should_delete_a_draft_completely()
    {
        // insert record so it can be deleted completedly to the draft status
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecordsImport&targetStatus=DRAFT'
        ])->enableRunAllSubTask()->initialiseTask();
        $importTask->run();

        // record should exist in PUBLISHED state at this stage
        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertTrue($record);

        // schedule a processDelete task and run it
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecordsImport'
        ])->initialiseTask();
        $importTask->setTaskData('deletedRecords', [$record->registry_object_id]);
        $deleteTask = $importTask->getTaskByName("ProcessDelete");
        $deleteTask->run();

        // record in draft state should be gone completely
        $record = RegistryObjectsRepository::getByKeyAndStatus('minh-test-record-pipeline', 'DRAFT');
        $this->assertNull($record);
    }

    /** @test **/
    public function test_it_should_delete_and_clean_index()
    {
        // given I have a set of records with links
        $this->ci->config->set_item('harvested_contents_path', TEST_APP_PATH . 'core/data/');
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'Has 10 records',
            'params' => http_build_query([
                'runAll' => true,
                'ds_id' => 209,
                'batch_id' => 'AUTestingRecords_ds209_10_different_records'
            ])
        ])->initialiseTask();

        $importTask->run();

        // should have 10 records afterwards
        $publishedRecords = RegistryObject::where('data_source_id', 209)->where('status', 'PUBLISHED');
        $this->assertEquals(10, $publishedRecords->count());

        // when deleting a set of records
        $recordsToDelete = [
            RegistryObjectsRepository::getPublishedByKey('AUTestingRecordsNestedReverseIMOS/71bd7e16-7333-3e74-aaeb-3039b283ff14')->registry_object_id,
            RegistryObjectsRepository::getPublishedByKey('AUTestingRecordsjcu.edu.au/collection/enmasse/304')->registry_object_id
        ];

        // Find affected records
        $importTask = new ImportTask();
        $importTask->initialiseTask();
        $processDeleteTask = $importTask->getTaskByName("ProcessDelete");
        $affected = $processDeleteTask->getRelatedObjectsIDs($recordsToDelete);

        // should have at least 3 affected
        $this->assertGreaterThanOrEqual(count($affected), 3);

        // all affected records should have their portal index updated
        $deleteTask = new ImportTask;
        $deleteTask->init([
            'params' => http_build_query([
                'ds_id' => '209',
                'runAll' => true,
                'pipeline' => 'PublishingWorkflow'
            ])
        ])->setTaskData('deletedRecords', $recordsToDelete)
            ->skipLoadingPayload()

            ->initialiseTask();
        $deleteTask->run();

        // there must not be any related_collection_id to the deletedRecords
        $this->ci->load->library('solr');
        $result = $this->ci->solr->init('portal')
            ->setOpt('q', "related_collection_id:$recordsToDelete[0] related_collection_id:$recordsToDelete[1]")
            ->executeSearch(true);

        $this->assertEquals(0, $result['response']['numFound']);

        // delete all
        $publishedRecords = RegistryObject::where('data_source_id', 209)->where('status', 'PUBLISHED')->get()->pluck('registry_object_id');
        $deleteTask = new ImportTask;
        $deleteTask->init([
            'params' => http_build_query([
                'ds_id' => '209',
                'runAll' => true,
                'pipeline' => 'PublishingWorkflow'
            ])
        ])->setTaskData('deletedRecords', $publishedRecords)
            ->skipLoadingPayload()

            ->initialiseTask();

        $deleteTask->run();
    }

    /**
     * Helper
     * insert a record so it can be deleted
     */
    private function importRecord()
    {
        // insert the record in pipeline
        $importTask = new ImportTask();
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id=209&batch_id=AUTestingRecordsImport'
        ])->enableRunAllSubTask()->initialiseTask();
        $importTask->run();
    }

    public function tearDown()
    {
        // make sure that this record is gone forever
        RegistryObjectsRepository::completelyEraseRecord("minh-test-record-pipeline");
    }

    public function tearDownAfterClass()
    {
        $files = [];
        foreach (glob(TEST_APP_PATH. "core/data/209/*.processed") as $filename) {
            $files[] = $filename;
        }
        foreach (glob(TEST_APP_PATH. "core/data/209/*.validated") as $filename) {
            $files[] = $filename;
        }
        foreach (glob(TEST_APP_PATH. "core/data/209/MANUAL*") as $filename) {
            $files[] = $filename;
        }
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}