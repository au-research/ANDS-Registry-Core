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

        $chunkIDs = collect($ids)->chunk($this->chunkLimit);
        $chunkKeys = collect($keys)->chunk($this->chunkLimit);
        $numChunk = count($chunkIDs);

        $this->log("Number of chunks: $numChunk");

        $affectedRecordIDs = [];
        foreach ($chunkIDs as $index => $chunk) {
            $affectedRecordIDs = array_merge(
                $affectedRecordIDs,
                RelationshipProvider::getAffectedIDsFromIDs(
                    $chunk->toArray(),
                    $chunkKeys->get($index)->toArray(),
                    true
                )
            );

            $affectedRecordIDs = collect($affectedRecordIDs)
                ->flatten()->unique()->values()->toArray();

            $this->updateProgress($index, $numChunk, "Processed ($index/$numChunk)");
        }

        $affectedRecordIDs = collect($affectedRecordIDs)->filter(function($item) use ($ids){
            return !in_array($item, $ids);
        })->unique()->values()->toArray();

        $currentAffectedRecords = $this->parent()->getTaskData('affectedRecords') ? $this->parent()->getTaskData('affectedRecords') : [];
        if ($currentAffectedRecords) {
            $affectedRecordIDs = array_merge($currentAffectedRecords, $affectedRecordIDs);
        }

        // make absolute sure that it's a unique list
        $affectedRecordIDs = collect($affectedRecordIDs)
            ->flatten()->unique()->values()->toArray();

        $countAffected = count($affectedRecordIDs);
        $this->log("Found $countAffected affected records");

        $this->parent()->setTaskData("affectedRecords", $affectedRecordIDs);
    }
}