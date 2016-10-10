<?php


namespace ANDS\API\Task\ImportSubTask;


class ProcessQualityMetadata extends ImportSubTask
{
    protected $requireImportedRecords = true;

    public function run_task()
    {
        $this->parent()->getCI()->load->model('registry/registry_object/registry_objects', 'ro');
        foreach ($this->parent()->getTaskData("importedRecords") as $roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $ro->update_quality_metadata();
        }
    }
}