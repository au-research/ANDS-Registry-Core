<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;

/**
 * Class PopulateAffectedList
 * @package ANDS\API\Task\ImportSubTask
 */
class PopulateAffectedList extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "GENERATING AFFECTED LIST";
    private $chunkLimit = 100;

    public function run_task()
    {
        $ids = [];
        $keys = [];
        foreach (['party', 'activity', 'collection', 'service'] as $class) {
            if ($this->parent()->getTaskData("imported_".$class."_ids")) {
                $ids = array_merge(
                    $ids, $this->parent()->getTaskData("imported_".$class."_ids")
                );
                $keys = array_merge(
                    $keys, $this->parent()->getTaskData("imported_".$class."_keys")
                );
            }
        }

        // try importedRecords if the imported_$class_ids are not populated
        // this is the case for ManualImport pipeline
        if (sizeof($ids) == 0) {
            $importedRecords = $this->parent()->getTaskData('importedRecords');
            $ids = $importedRecords;
            $keys = RegistryObject::whereIn('registry_object_id', $ids)
                ->get()->pluck('key')->toArray();
        }

        $total = count($ids);

        $this->log("Getting affectedIDs for $total records");

        $affectedRecordIDs = RelationshipProvider::getAffectedIDsFromIDs($ids, $keys);

        $countAffected = count($affectedRecordIDs);
        $this->log("Found $countAffected affected records");

        $this->parent()->setTaskData("affectedRecords", $affectedRecordIDs);
    }
}