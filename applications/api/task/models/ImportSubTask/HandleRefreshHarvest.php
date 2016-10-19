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
use ANDS\Repository\DataSourceRepository;
use ANDS\RegistryObject;

class HandleRefreshHarvest extends ImportSubTask
{
    private $toBeDeletedRecordCutOffRatio = 0.2;
    public function run_task()
    {

        $dataSource = DataSourceRepository::getByID($this->parent()->dataSourceID);

        if (!$dataSource) {
            $this->stoppedWithError("Data Source ".$this->parent()->dataSourceID." Not Found");
            return;
        }
        $this->handleRefreshHarvest($dataSource);

        
        return $this;
    }

    public function handleRefreshHarvest($dataSource)
    {


        $advanced_harvest_mode = $dataSource->getDataSourceAttribute("advanced_harvest_mode");
        if($advanced_harvest_mode->value == 'REFRESH'){
            $dataSource->updateHarvest($this->parent()->harvestID, ['status'=>'REFRESHING DATASOURCE']);
            $datasourceRecordBeforeCount = $this->parent()->getTaskData("datasourceRecordBeforeCount");
            $recordCount = Repo::getCountByDataSourceIDAndStatus($this->parent()->dataSourceID,
                $this->parent()->getTaskData("targetStatus"));
            $recordsToDelete = Repo::getRecordsByDifferentHarvestID($this->parent()->batchID,
                $this->parent()->dataSourceID, $this->parent()->getTaskData("targetStatus")
                );
            $afterRefreshRecordCount = $recordCount - count($recordsToDelete);
            if(count($recordsToDelete) < 1)
            {
                $this->log("No records found to be deleted");
                $this->parent()->updateImporterMessage("No records found to be deleted");
                return;
            }
            // the total count of the records in the datasource should not be reduced by more than 20%
            if((1 - $this->toBeDeletedRecordCutOffRatio) <= (($afterRefreshRecordCount / $datasourceRecordBeforeCount)))
            {
                $this->log(count($recordsToDelete)." records marked for deletion");
                $this->parent()->updateImporterMessage(count($recordsToDelete)." records marked for deletion");
                foreach($recordsToDelete as $record){
                    $this->parent()->addTaskData('deletedRecords', $record->registry_object_id);
                }
            }
            else{
                $this->log("Refresh is aborted");
                $this->log("Too many (".count($recordsToDelete).") records would be removed, original count(".
                    $datasourceRecordBeforeCount.") would be reduced more than ".
                    ($this->toBeDeletedRecordCutOffRatio * 100)."% to result (".$afterRefreshRecordCount.")");
                $this->parent()->updateImporterMessage("Refresh is aborted; Too many (".count($recordsToDelete).") records would be removed, original count(".
                    $datasourceRecordBeforeCount.") would be reduced more than ".
                    ($this->toBeDeletedRecordCutOffRatio * 100)."% to result (".$afterRefreshRecordCount.")");
            }
        }else{
            $this->log("Advanced Harvest Mode is set ".$advanced_harvest_mode->value);
            $this->parent()->updateImporterMessage("Advanced Harvest Mode is set ".$advanced_harvest_mode->value);
        }
    }


}