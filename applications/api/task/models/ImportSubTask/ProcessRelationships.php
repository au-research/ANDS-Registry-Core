<?php


namespace ANDS\API\Task\ImportSubTask;


class ProcessRelationships extends ImportSubTask
{
    protected $requireImportedRecords = true;

    public function run_task()
    {
        foreach ($this->parent()->getTaskData("importedRecords") as $roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $ro->addRelationships();
            // $ro->cacheRelationshipMetadata();

            // TODO: populate affectedRecords
        }
    }
}