<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Registry\Providers\DCI\DataCitationIndexProvider;
use ANDS\Registry\Providers\Scholix\ScholixProvider;
use ANDS\Repository\RegistryObjectsRepository;

class ProcessScholix extends ImportSubTask
{
    protected $title = "PROCESSING SCHOLIX METADATA";

    public function run_task()
    {
        $importedRecords = $this->parent()->getTaskData("importedRecords") ? $this->parent()->getTaskData("importedRecords") : [];

        $affectedRecords = $this->parent()->getTaskData("affectedRecords") ? $this->parent()->getTaskData("affectedRecords") : [];

        $totalRecords = array_merge($importedRecords, $affectedRecords);
        $totalRecords = array_values(array_unique($totalRecords));

        $total = count($totalRecords);

        if ($total == 0) {
            $this->log("No records needed to have scholix generated");
            return;
        }

        $targetStatus = $this->parent()->getTaskData('targetStatus');
        if (!RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' No scholix generation required');
            return;
        }

        foreach ($totalRecords as $index => $roID) {
            $record = RegistryObjectsRepository::getRecordByID($roID);
            if (!$record) {
                $this->log("No record with ID $roID found");
                continue;
            }

            $this->log("Processing Scholix for RegistryObject[id={$record->id}]");
            ScholixProvider::process($record);

            // piggyback this provider on this record
            // TODO maybe refactor into a dedicated (extra) metadata processor for various records
            $this->log("Processing DCI for RegistryObject[id={$record->id}]");
            DataCitationIndexProvider::process($record);

            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($roID)");
        }
    }
}