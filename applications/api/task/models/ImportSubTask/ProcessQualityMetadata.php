<?php


namespace ANDS\API\Task\ImportSubTask;
use ANDS\Repository\DataSourceRepository;

class ProcessQualityMetadata extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "GATHERING METADATA QUALITY";

    public function run_task()
    {
        $this->parent()->getCI()->load->model('registry/registry_object/registry_objects', 'ro');
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);
        foreach ($importedRecords as $index=>$roID) {
            $ro = $this->parent()->getCI()->ro->getByID($roID);
            $ro->update_quality_metadata();
            $this->updateProgress($index, $total, "Processed $ro->title($roID) ($index/$total)");
        }
    }
}