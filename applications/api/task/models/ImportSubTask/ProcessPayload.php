<?php

namespace ANDS\API\Task\ImportSubTask;


use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class ProcessPayload extends ImportSubTask
{
    protected $requirePayload = true;

    public function run_task()
    {
        // remove duplicates
        $keys = [];
        foreach ($this->parent()->getPayloads() as $path=>$xml) {
            $processed = [];
            $registryObjects = XMLUtil::getElementsByName($xml, 'registryObject');
            foreach ($registryObjects as $registryObject) {
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
            $payload = implode("", $processed);
            if ($payload) {
                $this->parent()->setPayload($path, XMLUtil::wrapRegistryObject($payload));
            } else {
                $this->log("Payload $path contains no importable records");
                $this->parent()->deletePayload($path);
            }
        }

        // verify harvestability for each registryObject
        foreach ($this->parent()->getPayloads() as $path=>$xml) {
            $processed = [];
            $registryObjects = XMLUtil::getElementsByName($xml, 'registryObject');
            foreach ($registryObjects as $registryObject) {
                if ($this->checkHarvestability($registryObject)){
                    $processed[] = $registryObject->saveXML();
                }
            }
            $payload = implode("", $processed);
            $this->parent()->setPayload($path, XMLUtil::wrapRegistryObject($payload));
            // @todo write _processed.xml
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
        $dataSourceDefaultStatus = $this->parent()
            ->getTaskData("dataSourceDefaultStatus");
        $matchingStatusRecords = RegistryObject::where('key', $key)
            ->where('status', $dataSourceDefaultStatus)->take(1)->get()->first();

        if ($matchingStatusRecords !== null) {
            $currentRecordData = $matchingStatusRecords->data->filter(function($value){
                return $value->current == "TRUE";
            })->first();

            $hash = $currentRecordData->hash;
            $newHash = md5(XMLUtil::wrapRegistryObject($registryObject->saveXML()));

            if ($hash === $newHash) {
                // @todo I can say something here for logging, already exists latest version
                return false;
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