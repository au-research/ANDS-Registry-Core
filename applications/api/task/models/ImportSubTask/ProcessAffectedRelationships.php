<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Util\Config;

/**
 * Class ProcessAffectedRelationships
 * @package ANDS\API\Task\ImportSubTask
 */
class ProcessAffectedRelationships extends ImportSubTask
{
    protected $title = "PROCESSING AFFECTED RELATIONSHIPS";

    public function run_task()
    {
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

        $myceliumClient->startProcessingSideEffectQueue($myceliumRequestId);

        $requestStatus = null;
        $startTime = microtime(true);

        // TODO implement a better progress checking, consider remove elapsed for make the value larger
        // setting limit to 5 minutes = 300 seconds
        $elapsed = 0;
        while ($requestStatus != "COMPLETED" && $elapsed < 300) {
            $now = microtime(true);
            $elapsed = $now - $startTime;
            $result = $myceliumClient->getRequestById($myceliumRequestId);
            $request = json_decode($result->getBody()->getContents(), true);
            $requestStatus = $request['status'];
            $this->log("Request Status is now $requestStatus, elapsed $elapsed");
            sleep(1);
        }

        $this->log("Processing Affected Relationships Finished");
    }
}