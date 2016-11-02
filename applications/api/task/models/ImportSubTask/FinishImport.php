<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 20/09/2016
 * Time: 1:33 PM
 */

namespace ANDS\API\Task\ImportSubTask;

use ANDS\DataSource;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository as Repo;

class FinishImport extends ImportSubTask
{

    private $harvestStarted;

    protected $requireDataSource = true;
    protected $title = "FINISHING IMPORT";

    public function run_task()
    {
        $dataSource = $this->getDataSource();

        $this->setHarvestTime();

        $this->parent()->setTaskData(
            "datasourceRecordAfterCount",
            Repo::getCountByDataSourceIDAndStatus($this->parent()->dataSourceID,
                $this->parent()->getTaskData("targetStatus")
            )
        );

        $this->handleAdvancedHarvest($dataSource);
        $this->updateDataSourceLogs($dataSource);
        $this->setHarvestStatus($dataSource);
        $this->updateDataSourceStats($dataSource);

        return $this;
    }

    private function setHarvestTime()
    {
        $dataSource = $this->getDataSource();

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
    public function handleAdvancedHarvest($dataSource)
    {
        $advancedHarvestMode = $dataSource->getDataSourceAttribute("advanced_harvest_mode")->value;

        $dataSource->setDataSourceAttribute("last_harvest_run_date",'');

        if ($advancedHarvestMode == 'INCREMENTAL') {
            $this->log("Next from_date ". $this->harvestStarted);
            $this->parent()->updateHarvest(['last_run'=>$this->harvestStarted]);
            $dataSource->setDataSourceAttribute("last_harvest_run_date", $this->harvestStarted);
        }

        $dataSource->save();
    }

    public function setHarvestStatus($dataSource){
        $harvestFrequency = $dataSource->getDataSourceAttributeValue("harvest_frequency");
        $harvestDate = strtotime($dataSource->getDataSourceAttributeValue("harvest_date"));

        if($harvestFrequency  == 'once only' || $harvestFrequency == ''){
            $this->parent()->updateHarvest(['status'=>'IDLE', "importer_message" => "Harvest completed!"]);
            return;
        }

        // reschedule the harvest
        $nextRun = $this->getNextHarvestDate($harvestDate, $harvestFrequency);
        $nextHarvestDate = date("Y-m-d\TH:i:s\Z", $nextRun);
        $this->log("Harvest rescheduled to run at ". $nextHarvestDate);
        $this->log("Next from_date ". $this->harvestStarted);
        $dataSource->setDataSourceAttribute("last_harvest_run_date", $this->harvestStarted);
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
            'last_run' => $this->harvestStarted,
            'next_run' => date('Y-m-d\TH:i:s.uP', $nextRun),
            'batch_number' => $batchNumber,
            'importer_message' => "Harvest rescheduled for: $nextRunDate"
        ]);

    }


    /**
     * Add a data_source_log
     *
     * @param $dataSource
     */
    public function updateDataSourceLogs($dataSource)
    {
        //append_log($log_message, $log_type = "info | error", $log_class="data_source", $harvester_error_type=NULL)
        $targetStatus = $this->parent()->getTaskData("targetStatus");
        $selectedKeys = [
            "dataSourceDefaultStatus" => "Default Import Status for Data Source",
            "targetStatus" => "Target Status for Import",
            "recordsInFeedCount" => "Valid Records Received in Harvest",
            "invalidRegistryObjectsCount" => "Failed to Validate",
            "duplicateKeyinFeedCount" => "Duplicated Records",
            "recordsExistOtherDataSourceCount" => "Record exist in other Datasource(s)",
            "missingRegistryObjectKeyCount" => "Invalid due to Missing key",
            "missingOriginatingSourceCount" => "Invalid due to missing OriginatingSource",
            "missingGroupAttributeCount" => "Invalid missing group Attribute",
            "recordsCreatedCount" => "New Records Created",
            "recordsUpdatedCount" => "Records updated",
            "recordsNotUpdatedCount" => "Records content unchanged",
            "recordsDeletedCount" => "Records deleted (due to OAI or Refresh mode)",
            "datasourceRecordBeforeCount" => "Number of " . $targetStatus . " records Before Import",
            "datasourceRecordAfterCount" => "Number of " . $targetStatus . " records After Import",
            "url" => "URL"
        ];

        $source = $this->parent()->getTaskData("source");

        if ($errorList = $this->parent()->getError()) {
            $message = "Import from $source COMPLETED with error(s)" . NL;
            $message .= "Batch ID: ".$this->parent()->batchID.NL;
            $message .= "Time: ".date("Y-m-d\TH:i:s\Z", time()).NL;
            foreach ($selectedKeys as $key=>$title){
                $taskData = $this->parent()->getTaskData($key);
                if($taskData !== 0) {
                    $message .= $title . ": " . $taskData . NL;
                }
            }
            $message .= NL.NL. implode(NL.NL, $errorList);

            $dataSource->appendDataSourceLog($message, "error", "IMPORTER", "");
            return;
        }

        $message = "Import from $source COMPLETED" . NL;
        $message .= "Batch ID: ".$this->parent()->batchID.NL;
        $message .= "Time: ".date("Y-m-d\TH:i:s\Z", time()).NL;
        $message .= "TaskID: ".$this->parent()->getId().NL;

        foreach ($selectedKeys as $key=>$title){
            $taskData = $this->parent()->getTaskData($key);
            if($taskData !== 0 && $taskData !== null && $taskData != "") {
                $message .= $title . ": " . $taskData . NL;
            }
        }

        $this->parent()->setTaskData("dataSourceLog", $message);
        $dataSource->appendDataSourceLog($message, "info", "IMPORTER", "");
        return;
    }

    function getNextHarvestDate($harvestDate, $harvestFrequency)
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

    public function updateDataSourceStats($dataSource)
    {
        // update count_total
        $dataSource->setDataSourceAttribute(
            'count_total',
            RegistryObject::where('data_source_id', $dataSource->data_source_id)->count()
        );

        // count_$status
        $validStatuses = ["MORE_WORK_REQUIRED", "DRAFT", "SUBMITTED_FOR_ASSESSMENT", "ASSESSMENT_IN_PROGRESS", "APPROVED", "PUBLISHED"];
        foreach ($validStatuses as $status) {
            $dataSource->setDataSourceAttribute(
                'count_'.$status,
                RegistryObject::where('data_source_id', $dataSource->data_source_id)
                    ->where('status', $status)->count()
            );
        }

        // TODO :update count_ql

    }

}