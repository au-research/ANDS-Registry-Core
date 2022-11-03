<?php

namespace ANDS\API\Task\ImportSubTask;

use ANDS\DataSource;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository as Repo;

class ScheduleHarvest extends ImportSubTask
{
    protected $requireDataSource = true;
    protected $title = "SCHEDULING NEXT HARVEST";

    public function run_task()
    {
        $dataSource = $this->getDataSource();
        if(!$dataSource){
            $dataSource = DataSource::find($this->parent()->dataSourceID);
        }
        $this->setHarvestTime();
        $this->handleIncrementalHarvest($dataSource);
        $this->setHarvestStatus($dataSource);
    }

    private function setHarvestTime()
    {
        $dataSource = $this->getDataSource();
        if(!$dataSource){
            $dataSource = DataSource::find($this->parent()->dataSourceID);
        }

        $harvestMessage = null;

        if ($harvestID = $dataSource->getHarvest($this->parent()->harvestID)) {
            $harvestMessage = json_decode($dataSource->getHarvest($this->parent()->harvestID)->message);
        }

        if (isset($harvestMessage->start_utc)) {
            $this->harvestStarted = $harvestMessage->start_utc;
            return;
        }

        date_default_timezone_set('UTC');
        $this->harvestStarted = date("Y-m-d\TH:i:s\Z", time());
        date_default_timezone_set('Australia/Canberra');
    }

    /**
     * Check INCREMENTAL
     * set last_harvest_run_date
     *
     * @param $dataSource
     */
    public function handleIncrementalHarvest($dataSource)
    {
        $advancedHarvestMode = $dataSource->getDataSourceAttribute("advanced_harvest_mode")->value;

        $this->parent()->updateHarvest(['last_run' => date('Y-m-d\TH:i:s.uP', strtotime($this->harvestStarted))]);

        if ($advancedHarvestMode == 'INCREMENTAL') {
            if($this->parent()->getTaskData('pipeline') == 'ErrorWorkflow'){
                $previous_run_date = $dataSource->getDataSourceAttributeValue("last_harvest_run_date");
                $this->log("Next from_date ". $previous_run_date);
            }
            else {
                $this->log("Next from_date " . $this->harvestStarted);
                $dataSource->setDataSourceAttribute("last_harvest_run_date", $this->harvestStarted);
            }
        }
        else {
            $dataSource->setDataSourceAttribute("last_harvest_run_date",'');
        }

        $dataSource->save();
    }

    public function setHarvestStatus($dataSource)
    {
        $harvestFrequency = $dataSource->getDataSourceAttributeValue("harvest_frequency");
        $harvestDate = strtotime($dataSource->getDataSourceAttributeValue("harvest_date"));

        if ($harvestFrequency  == 'once only' || $harvestFrequency == '') {
            $this->parent()->updateHarvest(['status'=>'IDLE', "importer_message" => "Harvest completed!"]);
            return;
        }

        // reschedule the harvest
        $nextRun = $this->getNextHarvestDate($harvestDate, $harvestFrequency);
        $nextHarvestDate = date("Y-m-d\TH:i:s\Z", $nextRun);
        $this->log("Harvest rescheduled to run at ". $nextHarvestDate);
        $batchNumber = strtoupper(sha1($nextRun));

        $nextRunDate = date('Y-m-d H:i:s', $nextRun);

        // Only log reinstante if source is harvester
        $source = $this->parent()->getTaskData('source') ? $this->parent()->getTaskData('source') : "harvester";
        if ($source == "harvester") {
            $dataSource->appendDataSourceLog(
                "Harvest rescheduled for: $nextRunDate with previous settings",
                "info", "IMPORTER"
            );
        }

        $this->parent()->updateHarvest([
            'status' => 'SCHEDULED',
            'next_run' => date('Y-m-d\TH:i:s.uP', $nextRun),
            'batch_number' => $batchNumber,
            'importer_message' => "Harvest rescheduled for: $nextRunDate"
        ]);

    }

    private function getNextHarvestDate($harvestDate, $harvestFrequency)
    {
        $now = time();

        if ($harvestDate !== null) {
            $nextHarvest = $harvestDate;
        } else {
            $nextHarvest = 0;
        }

        while ($nextHarvest < $now) {
            if ($harvestFrequency == 'daily') {
                $nextHarvest = strtotime('+1 day', $nextHarvest);
            } elseif ($harvestFrequency == 'weekly') {
                $nextHarvest = strtotime('+1 week', $nextHarvest);
            } elseif ($harvestFrequency == 'fortnightly') {
                $nextHarvest = strtotime('+2 week', $nextHarvest);
            } elseif ($harvestFrequency == 'monthly') {
                $nextHarvest = strtotime('+1 month', $nextHarvest);
            } elseif ($harvestFrequency == 'hourly') {
                $nextHarvest += 60 * 60;
            }
        }

        return $nextHarvest;
    }
}