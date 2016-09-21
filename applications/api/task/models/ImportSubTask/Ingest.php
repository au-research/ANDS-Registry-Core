<?php

namespace ANDS\API\Task\ImportSubTask;

use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class Ingest extends ImportSubTask
{
    protected $requirePayload = true;

    public function run_task()
    {
        foreach ($this->parent()->getPayloads() as $payload) {
            $xml = $payload->getContentByStatus('processed');
            if ($xml === null) {
                $this->addError("Processed XML not found for ". $payload->getPath());
                break;
            }
            $registryObjects = XMLUtil::getElementsByName($xml, 'registryObject');
            foreach ($registryObjects as $registryObject) {
                $this->insertRegistryObject($registryObject);
            }
        }

        $this->handleAdvancedHarvest($payload);
    }

    public function handleAdvancedHarvest()
    {
        /*
         * if it's REFRESH, delete every harvest that is not manually entered and has a different batch id
         *
         */
    }

    public function insertRegistryObject($registryObject)
    {
        $key = trim((string) $registryObject->key);

        // check existing one
        if ($matchingRecord = $this->getMatchingRecord($key)) {
            $this->log("Record key:($key) exists with id:($matchingRecord->registry_object_id). Adding new current version.");

            // deal with previous versions
            RecordData::where('registry_object_id', $matchingRecord->registry_object_id)
                ->update(['current' => '']);

            // add new version in and set it to current
            $newVersion = $this->addNewVersion(
                $matchingRecord->registry_object_id,
                XMLUtil::wrapRegistryObject(
                    $registryObject->saveXML()
                )
            );
            $this->log("Added new Version :$newVersion->id to existing record");

            $matchingRecord->setRegistryObjectAttribute('modified', time());

            $this->parent()->addTaskData("importedRecords", $matchingRecord->registry_object_id);

        } elseif ($deletedRecord = $this->getDeletedRecord($key)) {

            $deletedRecord->status = $this->parent()->getTaskData("targetStatus");
            $deletedRecord->save();

            // TODO: check if the latest record data is the same first
            // TODO: the matchingRecord is similar, refactor to pull this functionality out

            // deal with previous versions
            RecordData::where('registry_object_id', $deletedRecord->registry_object_id)
                ->update(['current' => '']);

            // add new version in and set it to current
            $newVersion = $this->addNewVersion(
                $deletedRecord->registry_object_id,
                XMLUtil::wrapRegistryObject(
                    $registryObject->saveXML()
                )
            );
            $this->log("Added new Version:$newVersion->id and reinstated record:".$deletedRecord->registry_object_id);

            $deletedRecord->setRegistryObjectAttribute('modified', time());

            $this->parent()->addTaskData("importedRecords", $deletedRecord->registry_object_id);

        } else {
            $this->log("Record $key does not exist. Creating new record and data");

            //find a deleted record and reinstate it

            // create new record
            $ro = new RegistryObject;
            $ro->key = $key;
            $ro->data_source_id = $this->parent()->dataSourceID;
            $ro->status = $this->parent()->getTaskData("targetStatus");
            $ro->save();
            $ro->setRegistryObjectAttribute('created', time());

            // create a new record data
            $newVersion = $this->addNewVersion(
                $ro->registry_object_id,
                XMLUtil::wrapRegistryObject(
                    $registryObject->saveXML()
                )
            );

            $this->log("Record id:$ro->registry_object_id created, key:$key with record data: id:$newVersion->id");

            // TODO: add this record to the imported records
            $this->parent()->addTaskData("importedRecords", $ro->registry_object_id);
        }
    }

    /**
     * TODO: refactor to RecordDataRepository
     * @param $registryObjectID
     * @param $xml
     * @return RecordData
     */
    public function addNewVersion($registryObjectID, $xml)
    {
        $newVersion = new RecordData;
        $newVersion->current = true;
        $newVersion->registry_object_id = $registryObjectID;
        $newVersion->timestamp = time();
        $newVersion->saveData($xml);
        $newVersion->save();
        return $newVersion;
    }

    public function getMatchingRecord($key)
    {
        $targetStatus = $this->parent()
            ->getTaskData("targetStatus");
        $matchingStatusRecords = RegistryObject::where('key', $key)
            ->where('status', $targetStatus)->first();
        return $matchingStatusRecords;
    }

    public function getDeletedRecord($key)
    {
        $deletedRecord = RegistryObject::where('key', $key)
            ->where('status', 'DELETED')
            ->first();
        return $deletedRecord;
    }
}