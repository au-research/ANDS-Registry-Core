<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Cache\Cache;
use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Registry\Providers\GraphRelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
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
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));

        $startTime = microtime(true);
        $targetStatus = $this->parent()->getTaskData('targetStatus');
        if (!RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' does not process graph network');
            return;
        }
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);
        foreach ($importedRecords as $index => $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            $result = $myceliumClient->importRecord($record);
            if ($result->getStatusCode() === 200) {
                $this->log("Imported record {$record->id} to mycelium");
            } else {
                $reason = $result->getBody()->getContents();
                $this->addError("Failed to import record {$record->id} to mycelium. Reason: $reason");
            }
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->id)");
        }

        // importedRecords should already be ordered by ProcessRelationship
//        $importedRecords = $this->parent()->getTaskData("importedRecords");
//        $total = count($importedRecords);
//
//        $this->log("Process Graph Relationships started for $total records");
//        $stats = new CombinedStatistics();
//        foreach ($importedRecords as $index => $id) {
//
//            $elapsed = microtime(true) - $startTime;
//            if ($elapsed > $this->timeLimit) {
//                $this->addError("Elapsed: {$elapsed}s. Task has run for more than {$this->timeLimit} seconds. Terminating... Processed ($index/$total)");
//                break;
//            }
//
//            $record = RegistryObjectsRepository::getRecordByID($id);
//            try {
//                /** @var CombinedStatistics $statistics */
//                $stat = GraphRelationshipProvider::process($record);
//
//                $stats->mergeStats($stat);
//
//            } catch (\Exception $e) {
//                $this->addError("Error processing graph relationships for {$id}: ". get_exception_msg($e));
//            }
//            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->registry_object_id)");
//        }
//
//        $this->log("Nodes Created: {$stats->nodesCreated()}");
//        $this->log("Nodes Deleted: {$stats->nodesDeleted()}");
//        $this->log("Relationships Created: {$stats->relationshipsCreated()}");
//        $this->log("Relationships Deleted: {$stats->relationshipsDeleted()}");
//        $this->log("Properties Set: {$stats->propertiesSet()}");

        $this->log("Process Graph Relationships completed for $total records");
    }
}