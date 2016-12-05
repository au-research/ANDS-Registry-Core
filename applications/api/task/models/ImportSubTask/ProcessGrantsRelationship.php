<?php


namespace ANDS\API\Task\ImportSubTask;
use ANDS\Registry\Providers\RelationshipProvider;

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
        // importedRecords should already be ordered by ProcessRelationship
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

        $this->log("Process Grants (inferred) Relationships started for $total records");

        foreach ($importedRecords as $index => $record) {
            // process implicit relationships for the grants network
            RelationshipProvider::processGrantsRelationship($record);
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->registry_object_id)");
            tearDownEloquent();
        }

        $this->log("Process Grants (inferred) Relationships completed for $total records");

        // re-obtain affected ids
        $affectedRecordIDs = RelationshipProvider::getAffectedIDsFromIDs($importedRecords);

        // get affected ids after the processing (to cater for new relationships)
        $affectedRecordIDs = array_merge(
            $affectedRecordIDs,
            RelationshipProvider::getAffectedIDsFromIDs($importedRecords)
        );

        // make absolute sure that it's a unique list
        $affectedRecordIDs = collect($affectedRecordIDs)
            ->flatten()->unique()->values()->toArray();

        // get currently affected records, if set, merge
        $currentAffectedRecords = $this->parent()->getTaskData('affectedRecords');
        if ($currentAffectedRecords) {
            $affectedRecordIDs = array_merge($currentAffectedRecords, $affectedRecordIDs);
        }

        $affectedRecordIDs = array_values(array_unique($affectedRecordIDs));
        $total = count($affectedRecordIDs);

        // only set if affected is greater than 0
        if ($total > 0) {
            $this->parent()->setTaskData('affectedRecords', $affectedRecordIDs);
        }

        $this->log("Discovered $total affected records after Processing Grants relationships");
    }
}