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
    //protected $timeLimit = 3600;

    public function run_task()
    {

        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));
        $import_count = 0;
        $error_count = 0;
        $startTime = microtime(true);
        $targetStatus = $this->parent()->getTaskData('targetStatus');
        if (!RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' does not process graph network');
            return;
        }
        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);
        
        // create a new Mycelium Request
        $sideEffectRequestId = $this->parent()->getTaskData("SideEffectRequestId");
        if ($sideEffectRequestId == null) {
            $result = $myceliumClient->createNewAffectedRelationshipRequest();
            $request = json_decode($result->getBody()->getContents(), true);
            $this->log("Affected Relationship Request created with id: ".$request['id']);
            $sideEffectRequestId = $request['id'];
            $this->parent()->setTaskData("SideEffectRequestId", $sideEffectRequestId);
        }

        foreach ($importedRecords as $index => $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            $result = $myceliumClient->importRecord($record, $sideEffectRequestId);

            if ($result->getStatusCode() === 200) {
                $import_count++;
                //$this->log("Imported record {$record->id} to mycelium");
                debug("Imported record {$record->id} to mycelium");
            } else {
                $error_count++;
                $reason = $result->getBody()->getContents();
                $this->addError("Failed to import record {$record->id} to mycelium. Reason: $reason");
                debug("Failed to import record {$record->id} to mycelium. Reason: $reason");
            }
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->id)");
            debug("Processed ($index/$total) $record->title($record->id)");
        }
        debug("Process Graph Relationships completed for $import_count records");
        $this->log("Process Graph Relationships completed for $import_count records");
        if($error_count > 0){
            $this->log("Failed to process Graph Relationships for $error_count records");
        }
    }
}