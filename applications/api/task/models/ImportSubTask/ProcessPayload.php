<?php

namespace ANDS\API\Task\ImportSubTask;


use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class ProcessPayload extends ImportSubTask
{
    protected $requirePayload = true;
    protected $payloadSource = "validated";
    protected $payloadOutput = "processed";

    public function run_task()
    {

        // remove duplicates
        $keys = [];
        foreach ($this->parent()->getPayloads() as &$payload) {

            $path = $payload->getPath();
            $xml = $payload->getContentByStatus($this->payloadSource);

            $processed = [];
            $registryObjects = XMLUtil::getElementsByName($xml, 'registryObject');
            foreach ($registryObjects as $registryObject) {
                $this->parent()->incrementTaskData("recordsInFeedCount");
                $key = trim((string) $registryObject->key);
                if ($key == '') {
                    $this->log("Error whilst ingesting record, 'key' must have a value");
                } elseif (!in_array($key, $keys)) {
                    $processed[] = $registryObject->saveXML();
                    $keys[] = $key;
                } else {
                    $this->log("Ignored a record already exists in import list: " . $key);
                }
            }
            $xmlPayload = implode("", $processed);
            if ($xmlPayload) {
                $payload->writeContentByStatus(
                    $this->payloadOutput, XMLUtil::wrapRegistryObject($xmlPayload)
                );
                $this->log('Process stage 1 completed for '. $path);
            } else {
                $this->log("Payload $path contains no importable records");
                $this->parent()->deletePayload($path);
            }
        }

        // verify harvestability for each registryObject
        foreach ($this->parent()->getPayloads() as &$payload) {

            $path = $payload->getPath();
            $xml = $payload->getContentByStatus($this->payloadOutput);

            $processed = [];


            $registryObjects = XMLUtil::getElementsByName($xml, 'registryObject');
            foreach ($registryObjects as $registryObject) {
                if ($this->checkHarvestability($registryObject) === true){
                    $processed[] = $registryObject->saveXML();
                }
            }
            $xmlPayload = implode("", $processed);
            $payload->writeContentByStatus(
                $this->payloadOutput, XMLUtil::wrapRegistryObject($xmlPayload)
            );
            $this->log('Process stage 2 completed for '. $path);
        }

    }

    /**
     * Returns whether a registryObject SimpleXML should be ingested
     * @todo refactor into sub procedure
     *
     * @param $registryObject
     * @return bool
     */
    public function checkHarvestability($registryObject)
    {
        // validate key attributes
        // group
        // originatingSource

        $key = trim((string) $registryObject->key);

        if ((string)$registryObject->originatingSource == '') {
            $this->log("Error whilst ingesting record with key " . $key . ": " . "Registry Object 'originatingSource' must have a value");
            return false;
        }

        if ((string)$registryObject['group'] == '') {
            $this->log("Error whilst ingesting record with key " . $key . ": " .  "Registry Object '@group' must have a value");
            return false;
        }

        // find the current record data belongs to the record with the same status as the dataSourceDefaultStatus
        $targetStatus = $this->parent()
            ->getTaskData("targetStatus");
        $matchingStatusRecord = RegistryObject::where('key', $key)
            ->where('status', $targetStatus)
            ->first();

        if ($matchingStatusRecord !== null) {
            $this->parent()->addTaskData("harvestedRecordIDs", $matchingStatusRecord->registry_object_id);
            $currentRecordData = $matchingStatusRecord->getCurrentData();

            if ($currentRecordData === null) {
                $this->log("Record key:($matchingStatusRecord->key) does not have current record data");
                return true;
            }

            $hash = $currentRecordData->hash;
            $newHash = md5(XMLUtil::wrapRegistryObject($registryObject->saveXML()));

            // check matching data source
            if ($matchingStatusRecord->data_source_id != $this->parent()->dataSourceID) {
                $this->log("Record key:($matchingStatusRecord->key) exists in a different data source");
                $this->parent()->incrementTaskData("recordsExistOtherDataSourceCount");
                return false;
            }

            if ((string) $hash === (string) $newHash) {
                $this->log("Record key:($matchingStatusRecord->key) already has a record data matching payload.");
                $this->parent()->incrementTaskData("recordsNotUpdatedCount");
                // @todo I can say something here for logging, already exists latest version
                return false;
            } else {
                $this->log("New record data found for $matchingStatusRecord->key, ($hash and $newHash)");
            }

        }



        return true;

        // @todo move to XMLUtilTest
        //get the key, check for existing registryObject with this key
//        $key = XMLUtil::getElementsByXPath(
//            XMLUtil::wrapRegistryObject(
//                $registryObject->saveXML()),
//                "/ro:registryObjects/ro:registryObject/ro:key"
//        );
    }



}