<?php

namespace ANDS\Test;

use ANDS\API\Task\ImportTask;
use ANDS\Repository\DataSourceRepository;
use ANDS\DataSource as DataSource;
use ANDS\DataSourceAttribute;

/**
 * Class TestPopulateImportOptions
 * @package ANDS\Test
 */
class TestScheduleHarvestTask extends UnitTest
{
    /** @test **/
    private $ds_id = 5;
    private $batch_id = "DOESNTMATTER";
    private $harvest_id = 50;
    private $dataSource;
    private $incrementhHarvestMode = "INCREMENTAL";
    private $oldDataSourceadvancedHarvestMode = "";


    /** @test **/
    public function test_it_should_not_change_last_harvest_run_date()
    {

        $task = $this->getScheduleHarvestTask('ErrorWorkflow');

        $this->assertEquals($task->parent()->dataSourceID, "5");
        $this->assertEquals($task->parent()->batchID, "DOESNTMATTER");
        $this->dataSource = DataSourceRepository::getByID($this->ds_id);
        $this->setUpAdvancedHarvestMode($this->incrementhHarvestMode);
        $oldHarvestFrom = $this->dataSource->getDataSourceAttributeValue("last_harvest_run_date");

        $task->run_task();
        $this->assertEquals($oldHarvestFrom, $this->dataSource->getDataSourceAttributeValue("last_harvest_run_date"));
        $this->reSetUpAdvancedHarvestMode();
    }
    /** @test **/
    public function test_it_should_change_last_harvest_run_date()
    {

        $task = $this->getScheduleHarvestTask('default');
        $this->setUpAdvancedHarvestMode($this->incrementhHarvestMode);
        $this->assertEquals($task->parent()->dataSourceID, "5");
        $this->assertEquals($task->parent()->batchID, "DOESNTMATTER");
        $this->dataSource = DataSourceRepository::getByID($this->ds_id);
        $oldHarvestFrom =$this->dataSource->getDataSourceAttributeValue("last_harvest_run_date");

        $task->run_task();
        $this->assertNotEquals($oldHarvestFrom, $this->dataSource->getDataSourceAttributeValue("last_harvest_run_date"));
        $this->reSetUpAdvancedHarvestMode();
    }
    
    /**
     * Helper
     * Return a ProcessCoreMetadata Task for use each test
     * @return ImportSubTask
     */
    public function getScheduleHarvestTask($pipeline)
    {
        $importTask = new ImportTask();
        //dd($importTask);
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id='. $this->ds_id .'&batch_id='.$this->batch_id.'&harvest_id='.$this->harvest_id.'&pipeline='.$pipeline
        ])->initialiseTask();
        $task = $importTask->getTaskByName("ScheduleHarvest");

        return $task;
    }


    public function setUpAdvancedHarvestMode($newMode)
    {
        $this->oldDataSourceadvancedHarvestMode = $this->dataSource->getDataSourceAttributeValue("advanced_harvest_mode");
        $this->dataSource->setDataSourceAttribute("advanced_harvest_mode", $newMode);

    }

    public function reSetUpAdvancedHarvestMode()
    {
        $this->dataSource->setDataSourceAttribute("advanced_harvest_mode", $this->oldDataSourceadvancedHarvestMode);
    }


}