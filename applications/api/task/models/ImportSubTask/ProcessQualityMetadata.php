<?php


namespace ANDS\API\Task\ImportSubTask;
use ANDS\Registry\Providers\QualityMetadataProvider;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class ProcessQualityMetadata
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessQualityMetadata extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "GATHERING METADATA QUALITY";

    public function run_task()
    {
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);
        foreach ($importedRecords as $index => $roID) {
            $record = RegistryObjectsRepository::getRecordByID($roID);
            QualityMetadataProvider::process($record);
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($roID)");
        }
    }
}