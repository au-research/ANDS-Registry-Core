<?php


namespace ANDS\API\Task\ImportSubTask;
use ANDS\Registry\Providers\LinkProvider;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class ProcessLinks
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessLinks extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING LINKS";

    public function run_task()
    {
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);
        $this->log("Generating links for $total records");
        foreach ($importedRecords as $index => $roID) {
            $record = RegistryObjectsRepository::getRecordByID($roID);
            $newLinks = LinkProvider::process($record);
            $this->updateProgress($index, $total,
                "Processed ($index/$total) $record->title($roID), links ($newLinks)");
        }
    }
}