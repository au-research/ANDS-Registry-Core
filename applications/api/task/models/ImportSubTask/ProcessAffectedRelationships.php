<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Util\Config;

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
        $sideEffectRequestId = $this->parent()->getTaskData("SideEffectRequestId");
        $this->log("Processing Side Effect QueueID: $sideEffectRequestId");

        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));
        $myceliumClient->startProcessingSideEffectQueue($sideEffectRequestId);

        $requestStatus = null;
        $startTime = microtime(true);
        $elapsed = 0;
        while ($requestStatus != "COMPLETED" && $elapsed < 10000) {
            $now = microtime(true);
            $elapsed = $now - $startTime;
            $result = $myceliumClient->getRequestById($sideEffectRequestId);
            $request = json_decode($result->getBody()->getContents(), true);
            $requestStatus = $request['status'];
            $this->log("Request Status is now $requestStatus, elapsed $elapsed");
            sleep(1);
        }

        $this->log("Processing Side Effect Queue Finished");
    }
}