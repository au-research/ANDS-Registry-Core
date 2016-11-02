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

    public function run_task()
    {
        foreach ($this->parent()->getPayloads() as $payload) {
            $xml = $payload->getContentByStatus('processed');
            if ($xml === null) {
                $this->addError("Processed XML not found for ". $payload->getPath());
                break;
            }
            $registryObjects = XMLUtil::getElementsByName($xml, 'registryObject');
            $total = count($registryObjects);
            foreach ($registryObjects as $index=>$registryObject) {
                $this->insertRegistryObject($registryObject);
                $this->updateProgress(
                    $index, $total,
                    "Processed " . trim((string) $registryObject->key. "($index/$total)")
                );
            }
            $recordsCreatedCount = $this->parent()->getTaskData("recordsCreatedCount");
            $recordsUpdatedCount = $this->parent()->getTaskData("recordsUpdatedCount");
            $this->parent()->updateHarvest(["importer_message" => "Records Created: ".$recordsCreatedCount. ". Records Update: ".$recordsUpdatedCount]);

        }
    }


    public function insertRegistryObject($registryObject)
    {
        $key = trim((string) $registryObject->key);

        // check existing one
        if ($existingRecord = Repo::getMatchingRecord($key, $this->parent()->getTaskData("targetStatus"))) {

            $this->log("Record key:($key) exists with id:($existingRecord->registry_object_id). Adding new current version.");
            $this->parent()->incrementTaskData("recordsUpdatedCount");
            // deal with previous versions
            RecordData::where('registry_object_id', $existingRecord->registry_object_id)
                ->update(['current' => '']);

            // add new version in and set it to current
            $newVersion = Repo::addNewVersion(
                $existingRecord->registry_object_id,
                XMLUtil::wrapRegistryObject(
                    $registryObject->saveXML()
                )
            );

            $this->log("Added new Version :$newVersion->id to existing record");
            $existingRecord->setRegistryObjectAttribute('updated', time());
            $existingRecord->status = $this->parent()->getTaskData("targetStatus");
            $this->parent()->addTaskData("importedRecords", $existingRecord->registry_object_id);

        } elseif (Repo::isPublishedStatus($this->parent()->getTaskData("targetStatus")) &&
                                $deletedRecord = Repo::getDeletedRecord($key)) {
            // deleted records should retain their record IDs only when reinstated directly to PUBLISHED status
            $deletedRecord->status = $this->parent()->getTaskData("targetStatus");
            $this->parent()->incrementTaskData("recordsUpdatedCount");
            // can claim deleted records of different datasources
            $deletedRecord->data_source_id = $this->parent()->dataSourceID;

            $deletedRecord->save();

            // TODO: check if the latest record data is the same first
            // TODO: the matchingRecord is similar, refactor to pull this functionality out

            // deal with previous versions
            RecordData::where('registry_object_id', $deletedRecord->registry_object_id)
                ->update(['current' => '']);

            // add new version in and set it to current
            $newVersion = Repo::addNewVersion(
                $deletedRecord->registry_object_id,
                XMLUtil::wrapRegistryObject(
                    $registryObject->saveXML()
                )
            );
            $this->log("Added new Version:$newVersion->id and reinstated record:".$deletedRecord->registry_object_id);

            $deletedRecord->setRegistryObjectAttribute('updated', time());
            $this->parent()->addTaskData("importedRecords", $deletedRecord->registry_object_id);

        } else {
            $this->log("Record $key does not exist. Creating new record and data");
            $this->parent()->incrementTaskData("recordsCreatedCount");
            //find a deleted record and reinstate it

            // create new record
            $newRecord = new RegistryObject;
            $newRecord->key = $key;
            $newRecord->data_source_id = $this->parent()->dataSourceID;
            $newRecord->status = $this->parent()->getTaskData("targetStatus");
            $newRecord->save();
            $newRecord->setRegistryObjectAttribute('created', time());
            $newRecord->setRegistryObjectAttribute('updated', time());

            // create a new record data
            $newVersion = Repo::addNewVersion(
                $newRecord->registry_object_id,
                XMLUtil::wrapRegistryObject(
                    $registryObject->saveXML()
                )
            );

            $this->log("Record id:$newRecord->registry_object_id created, key:$key with record data: id:$newVersion->id");

            // TODO: add this record to the imported records
            $this->parent()->addTaskData("importedRecords", $newRecord->registry_object_id);
        }
    }

}