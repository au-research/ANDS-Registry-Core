<?php


namespace ANDS\API\Task\ImportSubTask;

class ProcessRelationships extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING RELATIONSHIPS";

    public function run_task()
    {
        $this->parent()->getCI()->load->model('registry/registry_object/registry_objects', 'ro');
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);
        foreach ($importedRecords as $index => $roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $ro->addRelationships();
            $this->updateProgress($index, $total, "Processed ($index/$total) $ro->title($roID)");
        }
    }
}