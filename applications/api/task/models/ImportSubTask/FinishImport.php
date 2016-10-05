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
        $selectedKeys = ["recordsDeletedCount",
            "recordsInFeedCount",
            "recordsExistOtherDataSourceCount",
            "recordsCreatedCount",
            "recordsUpdatedCount",
            "recordsNotUpdatedCount",
            "datasourceRecordBeforeCount",
            "dataSourceDefaultStatus",
            "datasourceRecordAfterCount"];
        
        //$status = strtoupper($this->parent()->getStatus());
        $status = 'COMPLETED';
        if ($errorList = $this->parent()->getError()) {
            $message = "IMPORT ".$status." WITH ERROR(S)" . NL;
            $message .= "time:".date("Y-m-d\TH:i:s\Z", time()).NL;
            foreach ($this->parent()->taskData as $key => $val) {
                if (in_array($key, $selectedKeys)) {
                    $message .= $key . ":" . $val . NL;
                }
            }
            ob_start();
            var_dump($errorList);
            $message .= ob_get_clean();
            $dataSource->append_log($message, 'error');
            return;
        } else {
            $message = "IMPORT ".$status . NL;
            $message .= "time:".date("Y-m-d\TH:i:s\Z", time()).NL;
            foreach ($this->parent()->taskData as $key => $val) {
                if (in_array($key, $selectedKeys)) {
                    $message .= $key . ":" . $val . NL;
                }
            }
            $dataSource->append_log($message);
        }
    }
}