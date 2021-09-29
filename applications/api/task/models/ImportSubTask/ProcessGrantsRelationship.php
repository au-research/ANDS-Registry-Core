<?php


namespace ANDS\API\Task\ImportSubTask;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class ProcessGrantsRelationship
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessGrantsRelationship extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING GRANTS NETWORK RELATIONSHIPS";

    public function run_task()
    {
        $targetStatus = $this->parent()->getTaskData('targetStatus');
        if (!RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' does not process grants network');
            return;
        }

        // importedRecords should already be ordered by ProcessRelationship
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

        $this->log("Process Grants (inferred) Relationships started for $total records");

        foreach ($importedRecords as $index => $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            // process implicit relationships for the grants network
            RelationshipProvider::processGrantsRelationship($record);
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->registry_object_id)");
            tearDownEloquent();
        }

        $this->log("Process Grants (inferred) Relationships completed for $total records");
    }
}