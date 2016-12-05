<?php


namespace ANDS\Test;


use ANDS\API\Task\ImportSubTask\ProcessDelete;
use ANDS\API\Task\ImportTask;
use ANDS\Payload;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;

/**
 * TODO: Make test data source agnostic
 *
 * Class TestPublishingWorkflow
 * @package ANDS\Test
 */
class TestPublishingWorkflow extends UnitTest
{
    /** @test **/
    public function test_it_should_publish_approved()
    {
        $this->ci->config->set_item('harvested_contents_path', TEST_APP_PATH . 'core/data/');

        // there would be some records sitting in approved
        $dataSource = DataSourceRepository::getByKey("AUTEST1");
        $importTask = new ImportTask();
        $importTask->init([
            "name" => "It should import some approved records",
            "params" => http_build_query([
                'ds_id' => '209',
                'runAll' => true
            ])
        ]);

        $importTask->setPayload("AUTestingRecords", new Payload(TEST_APP_PATH. 'core/data/209/AUTestingRecords_ds209_8_different_records.xml'));

        $importTask->skipLoadingPayload();

        $importTask->initialiseTask()->run();

        // check that there are records in approved
        $this->assertEquals(8, RegistryObject::where('data_source_id', '209')->where('status', 'APPROVED')->count());

        // var_dump($importTask->getBenchmarkData());

        // running a publish task on those in approved
        $approvedIDs = RegistryObject::where('data_source_id', '209')->where('status', 'APPROVED')->get()->pluck('registry_object_id')->toArray();
        $publishTask = new ImportTask();
        $publishTask->init([
            'name' => 'It should publish some approved records',
            'params' => http_build_query([
                'ds_id' => '209',
                'runAll' => true,
                'pipeline' => 'PublishingWorkflow',
                'targetStatus' => 'PUBLISHED'
            ])
        ]);

        $publishTask->setTaskData('affectedRecords', $approvedIDs);

        $publishTask->run();

        // var_dump($publishTask->getBenchmarkData());


        // should create the published records
        $this->assertEquals(8, RegistryObject::where('data_source_id', '209')->where('status', 'PUBLISHED')->count());

        // and deleted the drafts
        $this->assertEquals(0, RegistryObject::where('data_source_id', '209')->where('status', 'APPROVED')->count());

        // with the publish record FixRelationshiped

        // deleting all the records afterwards
        $publishedIDs = RegistryObject::where('data_source_id', '209')->where('status', 'PUBLISHED')->get()->pluck('registry_object_id')->toArray();
        $deleteTask = new ImportTask();
        $deleteTask->init([
            'params' => http_build_query([
                'ds_id' => '209',
                'runAll' => true,
                'pipeline' => 'PublishingWorkflow'
            ])
        ])->setTaskData('deletedRecords', $publishedIDs)
            ->skipLoadingPayload()
            ->setCI($this->ci)
            ->initialiseTask();

        $deleteTask->run();

        // ensure nothing is left
        $this->assertEquals(0, RegistryObject::where('data_source_id', '209')->where('status', '!=', 'DELETED')->count());

        // var_dump($deleteTask->getBenchmarkData());
    }

    /** @test **/
    public function test_it_should_be_able_to_find_all_affected_records()
    {
        $importTask = new ImportTask();
        $importTask->setCI($this->ci)->initialiseTask();
        $processDeleteTask = $importTask->getTaskByName("ProcessDelete");

        $this->ci->load->library('solr');

        // random
        $ids = RegistryObject::where('status', 'PUBLISHED')
            ->inRandomOrder()->take(2000)
            ->pluck('registry_object_id')->toArray();

        $affected = $processDeleteTask->getRelatedObjectsIDs($ids);

        $this->assertTrue(count($affected) > 0);

    }

    public function setUpBeforeClass()
    {
        initEloquent();
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