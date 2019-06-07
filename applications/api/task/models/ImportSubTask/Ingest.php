<?php

namespace ANDS\API\Task\ImportSubTask;

use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository as Repo;

class Ingest extends ImportSubTask
{
    protected $requirePayload = true;
    protected $title = "INGESTING RECORDS";
    protected $data_source = null;
    
    public function run_task()
    {
        $payloads = $this->parent()->getPayloads();
        $multiplePayloads = count($payloads) > 1 ? true : false;
        $this->data_source = DataSourceRepository::getByID($this->parent()->dataSourceID);
        $payloadCounter = 0;
        foreach ($this->parent()->getPayloads() as $payloadIndex => $payload) {
            $payloadCounter++;
            $xml = $payload->getContentByStatus('processed');
            if ($xml === null) {
                $this->addError("Processed XML not found for ". $payload->getPath());
                break;
            }
            $registryObjects = XMLUtil::getElementsByName($xml, 'registryObject');
            $total = count($registryObjects);
            foreach ($registryObjects as $index => $registryObject) {
                $this->insertRegistryObject($registryObject);
                if (!$multiplePayloads) {
                    $this->updateProgress(
                        $index, $total,
                        "Processed registryObject ($index/$total) " . trim((string) $registryObject->key)
                    );
                }
            }
            if ($multiplePayloads) {
                $this->updateProgress(
                    $payloadCounter, count($payloads),
                    "Processed payload ($payloadCounter/".count($payloads).") " . $payloadIndex
                );
            }
        }

        $recordsCreatedCount = $this->parent()->getTaskData("recordsCreatedCount");
        $recordsUpdatedCount = $this->parent()->getTaskData("recordsUpdatedCount");
        $this->parent()->updateHarvest([
            "importer_message" => "Records Created: ".$recordsCreatedCount. ". Records Update: ".$recordsUpdatedCount
        ]);

    }


    public function insertRegistryObject($registryObject)
    {
        $key = trim((string) $registryObject->key);

        // check existing one
        if ($existingRecord = Repo::getMatchingRecord($key, $this->parent()->getTaskData("targetStatus"))) {

            // $this->log("Record key:($key) exists with id:($existingRecord->registry_object_id). Adding new current version.");

            $this->parent()->incrementTaskData("recordsUpdatedCount");
            // deal with previous versions
            RecordData::where('registry_object_id', $existingRecord->registry_object_id)
                ->update(['current' => 'FALSE']);

            // add new version in and set it to current
            $newVersion = Repo::addNewVersion(
                $existingRecord->registry_object_id,
                XMLUtil::wrapRegistryObject(
                    $registryObject->saveXML()
                )
            );

            // $this->log("Added new Version :$newVersion->id to existing record");
            $existingRecord->setRegistryObjectAttribute('updated', time());
            $user_name = $this->parent()->getTaskData("userName");

            if($user_name == null) {
                $user_name = "SYSTEM";
            }

            $existingRecord->setRegistryObjectAttribute('created_who', $user_name);
            if($this->data_source->getDataSourceAttributeValue('qa_flag') == true){
                $existingRecord->setRegistryObjectAttribute('manually_assessed', 'no');
            }
            $existingRecord->status = $this->parent()->getTaskData("targetStatus");
            $existingRecord->save();
            $this->parent()->addTaskData("importedRecords", $existingRecord->registry_object_id);
            $this->parent()->addTaskData("imported_".$existingRecord->class."_ids", $existingRecord->registry_object_id);
            $this->parent()->addTaskData("imported_".$existingRecord->class."_keys", $existingRecord->key);
            

        } elseif (Repo::isPublishedStatus($this->parent()->getTaskData("targetStatus")) &&
                                $deletedRecord = Repo::getDeletedRecord($key)) {
            // deleted records should retain their record IDs only when reinstated directly to PUBLISHED status
            $deletedRecord->status = $this->parent()->getTaskData("targetStatus");
            $this->parent()->incrementTaskData("recordsUpdatedCount");
            // can claim deleted records of different datasources

            $deletedRecord->data_source_id = $this->parent()->dataSourceID;

            $deletedRecord->save();

            $user_name = $this->parent()->getTaskData("userName");

            if($user_name == null) {
                $user_name = "SYSTEM";
            }

            $deletedRecord->setRegistryObjectAttribute('created_who', $user_name);

            if($this->data_source->getDataSourceAttributeValue('qa_flag') == 1){
                $deletedRecord->setRegistryObjectAttribute('manually_assessed', 'no');
            }

            // TODO: check if the latest record data is the same first
            // TODO: the matchingRecord is similar, refactor to pull this functionality out

            // deal with previous versions
            RecordData::where('registry_object_id', $deletedRecord->registry_object_id)
                ->update(['current' => 'FALSE']);

            // add new version in and set it to current
            $newVersion = Repo::addNewVersion(
                $deletedRecord->registry_object_id,
                XMLUtil::wrapRegistryObject(
                    $registryObject->saveXML()
                )
            );
            // $this->log("Added new Version:$newVersion->id and reinstated record:".$deletedRecord->registry_object_id);

            $deletedRecord->setRegistryObjectAttribute('updated', time());
            $this->parent()->addTaskData("importedRecords", $deletedRecord->registry_object_id);
            $this->parent()->addTaskData("imported_".$deletedRecord->class."_ids", $deletedRecord->registry_object_id);
            $this->parent()->addTaskData("imported_".$deletedRecord->class."_keys", $deletedRecord->key);

        } else {
            $xml = XMLUtil::wrapRegistryObject($registryObject->saveXML());
            // $this->log("Record $key does not exist. Creating new record and data");
            $this->parent()->incrementTaskData("recordsCreatedCount");
            // create new record
            $newRecord = new RegistryObject;
            $newRecord->key = $key;
            $newRecord->class = XMLUtil::getRegistryObjectClass($xml);
            $newRecord->data_source_id = $this->parent()->dataSourceID;
            $newRecord->status = $this->parent()->getTaskData("targetStatus");
            
            $user_name = $this->parent()->getTaskData("userName");
            
            if($user_name == null) {
                $user_name = "SYSTEM";
            }
            $newRecord->record_owner = $user_name;
            $newRecord->save();
            if($this->data_source->getDataSourceAttributeValue('qa_flag') == 1){
                $newRecord->setRegistryObjectAttribute('manually_assessed', 'no');
            }

            $newRecord->setRegistryObjectAttribute('created_who', $user_name);
            $newRecord->setRegistryObjectAttribute('created', time());
            $newRecord->setRegistryObjectAttribute('updated', time());


            // create a new record data
            $newVersion = Repo::addNewVersion($newRecord->registry_object_id, $xml);

            // $this->log("Record id:$newRecord->registry_object_id created, key:$key with record data: id:$newVersion->id");

            // TODO: add this record to the imported records
            $this->parent()->addTaskData("importedRecords", $newRecord->registry_object_id);
            $this->parent()->addTaskData("imported_".$newRecord->class."_ids", $newRecord->registry_object_id);
            $this->parent()->addTaskData("imported_".$newRecord->class."_keys", $newRecord->key);
        }
    }

}