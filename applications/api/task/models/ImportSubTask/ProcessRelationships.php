<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject\Relationship;
use ANDS\Repository\RegistryObjectsRepository;

class ProcessRelationships extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING RELATIONSHIPS";

    public function run_task()
    {
        // addRelationships to all importedRecords
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

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
        }
    }
}