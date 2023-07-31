<?php

namespace ANDS\Test;

use ANDS\API\Task\ImportTask;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Repository\DataSourceRepository;
use ANDS\RegistryObject;
use ANDS\DataSource;

/**
 * Class TestPopulateImportOptions
 * @package ANDS\Test
 */
class TestHandleRefreshHarvest extends UnitTest
{
    /** @test **/
    private $ds_id = 210;
    private $batch_id = "DC84A4EF406713BB87D9F2687546E200456332B4";
    private $lessThanCutOff = 0.16;
    private $moreThanCutOff = 0.6;
    private $counter = 0;
    private $refreshHarvestMode = "REFRESH";
    private $standardHarvestMode = "STANDARD";
    private $oldDataSourceadvancedHarvestMode = "";
    
    public function test_it_should_populate_deleted_records()
    {

        $task = $this->getHandleRefreshHarvestTask();

        $this->runPrerequisite($task->parent());
        $this->setUpTestRecords($this->lessThanCutOff);
        $this->setUpAdvancedHarvestMode($this->refreshHarvestMode);


        $task->run();
        // records should be marked for deletion
        $this->assertEquals($this->counter, count($task->parent()->getTaskData('deletedRecords')));
        $log = $task->getLog();
        // log should say how many records are marked for deletion
        $this->assertContains($this->counter." records marked for deletion", $log);

        $this->reSetUpAdvancedHarvestMode();

    }


    public function test_it_should_be_empty_deleted_records()
    {

        $task = $this->getHandleRefreshHarvestTask();
        $this->runPrerequisite($task->parent());
        $this->setUpAdvancedHarvestMode($this->refreshHarvestMode);
        $task->run();
        // records should be marked for deletion
        $this->assertNull($task->parent()->getTaskData('deletedRecords'));
        $log = $task->getLog();
        // log should say how many records are marked for deletion
        $this->assertContains("NO records found to delete", $log);
        $this->reSetUpAdvancedHarvestMode();
    }

    public function test_it_should_abort_refresh_harvest()
    {

        $task = $this->getHandleRefreshHarvestTask();
        $this->runPrerequisite($task->parent());
        $this->setUpAdvancedHarvestMode($this->refreshHarvestMode);
        $this->setUpTestRecords($this->moreThanCutOff);
        $task->run();
        // there should be no records marked for deletion
        $this->assertNull($task->parent()->getTaskData('deletedRecords'));
        $log = $task->getLog();
        // log should say "Refresh is aborted"
        $this->assertContains("Refresh is aborted", $log);
        $this->reSetUpAdvancedHarvestMode();
    }

    /** @name 1 */
    public function test_it_should_not_run_due_to_wrong_mode()
    {

        $task = $this->getHandleRefreshHarvestTask();
        $this->runPrerequisite($task->parent());

        $this->setUpTestRecords($this->lessThanCutOff);
        $this->setUpAdvancedHarvestMode($this->standardHarvestMode);

        $task->run();
        // there should be no records marked for deletion
        $this->assertNull($task->parent()->getTaskData('deletedRecords'));
        $log = $task->getLog();
        $this->assertContains("Advanced Harvest Mode is set ".$this->standardHarvestMode, $log);

        $this->reSetUpAdvancedHarvestMode();
    }

    /**
     * Helper
     * Return a ProcessCoreMetadata Task for use each test
     * @return ImportSubTask
     */
    public function getHandleRefreshHarvestTask()
    {
        $importTask = new ImportTask();
        //dd($importTask);
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id='. $this->ds_id .'&batch_id='.$this->batch_id
        ])->initialiseTask();
        $task = $importTask->getTaskByName("HandleRefreshHarvest");
        return $task;
    }

    /**
     * Helper
     * Run all prerequisite task before this process task
     *
     * @param $importTask
     */
    public function runPrerequisite($importTask)
    {
        $populateImportTask = $importTask->getTaskByName("PopulateImportOptions");
        $populateImportTask->run();
        $validateTask = $importTask->getTaskByName("ValidatePayload");
        $validateTask->run();
        $processTask = $importTask->getTaskByName("ProcessPayload");
        $processTask->run();
        $ingestTask = $importTask->getTaskByName("Ingest");
        $ingestTask->run();
        $ingestTask = $importTask->getTaskByName("ProcessCoreMetadata");
        $ingestTask->run();
        $this->counter = 0;
    }

    public function setUpTestRecords($ratio)
    {
        $records = RegistryObjectsRepository::getRecordsByHarvestID($this->batch_id, $this->ds_id);

        $cutOff = count($records) * $ratio;
        foreach($records as $record){
            if($this->counter < $cutOff){
                $record->setRegistryObjectAttribute('harvest_id', "NOT".$this->batch_id);
                $record->save();
                $this->counter++;
            }
            else{
                break;
            }
        }
    }

    public function setUpAdvancedHarvestMode($newMode)
    {
        $dataSource = DataSourceRepository::getByID($this->ds_id);
        $this->oldDataSourceadvancedHarvestMode = $dataSource->getDataSourceAttribute("advanced_harvest_mode");
        $dataSource->setDatSourceAttribute("advanced_harvest_mode", $newMode);

    }

    public function reSetUpAdvancedHarvestMode()
    {
        $dataSource = DataSourceRepository::getByID($this->ds_id);
        $dataSource->setDatSourceAttribute("advanced_harvest_mode", $this->oldDataSourceadvancedHarvestMode->value);
    }


}