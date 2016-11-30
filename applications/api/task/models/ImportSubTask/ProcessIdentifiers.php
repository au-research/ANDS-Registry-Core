<?php


namespace ANDS\API\Task\ImportSubTask;
use ANDS\Registry\Providers\IdentifierProvider;
use ANDS\Repository\RegistryObjectsRepository;

class ProcessIdentifiers extends ImportSubTask
{
    protected $requirePayload = true;
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING IDENTIFIERS";

    public function run_task()
    {
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);
        debug("Processing Identifiers for $total records");
        foreach ( $importedRecords as $index=>$roID) {
                $record = RegistryObjectsRepository::getRecordByID($roID);
                IdentifierProvider::process($record);
                $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($roID)");
            }
    }
}