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
            $recordCount = Repo::getCountByDataSourceIDAndStatus($this->parent()->dataSourceID,
                $this->parent()->getTaskData("targetStatus"));
            $recordsToDelete = Repo::getRecordsByDifferentHarvestID($this->parent()->batchID,
                $this->parent()->dataSourceID, $this->parent()->getTaskData("targetStatus")
                );
            if(count($recordsToDelete) < 1)
            {
                $this->log("No records found to be deleted");
                return;
            }
            if($this->toBeDeletedRecordCutOffRatio > ((count($recordsToDelete) / $recordCount)))
            {
                $this->log(count($recordsToDelete)." records marked for deletion");
                foreach($recordsToDelete as $record){
                    $this->parent()->addTaskData('deletedRecords', $record->registry_object_id);
                }
            }
            else{
                $this->log("Refresh is aborted");
                $this->log("Too many records would be removed ".count($recordsToDelete). "
                is more than ".($this->toBeDeletedRecordCutOffRatio * 100)."% of the total ".$recordCount);
            }
        }else{
            $this->log("Advanced Harvest Mode is set ".$advanced_harvest_mode->value);
        }
    }
}