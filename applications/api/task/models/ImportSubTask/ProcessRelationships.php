<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject\Relationship;
use ANDS\Repository\RegistryObjectsRepository;
use Illuminate\Support\Collection;

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

        foreach ($orderedRecords as $index => $record) {
            RelationshipProvider::process($record);
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->registry_object_id)");
            tearDownEloquent();
        }

        $affectedRecordIDs = RelationshipProvider::getAffectedIDsFromIDs($importedRecords);

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


        $this->processAffectedRecords();

    }

    public function processAffectedRecords()
    {
        $affectedRecords = $this->parent()->getTaskData('affectedRecords');
        if (!$affectedRecords) {
            return;
        }
        $total = count($affectedRecords);

        $this->log("Processing relationships of $total affected records");
        debug("Processing $total affected records");

        foreach ($affectedRecords as $index => $roID) {
            $record = RegistryObjectsRepository::getRecordByID($roID);
            RelationshipProvider::process($record);
            $this->updateProgress($index, $total, "Processed affected ($index/$total) $record->title($record->registry_object_id)");
        }

    }
}