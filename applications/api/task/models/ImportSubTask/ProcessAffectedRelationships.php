<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Util\Config;
use ANDS\Repository\RegistryObjectsRepository as Repo;

/**
 * Class ProcessAffectedRelationships
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessAffectedRelationships extends ImportSubTask
{
    public $title = "PROCESSING AFFECTED RELATIONSHIPS";

    public function run_task()
    {


        $targetStatus = $this->parent()->getTaskData('targetStatus');
        // TODO: until DRAFT records are 100% isolated in Mycelium we should only allow PUBLISHED records
        if (!Repo::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' No indexing required');
            return;
        }
        $myceliumRequestId = $this->parent()->getTaskData("myceliumRequestId");

        // requires sideEffectRequestId to continue
        if (!$myceliumRequestId) {
            $this->log("myceliumRequestId required for this task");
            return;
        }

        $this->log("Processing RequestID: $myceliumRequestId");

        $myceliumUrl = Config::get('mycelium.url');
        $myceliumClient = new MyceliumServiceClient($myceliumUrl);

        // requires Mycelium service being online to continue
        if (!$myceliumClient->ping()) {
            $this->addError("Failed to contact Mycelium at $myceliumUrl. ProcessAffectedRelationships is skipped");
            return;
        }
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        // Pass on Imported Records, so they won't be included in the affected processing
        $myceliumClient->startProcessingSideEffectQueue($myceliumRequestId, $importedRecords);

        $requestStatus = null;
        $startTime = microtime(true);

        // TODO implement a better progress checking, consider remove elapsed for make the value larger
        // setting limit to 5 minutes = 300 seconds
        $elapsed = 0;
        while ($requestStatus != "COMPLETED") {
            $now = microtime(true);
            $elapsed = $now - $startTime;
            $result = $myceliumClient->getRequestById($myceliumRequestId);
            $request = json_decode($result->getBody()->getContents(), true);
            $requestStatus = $request['status'];
            if(is_array($request['summary']) && isset($request['summary']['total'])){
                $total = $request['summary']['total'];
                $processed = $request['summary']['processed'];
                $this->updateProgress($processed, $total, "Processed ($processed/$total)");
                $this->log("Processed $processed/$total, elapsed $elapsed");
            }
            sleep(1);
        }

        $this->log("Processing Affected Relationships Finished");
    }
}