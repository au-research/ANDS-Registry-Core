<?php


namespace ANDS\API\Task\ImportSubTask;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
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
            // RDA-442 very large harvests fail to update task data is too many identifiers are added to the taskData
            // we should find these Identifiers when we need them using the imported record IDs instead
            //foreach($identifiers as $identifier){
            //    $this->parent()->addTaskData("imported_".$record->class."_identifiers", $identifier);
            // }

            $duplicateCount = count($record->findAllDuplicates());
            $record->setRegistryObjectAttribute("duplicate_count", $duplicateCount);
            $this->updateProgress($index, $total,
                "Processed ($index/$total) $record->title($roID)");
        }
    }
}