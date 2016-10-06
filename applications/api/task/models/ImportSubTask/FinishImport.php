<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 20/09/2016
 * Time: 1:33 PM
 */

namespace ANDS\API\Task\ImportSubTask;

use ANDS\DataSource;
use ANDS\Repository\RegistryObjectsRepository as Repo;

class FinishImport extends ImportSubTask
{

    public function run_task()
    {
        
        $this->parent()->getCI()->load->model('registry/data_source/data_sources', 'ds');
        $dataSource = $this->parent()->getCI()->ds->getByID($this->parent()->dataSourceID);
        if (!$dataSource) {
            $this->stoppedWithError("Data Source ".$this->parent()->dataSourceID." Not Found");
            return;
        }
        
        $this->handleAdvancedHarvest($dataSource);
        
        $this->parent()->setTaskData("datasourceRecordAfterCount",
            Repo::getCountByDataSourceIDAndStatus($this->parent()->dataSourceID,
                $this->parent()->getTaskData("dataSourceDefaultStatus")
            ));

        $this->updateDataSourceLogs($dataSource);
        
        return $this;
    }

    public function handleAdvancedHarvest($dataSource)
    {
        
        if($dataSource->advanced_harvest_mode == 'INCREMENTAL')
        {
            date_default_timezone_set('UTC');
            $dataSource->setAttribute("last_harvest_run_date",date("Y-m-d\TH:i:s\Z", time()));
            date_default_timezone_set('Australia/Canberra');
            $dataSource->setNextHarvestRun($this->parent()->harvestID);
        }
        else
        {
            $dataSource->setAttribute("last_harvest_run_date",'');
            $dataSource->updateHarvestStatus($this->parent()->harvestID, 'COMPLETED');
        }       
    }


    public function updateDataSourceLogs($dataSource)
    {
        //append_log($log_message, $log_type = "info | error", $log_class="data_source", $harvester_error_type=NULL)
        $targetStatus = $this->parent()->getTaskData("targetStatus");
        $selectedKeys = ["dataSourceDefaultStatus"=>"Default Import Status for Data Source",
            "recordsInFeedCount"=>"Valid Records Received in Harvest",
            "invalidRegistryObjectsCount"=>"Failed to Validate",
            "duplicateKeyinFeedCount"=>"Duplicated Records",
            "recordsExistOtherDataSourceCount"=>"Record exist in other Datasource(s)",
            "missingRegistryObjectKeyCount"=>"Invalid due to Missing key",
            "missingOriginatingSourceCount"=>"Invalid due to missing OriginatingSource",
            "missingGroupAttributeCount"=>"Invalid missing group Attribute",
            "recordsCreatedCount"=>"New Records Created",
            "recordsUpdatedCount"=>"Records updated",
            "recordsNotUpdatedCount"=>"Records content unchanged",
            "recordsDeletedCount"=>"Records deleted (due to OAI or Refresh mode",
            "datasourceRecordBeforeCount"=>"Number of ".$targetStatus." records Before Import",
            "datasourceRecordAfterCount"=>"Number of ".$targetStatus." records After Import"];

        $status = 'COMPLETED';
        if ($errorList = $this->parent()->getError()) {
            $message = "IMPORT ".$status." WITH ERROR(S)" . NL;
            $message .= "Batch ID: ".$this->parent()->batchID.NL;
            $message .= "time:".date("Y-m-d\TH:i:s\Z", time()).NL;
            foreach ($selectedKeys as $key=>$title){
                $taskData = $this->parent()->getTaskData($key);
                if($taskData !== 0) {
                    $message .= $title . ": " . $taskData . NL;
                }
            }
            ob_start();
            var_dump($errorList);
            $message .= ob_get_clean();
            $dataSource->append_log($message, 'error');
            return;
        } else {
            $message = "IMPORT ".$status . NL;
            $message .= "Batch ID: ".$this->parent()->batchID.NL;
            $message .= "time:".date("Y-m-d\TH:i:s\Z", time()).NL;
            foreach ($selectedKeys as $key=>$title){
                $taskData = $this->parent()->getTaskData($key);
                if($taskData !== 0) {
                    $message .= $title . ":" . $taskData . NL;
                }
            }
            $dataSource->append_log($message);
        }
    }
}