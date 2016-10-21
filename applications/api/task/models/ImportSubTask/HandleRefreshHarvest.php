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
    protected $requireDataSource = true;
    protected $title = "REFRESHING DATASOURCE";

    public function run_task()
    {
        $this->handleRefreshHarvest();
    }

    public function handleRefreshHarvest()
    {
        $dataSource = $this->getDataSource();

        $advanced_harvest_mode = $dataSource->getDataSourceAttribute("advanced_harvest_mode");

        if ($advanced_harvest_mode->value != 'REFRESH') {
            return;
        }

        $datasourceRecordBeforeCount = $this->parent()->getTaskData("datasourceRecordBeforeCount");
        $recordCount = Repo::getCountByDataSourceIDAndStatus(
            $this->parent()->dataSourceID,
            $this->parent()->getTaskData("targetStatus")
        );

        $recordsToDelete = Repo::getRecordsByDifferentHarvestID(
            $this->parent()->batchID,
            $this->parent()->dataSourceID,
            $this->parent()->getTaskData("targetStatus")
        );

        $afterRefreshRecordCount = $recordCount - count($recordsToDelete);

        if (count($recordsToDelete) < 1) {
            $this->log("No records found to be deleted");
            $this->parent()->updateHarvest(["importer_message" => "No records found to be deleted"]);
            return;
        }

        // the total count of the records in the datasource should not be reduced by more than 20%
        if ((1 - $this->toBeDeletedRecordCutOffRatio) <= (($afterRefreshRecordCount / $datasourceRecordBeforeCount))) {
            $this->log(count($recordsToDelete) . " records marked for deletion");
            $this->parent()->updateHarvest([
                "importer_message" => count($recordsToDelete) . " records marked for deletion"
            ]);
            foreach ($recordsToDelete as $record) {
                $this->parent()->addTaskData('deletedRecords', $record->registry_object_id);
            }
            return;
        }

        $this->log("Refresh is aborted");
        $this->log("Too many (" . count($recordsToDelete) . ") records would be removed, original count(" .
            $datasourceRecordBeforeCount . ") would be reduced more than " .
            ($this->toBeDeletedRecordCutOffRatio * 100) . "% to result (" . $afterRefreshRecordCount . ")");
        $this->parent()->updateHarvest([
            "importer_message" => "Refresh is aborted; Too many (" . count($recordsToDelete) . ") records would be removed, original count(" .
                $datasourceRecordBeforeCount . ") would be reduced more than " .
                ($this->toBeDeletedRecordCutOffRatio * 100) . "% to result (" . $afterRefreshRecordCount . ")"
        ]);
    }
}