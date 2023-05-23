<?php


namespace ANDS\API\Task\ImportSubTask;


use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Repository\RegistryObjectsRepository as Repo;
use ANDS\Util\Config;

class IndexGraphRelationships extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "INDEXING GRAPH RELATIONSHIPS";

    public function run_task()
    {
        $indexed_count = 0;
        $error_count = 0;

        $targetStatus = $this->parent()->getTaskData('targetStatus');
        // TODO: until DRAFT records are 100% isolated in Mycelium we should only allow PUBLISHED records
        if (!Repo::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' No indexing required');
            return;
        }

        debug("INDEXING GRAPH RELATIONSHIPS");
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));

        $targetStatus = $this->parent()->getTaskData('targetStatus');
        // as of Mycelium we are indexing records with any status

        $importedRecords = $this->parent()->getTaskData("importedRecords");
        $total = count($importedRecords);

        $last_record_index = $this->parent()->getTaskData("last_record_index");

        foreach ($importedRecords as $index => $id) {
            // fast-forward to last record if it was set
            if($last_record_index != null && $last_record_index >= $index){
                $this->updateProgress($index, $total, "skipping ($index/$total))");
            }else {
                try {
                    $record = RegistryObjectsRepository::getRecordByID($id);
                    $result = $myceliumClient->indexRecord($record);
                    // it just says done with 200,
                    if ($result->getStatusCode() === 200) {
                        $indexed_count++;
                        // set last_record_index when process ran successfully
                        $this->parent()->setTaskData("last_record_index", $index);
                        $this->parent()->save();
                        debug("Indexed Relationship for record id: $id  #:$indexed_count");
                    } else {
                        $reason = $result->getBody()->getContents();
                        $error_count++;
                        $this->addError("Failed to index record {$id} to mycelium. Reason: $reason");
                        debug("Failed to index record {$id} to mycelium. Reason: $reason");
                    }
                    $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->id)");
                }catch (\Exception $e){
                    $msg = $e->getMessage();
                    $this->addError("Failed to index record {$id} to mycelium. Reason: $msg");
                    debug("Failed to index record {$id} to mycelium. Reason: $msg");
                }
            }
        }
        // unset last_record_index when finished
        $this->parent()->setTaskData("last_record_index", null);
        $this->parent()->save();
        if($indexed_count > 0){
            $this->log("Indexed {$indexed_count} record(s) by mycelium");
        }
        if($error_count > 0){
            $this->log("Failed to Index {$error_count} record(s) by mycelium");
        }
    }

}