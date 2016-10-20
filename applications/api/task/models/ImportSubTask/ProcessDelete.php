<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Repository\DataSourceRepository;

class ProcessDelete extends ImportSubTask
{
    protected $requireDeletedRecords = true;

    public function run_task()
    {
        $dataSource = DataSourceRepository::getByID($this->parent()->dataSourceID);
        if (!$dataSource) {
            $this->stoppedWithError("Data Source ".$this->parent()->dataSourceID." Not Found");
            return;
        }
        $this->parent()->updateHarvest(['status'=>'DELETING RECORDS']);

        foreach ($this->parent()->getTaskData('deletedRecords') as $id) {
            $record = RegistryObject::find($id);
            if ($record && $record->isPublishedStatus()) {
                $record->status = "DELETED";
                $record->save();
                $this->parent()->incrementTaskData("recordsDeletedCount");
            } elseif ($record && $record->isDraftStatus()) {
                RegistryObjectsRepository::completelyEraseRecordByID($id);
                $this->parent()->incrementTaskData("recordsDeletedCount");
            } else {
                $this->log("Record with ID " . $id . " doesn't exist for deletion");
            }

            // TODO: remove from index all id listed here
        }
    }
}