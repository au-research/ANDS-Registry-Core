<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class ProcessRelationships
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessRelationships extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING RELATIONSHIPS";

    public function run_task()
    {
        // addRelationships to all importedRecords
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);
        debug("Processing Relationship for $total records");

        // order records by
        $orderedRecords = [
            'party' => [],
            'activity' => [],
            'collection' => [],
            'service' => []
        ];

        foreach($importedRecords as $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            $orderedRecords[$record->class][] = $record;
        }

        $orderedRecords = array_merge(
            $orderedRecords['party'],
            $orderedRecords['activity'],
            $orderedRecords['collection'],
            $orderedRecords['service']
        );

        $this->log("Processing relationship of $total records");

        // get affected ids before the processing (to cater for relationship removal)
        $affectedRecordIDs = RelationshipProvider::getAffectedIDsFromIDs($importedRecords);

        foreach ($orderedRecords as $index => $record) {
            RelationshipProvider::process($record);
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->registry_object_id)");
            tearDownEloquent();
        }

        // TODO: Move to it's own subtask called ProcessGrantsRelationship
        foreach ($orderedRecords as $index => $record) {
            // process implicit relationships for the grants network
            RelationshipProvider::processGrantsRelationship($record);
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->registry_object_id)");
            tearDownEloquent();
        }

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

        // only set if affected is greater than 0
        if (sizeof($affectedRecordIDs) > 0) {
            $this->parent()->setTaskData('affectedRecords', $affectedRecordIDs);
        }
    }

}