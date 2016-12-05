<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Registry\Providers\RelationshipProvider;

/**
 * Class PopulateAffectedList
 * @package ANDS\API\Task\ImportSubTask
 */
class PopulateAffectedList extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "GENERATING AFFECTED LIST";
    private $chunkLimit = 200;

    public function run_task()
    {
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

        $this->log("Getting affectedIDs for $total records");

        $chunks = collect($importedRecords)->chunk($this->chunkLimit);
        $numChunk = count($chunks);

        $this->log("Number of chunks: $numChunk");

        $affectedRecordIDs = [];
        foreach ($chunks as $index => $chunk) {
            $affectedRecordIDs = array_merge(
                $affectedRecordIDs,
                RelationshipProvider::getAffectedIDsFromIDs($importedRecords)
            );
            $this->updateProgress($index, $total, "Processed ($index/$numChunk)");
        }

        $countAffected = count($affectedRecordIDs);
        $this->log("Found $countAffected affected records");

        $currentAffectedRecords = $this->parent()->getTaskData('affectedRecords') ? $this->parent()->getTaskByData('affectedRecords') : [];
        if ($currentAffectedRecords) {
            $affectedRecordIDs = array_merge($currentAffectedRecords, $affectedRecordIDs);
        }

        // make absolute sure that it's a unique list
        $affectedRecordIDs = collect($affectedRecordIDs)
            ->flatten()->unique()->values()->toArray();

        $this->parent()->setTaskData("affectedIDs", $affectedRecordIDs);
    }
}