<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Registry\Providers\RelationshipProvider;

/**
 * Class ProcessAffectedRelationships
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessAffectedRelationships extends ImportSubTask
{
    protected $requireAffectedRecords = true;
    protected $title = "PROCESSING AFFECTED RELATIONSHIPS";

    public function run_task()
    {
        $affectedRecords = $this->parent()->getTaskData('affectedRecords');
        $duplicateRecords = $this->parent()->getTaskData("duplicateRecords") ? $this->parent()->getTaskData("duplicateRecords") : [];

        $this->log("Size of affected records: ".count($affectedRecords));

        $total = count($affectedRecords);

        $this->log("Processing relationships of $total affected records");
        debug("Processing $total affected records");
        $affected_by_class = ["party"=>[],
            "activity"=>[],
            "service"=>[],
            "collection"=>[]
        ];

        foreach ($affectedRecords as $index => $roID) {
            $record = RegistryObjectsRepository::getRecordByID($roID);
            if($record){
                $affected_by_class[$record->class][] = $record;
            }

        }

        foreach ($affected_by_class as $class => $classArray) {
            foreach($classArray as $index=>$record) {
                debug("Processing affected record: $record->title($record->registry_object_id)");
                RelationshipProvider::process($record);
                RelationshipProvider::processGrantsRelationship($record);
                $this->updateProgress($index, $total, "Processed affected ($index/$total) $record->title($record->registry_object_id)");
            }
            tearDownEloquent();
        }

        $this->log("Size of duplicate records: ".count($duplicateRecords));

        $total_dup = count($duplicateRecords);

        foreach($duplicateRecords as $index => $roID) {
            $record = RegistryObjectsRepository::getRecordByID($roID);
            if($record){
                debug("Processing duplicate record: $record->title($record->registry_object_id)");
                RelationshipProvider::process($record);
                RelationshipProvider::processGrantsRelationship($record);
                $this->updateProgress($index, $total_dup, "Processed duplicate ($index/$total_dup) $record->title($record->registry_object_id)");
            }
        }

        debug("Finished processing (".($total + $total_dup).") affected/duplicate records");

        return;
    }
}