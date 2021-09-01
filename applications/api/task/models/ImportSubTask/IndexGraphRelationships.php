<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;

class IndexGraphRelationships extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "INDEXING GRAPH RELATIONSHIPS";

    public function run_task()
    {
        $indexed_count = 0;
        $error_count = 0;
        debug("INDEXING GRAPH RELATIONSHIPS");
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));

        $targetStatus = $this->parent()->getTaskData('targetStatus');
        if (!RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' does not process graph network');
            return;
        }

        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

        foreach ($importedRecords as $index => $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            $result = $myceliumClient->indexRecord($record);
            // it just says done with 200,
            if ($result->getStatusCode() === 200) {
                $indexed_count++;
                debug("Indexed Relationship for record id: $id  #:$indexed_count");
            } else {
                $reason = $result->getBody()->getContents();
                $error_count++;
                $this->addError("Failed to index record {$id} to mycelium. Reason: $reason");
                debug("Failed to index record {$id} to mycelium. Reason: $reason");
            }
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->id)");
        }
        if($indexed_count > 0){
            $this->log("Indexed {$indexed_count} record(s) by mycelium");
        }
        if($error_count > 0){
            $this->log("Failed to Index {$error_count} record(s) by mycelium");
        }
    }

}