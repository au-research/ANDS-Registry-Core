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

    public function run_task()
    {

        $ingestedRocordCount = 0;
        $dataSource = DataSourceRepository::getByID($this->parent()->dataSourceID);
        if (!$dataSource) {
            $this->stoppedWithError("Data Source ".$this->parent()->dataSourceID." Not Found");
            return;
        }
        $this->parent()->updateHarvest(['status'=>'INGESTING RECORDS']);

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
            $recordsCreatedCount = $this->parent()->getTaskData("recordsCreatedCount");
            $recordsUpdatedCount = $this->parent()->getTaskData("recordsUpdatedCount");
            $this->parent()->updateHarvest(["importer_message" => "Records Created: ".$recordsCreatedCount. "Records Update: ".$recordsUpdatedCount]);
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
        if ($matchingRecord = Repo::getMatchingRecord($key, $this->parent()->getTaskData("targetStatus"))) {
            $this->log("Record key:($key) exists with id:($matchingRecord->registry_object_id). Adding new current version.");
            $this->parent()->incrementTaskData("recordsUpdatedCount");
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

            $matchingRecord->setRegistryObjectAttribute('updated', time());
            $matchingRecord->status = $this->parent()->getTaskData("targetStatus");
            $this->parent()->addTaskData("importedRecords", $matchingRecord->registry_object_id);

        } elseif ($deletedRecord = Repo::getDeletedRecord($key)) {

            $deletedRecord->status = $this->parent()->getTaskData("targetStatus");
            $this->parent()->incrementTaskData("recordsUpdatedCount");
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

            $deletedRecord->setRegistryObjectAttribute('updated', time());
            $this->parent()->addTaskData("importedRecords", $deletedRecord->registry_object_id);

        } else {
            $this->log("Record $key does not exist. Creating new record and data");
            $this->parent()->incrementTaskData("recordsCreatedCount");
            //find a deleted record and reinstate it

            // create new record
            $ro = new RegistryObject;
            $ro->key = $key;
            $ro->data_source_id = $this->parent()->dataSourceID;
            $ro->status = $this->parent()->getTaskData("targetStatus");
            $ro->save();
            $ro->setRegistryObjectAttribute('created', time());
            $ro->setRegistryObjectAttribute('updated', time());

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


}