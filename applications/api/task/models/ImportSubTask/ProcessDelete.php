<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class ProcessDelete
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessDelete extends ImportSubTask
{
    protected $requireDeletedRecords = true;
    protected $title = "DELETING RECORDS";

    public function run_task()
    {
        foreach ($this->parent()->getTaskData('deletedRecords') as $id) {
            $record = RegistryObject::find($id);
            if ($record && $record->isPublishedStatus()) {
                // TODO: Refactor Repo::deleteRecord
                $record->status = "DELETED";
                $record->save();
                $this->log("Record $id ($record->status) is set to DELETED");
                $this->parent()->incrementTaskData("recordsDeletedCount");
            } elseif ($record && $record->isDraftStatus()) {
                RegistryObjectsRepository::completelyEraseRecordByID($id);
                $this->log("Record $id ($record->status) is set to DELETED");
                $this->parent()->incrementTaskData("recordsDeletedCount");
            } else {
                $this->log("Record with ID " . $id . " doesn't exist for deletion");
            }
        }

        // TODO: remove from index all id listed here

    }
}