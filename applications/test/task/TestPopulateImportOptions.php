<?php

namespace ANDS\Test;


use ANDS\API\Task\ImportSubTask\PopulateImportOptions;
use ANDS\API\Task\ImportTask;

class TestPopulateImportOptions extends UnitTest
{
    /** @test **/
    public function test_it_should_populate_the_right_datasource_default_status()
    {
        $dataSource = $this->ci->ds->getByKey("AUTestingRecords");
        $importTask = new ImportTask();
        $importTask
            ->init([
                'params' => 'ds_id='.$dataSource->id.'&batch_id=1234d'
            ])
            ->initialiseTask()
            ->run();
        $taskArray = $importTask->toArray();
        $this->assertEquals("PUBLISHED", $taskArray["data"]["dataSourceDefaultStatus"]);
    }

    public function setUp()
    {
        $this->ci->load->model('registry/data_source/data_sources', 'ds');
        require_once(API_APP_PATH.'vendor/autoload.php');
    }

}