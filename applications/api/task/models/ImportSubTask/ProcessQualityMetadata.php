<?php


namespace ANDS\API\Task\ImportSubTask;


class ProcessQualityMetadata extends ImportSubTask
{
    protected $requireImportedRecords = true;

    public function run_task()
    {
        foreach ($this->parent()->getTaskData("importedRecords") as $roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $ro->update_quality_metadata();
        }
    }
}