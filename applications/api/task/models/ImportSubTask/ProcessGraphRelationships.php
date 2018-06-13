<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Cache\Cache;
use ANDS\Registry\Providers\GraphRelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;

class ProcessGraphRelationships extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING GRAPH RELATIONSHIPS";

    public function run_task()
    {
        $targetStatus = $this->parent()->getTaskData('targetStatus');
        if (!RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' does not process graph network');
            return;
        }

        // importedRecords should already be ordered by ProcessRelationship
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

        $this->log("Process Graph Relationships started for $total records");
        foreach ($importedRecords as $index => $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            // process implicit relationships for the grants network
            try {
                Cache::forget("graph.{$record->id}");
                GraphRelationshipProvider::process($record);
            } catch (\Exception $e) {
                throw new \Exception("Error processing graph relationships for {$id}: ". get_exception_msg($e));
            }
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->registry_object_id)");
            tearDownEloquent();
        }

        $this->log("Process Graph Relationships completed for $total records");
    }
}