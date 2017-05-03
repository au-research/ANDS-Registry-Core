<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Registry\Providers\ScholixProvider;
use ANDS\Repository\RegistryObjectsRepository;

class ProcessScholix extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING SCHOLIX METADATA";

    public function run_task()
    {
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

        $targetStatus = $this->parent()->getTaskData('targetStatus');
        if (!RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' No scholix generation required');
            return;
        }

        foreach ($importedRecords as $index => $roID) {
            $record = RegistryObjectsRepository::getRecordByID($roID);
            if (!$record) {
                $this->log("No record with ID $roID found");
                continue;
            }
            ScholixProvider::process($record);
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($roID)");
        }
    }
}