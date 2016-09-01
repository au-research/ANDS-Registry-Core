<?php


namespace ANDS\API\Task\ImportSubTask;


class ProcessRelationships extends ImportSubTask
{
    public function run_task()
    {
        foreach ($this->parent()->getTaskData("importedRecords") as $roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $ro->addRelationships();
            $ro->cacheRelationshipMetadata();
        }
    }
}