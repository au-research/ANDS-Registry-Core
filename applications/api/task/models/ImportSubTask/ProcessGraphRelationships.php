<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use ANDS\Repository\RegistryObjectsRepository as Repo;

class ProcessGraphRelationships extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING GRAPH RELATIONSHIPS";

    /** @var int time limit in seconds */
    //protected $timeLimit = 3600;

    public function run_task()
    {


        $targetStatus = $this->parent()->getTaskData('targetStatus');
        // TODO: until DRAFT records are 100% isolated in Mycelium we should only allow PUBLISHED records
        if (!Repo::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' No indexing required');
            return;
        }

        $myceliumUrl = Config::get('mycelium.url');
        $myceliumClient = new MyceliumServiceClient($myceliumUrl);

        if (!$myceliumClient->ping()) {
            $this->addError("Failed to contact Mycelium at $myceliumUrl. ProcessGraphRelationship is skipped");
            return;
        }

        $import_count = 0;
        $error_count = 0;
        $startTime = microtime(true);


        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);
        
        // create a new Mycelium Request
        $myceliumRequestId = $this->parent()->getTaskData("myceliumRequestId");
        if ($myceliumRequestId == null) {
            $result = $myceliumClient->createNewImportRecordRequest($this->parent()->getBatchID());
            $request = json_decode($result->getBody()->getContents(), true);
            $myceliumRequestId = $request['id'];
            $this->log("Mycelium Request created ID: $myceliumRequestId");
            $this->parent()->setTaskData("myceliumRequestId", $myceliumRequestId);
        }
        $last_record_index = $this->parent()->getTaskData("last_record_index");

        foreach ($importedRecords as $index => $id) {
            // fast-forward to last record if it was set
            if($last_record_index != null && $last_record_index >= $index){
                $this->updateProgress($index, $total, "skipping ($index/$total))");
            }else {
                $record = RegistryObjectsRepository::getRecordByID($id);
                $result = $myceliumClient->importRecord($record, $myceliumRequestId);
                if ($result->getStatusCode() === 200) {
                    $import_count++;
                    // set last_record_index when process ran successfully
                    $this->parent()->setTaskData("last_record_index", $index);
                    $this->parent()->save();
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
        }
        // unset last_record_index when finished
        $this->parent()->setTaskData("last_record_index", null);
        $this->parent()->save();
        debug("Process Graph Relationships completed for $import_count records");
        $this->log("Process Graph Relationships completed for $import_count records");
        if($error_count > 0){
            $this->log("Failed to process Graph Relationships for $error_count records");
        }
    }
}