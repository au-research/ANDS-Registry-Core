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
            if ($result->getStatusCode() === 200) {
                $this->log("Indexed record {$record->id} to mycelium");
            } else {
                $reason = $result->getBody()->getContents();
                $this->addError("Failed to index record {$record->id} to mycelium. Reason: $reason");
            }
            $this->updateProgress($index, $total, "Processed ($index/$total) $record->title($record->id)");
        }
    }

}