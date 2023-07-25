<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Repository\RegistryObjectsRepository as Repo;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class PreserveCoreMetadata extends ImportSubTask
{
    protected $requireHarvestedOrImportedRecords = true;
    public $title = "PRESERVING CORE METADATA";
    protected $preservingAttributeNames = ['record_owner','flag','harvest_id','manually_assessed','modified','updated'];
    public function run_task()
    {
        $this->processUpdatedRecords();
    }

    /**
     * Update importedRecords core metadata
     *
     * @return array
     */
    public function processUpdatedRecords()
    {
        $importedRecords = $this->parent()->getTaskData("importedRecords");

        if ($importedRecords === false || $importedRecords === null) {
            return;
        }

        if(!(Repo::isPublishedStatus($this->parent()->getTaskData("targetStatus")))){
            return;
        }

        foreach ($importedRecords as $index => $roID) {
            $this->log('Copying metadata for record: ' . $roID);

            $record = RegistryObject::find($roID);

            if(!$record || !(Repo::isPublishedStatus($record->status))){
                return;
            }

            $draftRecord = Repo::getMatchingRecord($record->key, "DRAFT");

            if(!$draftRecord){
              return;
            }

            foreach ($this->preservingAttributeNames as $attributeName) {
                $attributeValue = $draftRecord->getRegistryObjectAttributeValue($attributeName);
                if($attributeValue){
                    $record->setRegistryObjectAttribute($attributeName, $attributeValue);
                }
            }
            $record->save();
        }
    }
}