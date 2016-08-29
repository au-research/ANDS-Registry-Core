<?php

namespace ANDS\API\Task\ImportSubTask;

use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class Ingest extends ImportSubTask
{
    public function run_task()
    {
        foreach ($this->parent()->getPayloads() as $path=>$xml) {
            $registryObjects = XMLUtil::getElementsByName($xml, 'registryObject');
            foreach ($registryObjects as $registryObject) {
                $this->insertRegistryObject($registryObject);
            }
        }
    }

    public function insertRegistryObject($registryObject)
    {
        $key = (string) $registryObject->key;
        // check existing one
        if ($matchingRecord = $this->getMatchingRecord($key)) {
            $this->log("Record $key exists. Adding new current version.");

            // deal with previous versions
            RecordData::where('registry_object_id', $matchingRecord->registry_object_id)
                ->update(['current' => '']);

            // add new version in and set it to current
            $newVersion = new RecordData;
            $newVersion->current = true;
            $newVersion->registry_object_id = $matchingRecord->registry_object_id;
            $newVersion->saveData($registryObject->saveXML());
            $newVersion->save();

        } else {
            $this->log("Record $key does not exist. Creating new record and data");
        }
    }

    public function getMatchingRecord($key) {
        $dataSourceDefaultStatus = $this->parent()
            ->getTaskData("dataSourceDefaultStatus");
        $matchingStatusRecords = RegistryObject::where('key', $key)
            ->where('status', $dataSourceDefaultStatus)->take(1)->get()->first();
        return $matchingStatusRecords;
    }
}