<?php


namespace ANDS\API\Task\ImportSubTask;


class OptimizeRelationship extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "OPTIMISE RELATIONSHIP INDEX";

    public function run_task()
    {
        $this->parent()->getCI()->load->model('registry/registry_object/registry_objects', 'ro');
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);
        foreach ($importedRecords as $index => $roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);

            $this->updateProgress($index, $total, "Processed $ro->title($roID) ($index/$total)");
        }
    }
}