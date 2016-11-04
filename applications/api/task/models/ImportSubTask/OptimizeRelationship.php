<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\API\Task\FixRelationshipTask;

class OptimizeRelationship extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "OPTIMISE RELATIONSHIP INDEX";

    public function run_task()
    {
        $this->parent()->getCI()->load->model('registry/registry_object/registry_objects', 'ro');
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

        $fixRelationshipTask = new FixRelationshipTask();
        $fixRelationshipTask->setCi($this->parent()->getCI())->init([]);

        foreach ($importedRecords as $index => $roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $fixRelationshipTask->fixRelationshipRecord($roID);
            $this->updateProgress($index, $total, "Processed ($index/$total) $ro->title($roID) ");
        }
    }
}