<?php


namespace ANDS\API\Task\ImportSubTask;
use ANDS\Registry\Providers\IdentifierProvider;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class ProcessIdentifiers
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessIdentifiers extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING IDENTIFIERS";

    public function run_task()
    {
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);
        $this->log("Processing Identifiers for $total records");
        foreach ($importedRecords as $index => $roID) {
            $record = RegistryObjectsRepository::getRecordByID($roID);
            $identifiers = IdentifierProvider::process($record);
            foreach($identifiers as $identifier){
                $this->parent()->addTaskData("imported_".$record->class."_identifiers", $identifier);
            }
            $this->updateProgress($index, $total,
                "Processed ($index/$total) $record->title($roID)");
        }
    }
}