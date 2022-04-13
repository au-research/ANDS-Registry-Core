<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;

class ProcessGraphRelationships extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "PROCESSING GRAPH RELATIONSHIPS";

    /** @var int time limit in seconds */
    //protected $timeLimit = 3600;

    public function run_task()
    {

        $myceliumUrl = Config::get('mycelium.url');
        $myceliumClient = new MyceliumServiceClient($myceliumUrl);

        if (!$myceliumClient->ping()) {
            $this->addError("Failed to contact Mycelium at $myceliumUrl. ProcessGraphRelationship is skipped");
            return;
        }

        $import_count = 0;
        $error_count = 0;
        $startTime = microtime(true);
        $targetStatus = $this->parent()->getTaskData('targetStatus');
        // as of Mycelium we are indexing records with any status

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

        foreach ($importedRecords as $index => $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            $result = $myceliumClient->importRecord($record, $myceliumRequestId);

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