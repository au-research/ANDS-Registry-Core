<?php


namespace ANDS\Test;


use ANDS\API\Task\ImportTask;
use ANDS\DataSource;
use ANDS\Payload;
use ANDS\Registry\Importer;
use ANDS\Registry\Providers\GrantsConnectionsProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Registry\Connections;

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
        }
    }

    /** @test **/
    public function test_it_should_sample_3()
    {
        $ids = [
            70118,
            901991,
            901992,
            901993,
            901994,
            901995,
            70115,
            70063,
            70119,
            902217,
            902218,
            820112,
            901680,
            901681,
            901682,
            901683,
            901684,
            901685,
            901686,
            901687,
            901688,
            901689,
            901690,
            901691,
            901692,
            901693,
            901694,
            901695,
            901696,
            901697,
            901698,
            901699,
            901700,
            901701,
            901702,
            901703,
            901704,
            901705,
            901706,
            901707,
            901708,
            901709,
            901710,
            901711,
            901712,
            901713,
            901714,
            901715,
            901716,
            901717,
            901718,
            901719,
            901720,
            901721,
            901722,
            901723,
            901724,
            901725,
            901726,
            901727,
            901728,
            901729,
            901730,
            901731,
            901732,
            901733,
            901734,
            901735,
            901736,
            901737,
            901738,
            901739,
            901740,
            901741,
            901742,
            901743,
            901744,
            901745,
            901746,
            901747,
            901748,
            901749,
            901750,
            901751,
            901752,
            901753,
            901754,
            901755,
            901756,
            901757,
            901758,
            901759,
            901760,
            901761,
            901762,
            901763,
            901764,
            901765,
            901766,
            901767,
            901768,
            901769,
            901770,
            901771,
            901772,
            901773,
            901774,
            901775,
            902219,
            902220,
            902221,
            902222,
            902223,
            902224,
            902225,
            902226,
            902227,
            902228,
            902229,
            902230,
            902231,
            902232,
            902233,
            902234,
            902235,
            902236,
            902237,
            902238,
            902239,
            902240,
            902241,
            902242,
            902243,
            902244,
            902245,
            902246,
            902247,
            902248,
            902249,
            902250,
            902251,
            902252,
            902253,
            902254,
            902255,
            902256,
            902257,
            902258,
            902259,
            902260,
            902261,
            902262,
            902263,
            902264,
            902265,
            902266,
            902267,
            902268,
            902269,
            902270,
            902271,
            902272,
            902273,
            902274,
            902275,
            902276,
            902277,
            902278,
            902279,
            902280,
            902281,
            902282,
            902283,
            902284,
            902285,
            902286,
            902287,
            902288,
            902289,
            902290,
            902291,
            902292,
            902293,
            902294,
            902295,
            902296,
            902297,
            902298,
            902299,
            902300,
            902301,
            902302,
            902303,
            902304,
            902305,
            902306,
            902307,
            902308,
            902309,
            902310,
            902311,
            902312,
            902313,
            902314,
            902315,
            902316,
            902317,
            902318,
            902319,
            902320,
            902321,
            902322,
            902323,
            902324,
            902325,
            902326,
            902327,
            902328,
            902329,
            902330,
            902331,
            902332,
            902333,
            902334,
            902335,
            902336,
            902337,
            902338,
            902339,
            902340,
            902341,
            902342,
            902343,
            902344,
            902345,
            902346,
            902347,
            902348,
            902349,
            902350,
            902351,
            902352,
            902353,
            902354,
            902355,
            902356,
            902357,
            902358,
            902359,
            902360,
            902361,
            902362,
            902363,
            902364,
            902365,
            902366,
            902367,
            902368,
            902369,
            902370,
            902371,
            902372,
            902373,
            902374,
            902375,
            902376,
            902377,
            902378,
            902379,
            902380,
            902381,
            902382,
            902383,
            902384,
            902385,
            902386,
            902387,
            902388,
            902389,
            902390,
            902391,
            902392,
            902393,
            902394,
            902395,
            902396,
            902397,
            902398,
            902399,
            902400,
            902401,
            902402,
            902403,
            902404,
            902405,
            902406,
            902407,
            902408,
            902409,
            902410,
            902411,
            902412,
            902413,
            902414,
            902415,
            902416,
            902417,
            902418,
            902419,
            902420,
            902421,
            902422,
            902423,
            226105,
            70034,
            70051,
            70060,
            70120,
            70121,
            208842,
            208859,
            208868,
            208928,
            208929,
            231896,
            231897,
            231898,
            231899,
            231900,
            70117,
            70043,
            208926,
            208923,
            208871,
            208925,
            208927,
            226137,
            226103,
            226115
        ];
        foreach ($ids as $id) {
            var_dump($id);
            $record = RegistryObject::find($id);
            RelationshipProvider::process($record);
        }

    }

    /** @test **/
    public function test_it_should_sample_2()
    {
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $ro = $this->ci->ro->getByID(971354);
        $ro->generateSlug();

        dd($ro->slug);

        $imported = [798252,
            798253,
            798254,
            798255,
            798256,
            798257,
            798258,
            798259,
            798260,
            798261,
            798262,
            798263,
            798264];

        $orderedRecords = [
            'party' => [],
            'activity' => [],
            'collection' => [],
            'service' => []
        ];

        foreach($imported as $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            $orderedRecords[$record->class][] = $record;
        }

        $orderedRecords = array_merge(
            $orderedRecords['party'],
            $orderedRecords['activity'],
            $orderedRecords['collection'],
            $orderedRecords['service']
        );

        $affected = RelationshipProvider::getAffectedIDsFromIDs($imported);
        dd($affected);
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