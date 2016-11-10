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
        $dataSource = DataSourceRepository::getByKey("AUTEST1");
        $importTask = new ImportTask();
        $importTask
            ->init([
                'params' => http_build_query([
                    'ds_id' => $dataSource->data_source_id
                ])
            ])
            ->initialiseTask()
            ->run();
        $taskArray = $importTask->toArray();
        //dd($dataSource->attr('manual_publish'));
        $this->assertEquals("PUBLISHED", $taskArray["data"]["dataSourceDefaultStatus"]);
    }

    public function setUp()
    {
        initEloquent();
    }
}