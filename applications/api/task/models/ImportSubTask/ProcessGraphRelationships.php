<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Cache\Cache;
use ANDS\Registry\Providers\GraphRelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;
use GraphAware\Common\Result\CombinedStatistics;
use GraphAware\Common\Result\ResultCollection;

class ProcessGraphRelationships extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING GRAPH RELATIONSHIPS";

    /** @var int time limit in seconds */
    protected $timeLimit = 3600;

    public function run_task()
    {
        $startTime = microtime(true);
        $targetStatus = $this->parent()->getTaskData('targetStatus');
        if (!RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' does not process graph network');
            return;
        }

        // importedRecords should already be ordered by ProcessRelationship
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

        $this->log("Process Graph Relationships started for $total records");
        $stats = new CombinedStatistics();
        foreach ($importedRecords as $index => $id) {

            $elapsed = microtime(true) - $startTime;
            if ($elapsed > $this->timeLimit) {
                $this->addError("Elapsed: {$elapsed}s. Task has run for more than {$this->timeLimit} seconds. Terminating... Processed ($index/$total)");
                break;
            }

            $record = RegistryObjectsRepository::getRecordByID($id);
            try {
                Cache::forget("graph.{$record->id}");

                /** @var CombinedStatistics $statistics */
                $stat = GraphRelationshipProvider::process($record);

                $stats->mergeStats($stat);

            } catch (\Exception $e) {
                $this->addError("Error processing graph relationships for {$id}: ". get_exception_msg($e));
            }
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->registry_object_id)");
        }

        $this->log("Nodes Created: {$stats->nodesCreated()}");
        $this->log("Nodes Deleted: {$stats->nodesDeleted()}");
        $this->log("Relationships Created: {$stats->relationshipsCreated()}");
        $this->log("Relationships Deleted: {$stats->relationshipsDeleted()}");
        $this->log("Properties Set: {$stats->propertiesSet()}");

        $this->log("Process Graph Relationships completed for $total records");
    }
}