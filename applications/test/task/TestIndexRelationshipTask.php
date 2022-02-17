<?php


namespace ANDS\Test;


use ANDS\API\Task\ImportTask;
use ANDS\API\Task\TaskManager;
use ANDS\DataSource;
use ANDS\Payload;
use ANDS\Registry\Importer;
use ANDS\Registry\Providers\GrantsConnectionsProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Registry\PreMyceliumConnections;

/**
 * Class TestIndexRelationshipTask
 * @package ANDS\Test
 */
class TestIndexRelationshipTask extends UnitTest
{

    /** @test **/
    public function test_it_should_regenerate_slug()
    {
        // this is actually a script
        // placeholder until a good place for script to be
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $ids = RegistryObject::where('slug', "")->pluck('registry_object_id')->toArray();
        foreach ($ids as $id) {
            $ro = $this->ci->ro->getByID($id);
            $ro->generateSlug();
            var_dump($ro->slug);
        }
    }

    /** @test **/
    public function test_it_should_sample()
    {

        $record = RegistryObject::find(751824);
        $record = RegistryObject::find(798176);
        $record = RegistryObject::find(798175);
        $record = RegistryObject::find(798069);
        $record = RegistryObject::find(798146);
        $record = RegistryObject::find(798044);
        $record = RegistryObject::find(798243);
        $record = RegistryObject::find(798257);
        $record = RegistryObject::find(798251);
        $record = RegistryObject::find(798220);
        $record = RegistryObject::find(798283);
        $record = RegistryObject::find(809032);
        $record = RegistryObject::find(840467);
        $record = RegistryObject::find(842576);
        $record = RegistryObject::find(842576);
        $record = RegistryObject::find(1164);

        $record = RegistryObject::find(1164);
        $record = RegistryObject::find(902430);
        $record = RegistryObject::find(902447);


        $task = new ImportTask;
        $task->init([
            'params' => http_build_query([
                'ds_id' => $record->data_source_id,
                'targetStatus' => 'PUBLISHED',
                'runAll' => 1
            ])
        ])->skipLoadingPayload()->initialiseTask();

        $task->setTaskData('importedRecords', [$record->registry_object_id]);

        $indexPortalTask = $task->getTaskByName("IndexPortal");
        $indexPortalTask->run();

        $indexRelationshipTask = $task->getTaskByName("IndexRelationship");
        $indexRelationshipTask->run();

        dd($indexRelationshipTask->toArray());

        $relationships = RelationshipProvider::getMergedRelationships($record);
        dd($indexRelationshipTask->getRelationshipIndex($relationships));

        dd($indexRelationshipTask->getMessage());

//        $relationships = RelationshipProvider::getMergedRelationships($record);
//        dd($indexRelationshipTask->getRelationshipIndex($relationships));
//        dd(RelationshipProvider::getIdentifierRelationship($record));
//        dd(RelationshipProvider::getAffectedIDsFromIDs([798088]));
    }

    /** @test */
    public function test_sample_test()
    {
        $obj = TaskManager::create($this->ci->db, $this->ci)->getTask(52185);
        $task = TaskManager::create($this->ci->db, $this->ci)->getTaskObject($obj);
        $task->setDb($this->ci->db)->setCI($this->ci);
        $task->initialiseTask();

        $ids = RelationshipProvider::getAffectedIDsFromIDs($task->getTaskData("importedRecords"), $keys = RegistryObject::whereIn('registry_object_id', $task->getTaskData("importedRecords"))
            ->get()->pluck('key')->toArray());

        $indexRelationshipTask = $task->getTaskByName("IndexRelationship");
        $indexRelationshipTask->run();
        dd($indexRelationshipTask->getMessage());
    }

    /** @test **/
    public function test_it_should_import_clean_grants_network()
    {
        // php index.php test task TestIndexRelationshipTask test_it_should_import_clean_grants_network

        $dataSource = DataSourceRepository::getByKey("AUTEST1");

        // delete all published records
        Importer::instantDeleteRecords($dataSource, ['status' => 'PUBLISHED']);

        // import first part
        Importer::instantImportRecord(
            $dataSource, new Payload(TEST_APP_PATH."core/data/clean_grants_test_records.xml")
        );

        // funder of a1 is f1
        $a1 = RegistryObjectsRepository::getPublishedByKey("GrantsTestActivity1_key");
        $a1funder = GrantsConnectionsProvider::create()->getFunder($a1);
        $this->assertEquals($a1funder->key, "GrantsTestFunder1_key");

        // funder of a2 is f1
        $a2 = RegistryObjectsRepository::getPublishedByKey("GrantsTestActivity1_key");
        $a2funder = GrantsConnectionsProvider::create()->getFunder($a2);
        $this->assertEquals($a2funder->key, "GrantsTestFunder1_key");

        // funder of c3 is f1
        $c3 = RegistryObjectsRepository::getPublishedByKey("GrantsTestCollection3_key");
        $c3funder = GrantsConnectionsProvider::create()->getFunder($c3);
        $this->assertEquals($c3funder->key, "GrantsTestFunder1_key");

        // c1 producedBy a1 and a2
        $c1 = RegistryObjectsRepository::getPublishedByKey("GrantsTestCollection1_key");
        $c1parentActivities = GrantsConnectionsProvider::create()->getParentsActivities($c1);
        $keys = collect($c1parentActivities)->pluck('key')->toArray();
        $this->assertContains("GrantsTestActivity2_key", $keys);
        $this->assertContains("GrantsTestActivity1_key", $keys);

        // c2 has c1 as parent
        $c2 = RegistryObjectsRepository::getPublishedByKey("GrantsTestCollection2_key");
        $c2parentCollections = GrantsConnectionsProvider::create()->getParentsCollections($c2);
        $keys = collect($c2parentCollections)->pluck('key')->toArray();
        $this->assertContains("GrantsTestCollection1_key", $keys);

        // c3 has c1 as parent
        $c3 = RegistryObjectsRepository::getPublishedByKey("GrantsTestCollection3_key");
        $c3parentCollections = GrantsConnectionsProvider::create()->getParentsCollections($c3);
        $keys = collect($c3parentCollections)->pluck('key')->toArray();
        $this->assertContains("GrantsTestCollection1_key", $keys);

        // c2 has a1 and a2 as parent activities
        $c2 = RegistryObjectsRepository::getPublishedByKey("GrantsTestCollection2_key");
        $c2parentActivities = GrantsConnectionsProvider::create()->getParentsActivities($c2);
        $keys = collect($c2parentActivities)->pluck('key')->toArray();
        $this->assertContains("GrantsTestActivity2_key", $keys);
        $this->assertContains("GrantsTestActivity1_key", $keys);

        // c3 has a1 and a2 as parent activities
        $c3 = RegistryObjectsRepository::getPublishedByKey("GrantsTestCollection3_key");
        $c3parentActivities = GrantsConnectionsProvider::create()->getParentsActivities($c3);
        $keys = collect($c3parentActivities)->pluck('key')->toArray();
        $this->assertContains("GrantsTestActivity2_key", $keys);
        $this->assertContains("GrantsTestActivity1_key", $keys);

        // a4 has f1 as funder
        $a4 = RegistryObjectsRepository::getPublishedByKey("GrantsTestActivity4_key");
        $a4funder = GrantsConnectionsProvider::create()->getFunder($a4);
        $this->assertEquals($a4funder->key, "GrantsTestFunder1_key");

        // import the second part
        Importer::instantImportRecord(
            $dataSource, new Payload(TEST_APP_PATH."core/data/clean_grants_test_records_part2.xml")
        );

        // c7 does not have a funder
        $c7 = RegistryObjectsRepository::getPublishedByKey("GrantsTestCollection7_key");
        $c7funder = GrantsConnectionsProvider::create()->getFunder($c7);
        $this->assertNull($c7funder);

        // c4 does not have a funder
        $c4 = RegistryObjectsRepository::getPublishedByKey("GrantsTestCollection4_key");
        $c4funder = GrantsConnectionsProvider::create()->getFunder($c4);
        $this->assertNull($c4funder);

        // c1 has c4 as collection parent
        $c1 = RegistryObjectsRepository::getPublishedByKey("GrantsTestCollection1_key");
        $c1parentCollections = GrantsConnectionsProvider::create()->getParentsCollections($c1);
        $keys = collect($c1parentCollections)->pluck('key')->toArray();
        $this->assertContains("GrantsTestCollection4_key", $keys);

        // c3 has c4 as collection parent
        $c3 = RegistryObjectsRepository::getPublishedByKey("GrantsTestCollection3_key");
        $c3parentCollections = GrantsConnectionsProvider::create()->getParentsCollections($c3);
        $keys = collect($c3parentCollections)->pluck('key')->toArray();
        $this->assertContains("GrantsTestCollection4_key", $keys);

        // c1 has c6 has collection parent
        $c1 = RegistryObjectsRepository::getPublishedByKey("GrantsTestCollection1_key");
        $c1parentCollections = GrantsConnectionsProvider::create()->getParentsCollections($c1);
        $keys = collect($c1parentCollections)->pluck('key')->toArray();
        $this->assertContains("GrantsTestCollection6_key", $keys);

        // c5 has funder f1
        $c5 = RegistryObjectsRepository::getPublishedByKey("GrantsTestCollection5_key");
        $c5funder = GrantsConnectionsProvider::create()->getFunder($c5);
        $this->assertEquals($c5funder->key, "GrantsTestFunder1_key");

        // import part 3
        Importer::instantImportRecord(
            $dataSource, new Payload(TEST_APP_PATH."core/data/clean_grants_test_records_part3.xml")
        );

        // a5 is the same as a4, so it has a funder
        $a5 = RegistryObjectsRepository::getPublishedByKey("GrantsTestActivity5_key");
        $a5funder = GrantsConnectionsProvider::create()->getFunder($a5);
        $this->assertEquals($a5funder->key, "GrantsTestFunder1_key");

    }

    /** @test **/
    public function test_it_should_contain_the_needed_relationship_index()
    {
        // $this->importRecords("clean_grants_test_records.xml");
        $dataSource = DataSourceRepository::getByKey("AUTEST1");
        $importTask = new ImportTask;
        $importTask->init([
            'params' => http_build_query([
                'ds_id' => $dataSource->data_source_id,
                'targetStatus' => 'PUBLISHED'
            ])
        ])->skipLoadingPayload()->enableRunAllSubTask()->initialiseTask();
        $importTask->setTaskData("importedRecords", [574582]);

        $processRelationshipTask = $importTask->getTaskByName("ProcessRelationships");
        $processRelationshipTask->run();
        var_dump($processRelationshipTask->getMessage());
//        var_dump($processRelationshipTask->getTaskData('benchmark'));

        $indexPortalTask = $importTask->getTaskByName("IndexPortal");
        $indexPortalTask->run();
//        var_dump($indexPortalTask->getTaskData('benchmark'));

        $indexRelationTask = $importTask->getTaskByName("IndexRelationship");
        $indexRelationTask->run();
//        var_dump($indexRelationTask->getTaskData('benchmark'));

//        $deleteTask = $this->deleteRecords();
    }

    /**
     * @param $file
     */
    private function importRecords($file)
    {
        $dataSource = DataSourceRepository::getByKey("AUTEST1");
        $importTask = new ImportTask();
        $importTask->init([
            'params' => http_build_query([
                'ds_id' => $dataSource->data_source_id,
                'targetStatus' => 'PUBLISHED'
            ])
        ])->skipLoadingPayload();

        $importTask->setPayload("grantsNetwork", new Payload(TEST_APP_PATH."core/data/$file"));
        $importTask->initialiseTask();
        $importTask->enableRunAllSubTask();
        $importTask->run();

        return $importTask;
    }

    /** @test **/
    public function test_it_should_update_relationship_of_a_record()
    {
        $importTask = new ImportTask();
        $importTask->init([
            'params' => http_build_query([
                'ds_id' => 213,
                'targetStatus' => 'PUBLISHED'
            ])
        ])->skipLoadingPayload();

        $ids = RegistryObject::where('data_source_id', 213)
            ->where('status', 'PUBLISHED')
            ->get()->pluck('registry_object_id')
            ->toArray();

        $importTask->setTaskData('importedRecords', $ids);

        $importTask->initialiseTask();
        $processRelationship = $importTask->getTaskByName("ProcessRelationships");
        $processRelationship->run();

        $indexPortal = $importTask->getTaskByName("IndexPortal");
        $indexPortal->run();

        $indexRelation = $importTask->getTaskByName("IndexRelationship");
        $indexRelation->run();

    }

    /** @test **/
    public function test_it_should_update_relationship_of_a_record_2() {
        $importTask = new ImportTask();
        $importTask->init([
            'params' => http_build_query([
                'ds_id' => 58,
                'targetStatus' => 'PUBLISHED'
            ])
        ])->skipLoadingPayload();
        $importTask->initialiseTask();


        $importTask->setTaskData('importedRecords', [72320]);
        $indexRelation = $importTask->getTaskByName("IndexRelationship");
        $indexRelation->run();

        dd($indexRelation->getMessage());
    }

    private function deleteRecords()
    {
        $dataSource = DataSourceRepository::getByKey("AUTEST1");
        $records = RegistryObjectsRepository::getRecordsByDataSourceIDAndStatus($dataSource->data_source_id, "PUBLISHED", 0, 100);
        $ids = collect($records)->pluck('registry_object_id')->toArray();
        $importTask = new ImportTask();
        $importTask->init([
            'params' => http_build_query([
                'ds_id' => $dataSource->data_source_id,
                'pipeline' => 'PublishingWorkflow'
            ])
        ])->skipLoadingPayload()->enableRunAllSubTask()->initialiseTask();
        $importTask->setTaskData("deletedRecords", $ids);
        $importTask->run();

        return $importTask;
    }


    public function setUpBeforeClass()
    {
        initEloquent();
    }

    public function tearDownAfterClass()
    {
        $files = [];
        foreach (glob(TEST_APP_PATH. "core/data/*.processed") as $filename) {
            $files[] = $filename;
        }
        foreach (glob(TEST_APP_PATH. "core/data/*.validated") as $filename) {
            $files[] = $filename;
        }
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}