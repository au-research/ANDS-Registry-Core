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
        $this->processUnchangedRecords();
        $this->handleRefreshHarvest();
    }

    /**
     * records that didn't get update but were included in the feed also get a new harvest_id
     */
    public function processUnchangedRecords()
    {
        $harvestedRecords = $this->parent()->getTaskData("harvestedRecordIDs");

        if ($harvestedRecords === false || $harvestedRecords === null) {
            return;
        }

        $total = count($harvestedRecords);
        foreach ($harvestedRecords as $index => $roID) {
//            $this->log('Processing (unchanged) record: ' . $roID);
//            $this->log('setting harvest_id for not refreshed records: ' . $roID);
            if ($record = RegistryObject::find($roID)) {
                $record->setRegistryObjectAttribute('harvest_id',
                    $this->parent()->batchID);
                $record->status = $this->parent()->getTaskData("targetStatus");
                $record->save();
                // $this->updateProgress($index, $total, "Processed ($index/$total) (unchanged) $record->title($roID) ");
            } else {
                $this->log("Unable to find RegistryObject ID: ". $roID);
            }
        }
    }

    public function handleRefreshHarvest()
    {
        // not a real harvest
        if($this->parent()->getHarvestID() == null){
            return;
        }


        $dataSource = $this->getDataSource();

        $advanced_harvest_mode = $dataSource->getDataSourceAttribute("advanced_harvest_mode");

        if ($advanced_harvest_mode->value != 'REFRESH') {
            return;
        }

        $this->parent()->setTaskData("refreshHarvestStatus" , "set");

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
            $msg = "No records found to be deleted";
            $this->log($msg);
            $this->parent()->setTaskData("refreshHarvestStatus" , $msg);
            $this->parent()->updateHarvest(["importer_message" => $msg]);
            return;
        }

        // the total count of the records in the datasource should not be reduced by more than 20%
        if ((1 - $this->toBeDeletedRecordCutOffRatio) <= (($afterRefreshRecordCount / $datasourceRecordBeforeCount))) {
            $msg = count($recordsToDelete) . " records marked for deletion";
            $this->log(count($recordsToDelete) . $msg);
            $this->parent()->updateHarvest(["importer_message" => $msg]);
            $this->parent()->setTaskData("refreshHarvestStatus" , $msg);
            foreach ($recordsToDelete as $record) {
                $this->parent()->addTaskData('deletedRecords', $record->registry_object_id);
            }
            return;
        }
        $this->log("Refresh Cancelled");
        $msg = "Refresh Cancelled Too many (" . count($recordsToDelete) . ") records would be removed, original count(" .
            $datasourceRecordBeforeCount . ") would be reduced more than " .
            ($this->toBeDeletedRecordCutOffRatio * 100) . "% to result (" . $afterRefreshRecordCount . ")";
        $this->log($msg);
        $this->parent()->setTaskData("refreshHarvestStatus" , $msg);
        $this->parent()->updateHarvest(["importer_message" => $msg]);
    }
}