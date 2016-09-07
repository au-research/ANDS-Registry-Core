<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class ProcessDelete extends ImportSubTask
{
    protected $requireDeletedRecords = true;

    public function run_task()
    {
        foreach ($this->parent()->getTaskData('deletedRecords') as $id) {
            $record = RegistryObject::find($id);
            if ($record && $record->isPublishedStatus()) {
                $record->status = "DELETED";
                $record->save();
            } elseif ($record && $record->isDraftStatus()) {
                RegistryObjectsRepository::completelyEraseRecordByID($id);
            } else {
                $this->log("Record with ID " . $id . " doesn't exist for deletion");
            }

            // TODO: remove from index all id listed here
        }
    }
}