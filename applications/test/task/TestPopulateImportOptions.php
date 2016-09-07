<?php

namespace ANDS\Test;

use ANDS\API\Task\ImportTask;
use ANDS\Repository\DataSourceRepository;

/**
 * Class TestPopulateImportOptions
 * @package ANDS\Test
 */
class TestPopulateImportOptions extends UnitTest
{
    /** @test **/
    public function test_it_should_populate_the_right_datasource_default_status()
    {
        $dataSource = DataSourceRepository::getByKey("AUTestingRecords");
        $importTask = new ImportTask();
        $importTask
            ->init([
                'params' => 'ds_id='.$dataSource->data_source_id.'&batch_id=1234d'
            ])
            ->initialiseTask()
            ->run();
        $taskArray = $importTask->toArray();
        $this->assertEquals("PUBLISHED", $taskArray["data"]["dataSourceDefaultStatus"]);
    }

    public function setUp()
    {
        $importTask = new ImportTask();
        $importTask->bootEloquentModels();
    }
}