<?php

namespace ANDS\Test;

use ANDS\API\Task\ImportTask;
use ANDS\Repository\DataSourceRepository;
use ANDS\DataSource;
use ANDS\DataSourceAttribute;

/**
 * Class TestPopulateImportOptions
 * @package ANDS\Test
 */
class TestFinishImportTask extends UnitTest
{
    /** @test **/
    private $ds_id = 210;
    private $batch_id = "5E179D76C119BC19F911FD4DE1E8F3542509D482";
    private $incrementhHarvestMode = "INCREMENT";


    
    public function test_it_should_get_correct_nextHarvestDate()
    {

        $task = $this->getFinishImportTask();
        $task->run_task();
    }

    /**
     * Helper
     * Return a ProcessCoreMetadata Task for use each test
     * @return ImportSubTask
     */
    public function getFinishImportTask()
    {
        $importTask = new ImportTask();
        //dd($importTask);
        $importTask->init([
            'name' => 'ImportTask',
            'params' => 'ds_id='. $this->ds_id .'&batch_id='.$this->batch_id
        ])->initialiseTask();
        $task = $importTask->getTaskByName("FinishImport");
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