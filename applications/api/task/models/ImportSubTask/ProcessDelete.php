<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\RegistryObject;

class ProcessDelete extends ImportSubTask
{
    public function run_task()
    {
        foreach ($this->parent()->getTaskData('deletedRecords') as $id) {
            $record = RegistryObject::find($id);
            if ($record) {
                $record->status = "DELETED";
                $record->save();
            } else {
                $this->log("Record with ID ".$id." doesn't exist for deletion");
            }

            // TODO: remove from index all id listed here
        }
    }
}