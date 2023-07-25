<?php


namespace ANDS\API\Task\ImportSubTask;
use ANDS\Registry\Providers\Quality\QualityMetadataProvider;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class ProcessQualityMetadata
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessQualityMetadata extends ImportSubTask
{
    protected $requireImportedRecords = true;
    public $title = "GATHERING METADATA QUALITY";

    public function run_task()
    {
        $importedRecords = $this->parent()->getTaskData("importedRecords") ? $this->parent()->getTaskData("importedRecords") : [];
        $affectedRecords = $this->parent()->getTaskData("affectedRecords") ? $this->parent()->getTaskData("affectedRecords") : [];
        $totalRecords = array_merge($importedRecords, $affectedRecords);
        $totalRecords = array_values(array_unique($totalRecords));

        $total = count($totalRecords);

        $this->log("Running Quality metadata on $total records");

        foreach ($totalRecords as $index => $roID) {
            $record = RegistryObjectsRepository::getRecordByID($roID);
            if($record){
                QualityMetadataProvider::process($record);
                $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($roID)");
            }
        }
    }
}