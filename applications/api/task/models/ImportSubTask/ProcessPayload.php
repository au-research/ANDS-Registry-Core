<?php

namespace ANDS\API\Task\ImportSubTask;


use ANDS\Registry\Providers\Quality\Exception;
use ANDS\Registry\Providers\Quality\QualityMetadataProvider;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository as Repo;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\XMLUtil;

class ProcessPayload extends ImportSubTask
{
    protected $requirePayload = true;
    protected $payloadSource = "validated";
    protected $payloadOutput = "processed";
    protected $title = "PROCESSING PAYLOADS";

    public function run_task()
    {
        // remove duplicates
        $this->processDuplicateKeys();

        // verify harvestability for each registryObject
        $this->checkPayloadHarvestability();

    }

    private function processDuplicateKeys()
    {
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
                    $this->parent()->incrementTaskData("missingRegistryObjectKeyCount");
                } elseif (!in_array($key, $keys)) {
                    $processed[] = $registryObject->saveXML();
                    $keys[] = $key;
                } else {
                    // $this->log("Ignored a record already exists in import list: " . $key);
                    $this->parent()->incrementTaskData("duplicateKeyinFeedCount");
                }
            }

            $xmlPayload = implode("", $processed);
            $payload->writeContentByStatus(
                $this->payloadOutput, XMLUtil::wrapRegistryObject($xmlPayload)
            );

            // $this->log('Process stage 1 completed for '. $path);

            if (trim($xmlPayload) == "") {
                $this->log("Payload $path contains no importable records");
                $this->parent()->deletePayload($path);
            }
        }
    }

    /**
     * Returns whether a registryObject SimpleXML should be ingested
     *
     * @param $registryObject
     * @return bool
     */
    public function checkHarvestability(\SimpleXMLElement $registryObject)
    {
        // validate key attributes
        // group
        // originatingSource

        $key = trim((string) $registryObject->key);

        try {
            QualityMetadataProvider::validate($registryObject->saveXML());
        } catch (\Exception $e) {
            $this->addError("Error whilst ingesting record with key " . $key . ": " . get_exception_msg($e));

            if ($e instanceof Exception\MissingGroup) {
                $this->parent()->incrementTaskData("missingGroupAttributeCount");
            } elseif ($e instanceof Exception\MissingOriginatingSource) {
                $this->parent()->incrementTaskData("missingOriginatingSourceCount");
            } elseif ($e instanceof Exception\MissingTitle) {
                $this->parent()->incrementTaskData("missingTitleCount");
            } elseif ($e instanceof Exception\MissingType) {
                $this->parent()->incrementTaskData("missingTypeCount");
            } elseif ($e instanceof Exception\MissingDescriptionForCollection) {
                $this->parent()->incrementTaskData("missingDescriptionCollectionCount");
            }

            // record failed validation, it's draft counterpart must not be deleted
            // this happens for HandleStatusChange pipeline and targetStatus is PUBLISHED
            $draftRecord = RegistryObjectsRepository::getDraftByKey($key);
            $tobeDeleted = $this->parent()->getTaskData('deletedRecords');
            if ($draftRecord && $tobeDeleted) {
                $this->parent()->setTaskData("deletedRecords", collect($tobeDeleted)->filter(function($id) use ($draftRecord){
                    return $id != $draftRecord->id;
                }));
                $this->log("Removed id {$draftRecord->id} from deletion due to $key failed processing");
            };

            return false;
        }

        // check matching data source
        $matchingStatusRecord = Repo::getNotDeletedRecordFromOtherDataSourceByKey($key, $this->parent()->dataSourceID);
        
        if ($matchingStatusRecord) {
            $this->log("Record key:($matchingStatusRecord->key) exists in a different data source");
            $this->parent()->incrementTaskData("recordsExistOtherDataSourceCount");
            return false;
        }
        
        // find the current record data belongs to the record with the same status_group as the dataSourceDefaultStatus
        $matchingStatusRecord = Repo::getMatchingRecord(
            $key, $this->parent()
            ->getTaskData("targetStatus"));

        if ($matchingStatusRecord !== null) {
            $currentRecordData = $matchingStatusRecord->getCurrentData();
            if ($currentRecordData === null) {
                //$this->log("Record key:($matchingStatusRecord->key) does not have current record data");
                return true;
            }

            $hash = $currentRecordData->hash;
            $newHash = md5(XMLUtil::wrapRegistryObject($registryObject->saveXML()));

            if ((string) $hash === (string) $newHash) {
                //$this->log("Record key:($matchingStatusRecord->key) already has a record data matching payload.");
                $this->parent()->addTaskData("harvestedRecordIDs", $matchingStatusRecord->registry_object_id);
                $this->parent()->incrementTaskData("recordsNotUpdatedCount");
                // @todo I can say something here for logging, already exists latest version
                return false;
            }
//            else {
//                $this->log("New record data found for $matchingStatusRecord->key, ($hash and $newHash)");
//            }
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function checkPayloadHarvestability()
    {
        // Let draft records through to save errors
        // DRAFT records comes via manual entry
        if ($this->parent()->getTaskData('targetStatus') === "DRAFT") {
            return;
        }

        foreach ($this->parent()->getPayloads() as &$payload) {
            $path = $payload->getPath();
            $xml = $payload->getContentByStatus($this->payloadOutput);

            $processed = [];

            $registryObjects = XMLUtil::getElementsByName($xml,
                'registryObject');
            foreach ($registryObjects as $registryObject) {
                if ($this->checkHarvestability($registryObject) === true) {
                    $processed[] = $registryObject->saveXML();
                }
            }
            $xmlPayload = implode("", $processed);
            $payload->writeContentByStatus(
                $this->payloadOutput, XMLUtil::wrapRegistryObject($xmlPayload)
            );
            // $this->log('Process stage 2 completed for ' . $path);
        }
    }


}