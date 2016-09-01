<?php


namespace ANDS\API\Task\ImportSubTask;


class ProcessIdentifier extends ImportSubTask
{
    public function run_task()
    {
        foreach ($this->parent()->getTaskData("importedRecords") as $roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $ro->processIdentifiers();
        }
    }
}