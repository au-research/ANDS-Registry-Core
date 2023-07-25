<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class PopulateAffectedList
 * @package ANDS\API\Task\ImportSubTask
 */
class PopulateAffectedList extends ImportSubTask
{
    protected $requireImportedRecords = true;
    public $title = "GENERATING AFFECTED LIST";
    private $chunkLimit = 400;

    public function run_task()
    {
        $targetStatus = $this->parent()->getTaskData('targetStatus');
        if (!RegistryObjectsRepository::isPublishedStatus($targetStatus)) {
            $this->log("Target status is ". $targetStatus.' does not affect records');
            return;
        }

        $ids = [];
        $keys = [];
        $identifiers = [];
        $duplicatedIdentifiers = [];
        foreach (['party', 'activity', 'collection', 'service'] as $class) {
            if ($this->parent()->getTaskData("imported_".$class."_ids")) {
                $ids = array_merge(
                    $ids, $this->parent()->getTaskData("imported_".$class."_ids")
                );
                $keys = array_merge(
                    $keys, $this->parent()->getTaskData("imported_".$class."_keys")
                );
            }
        }


        foreach (['party', 'activity', 'collection', 'service'] as $class) {
            if ($this->parent()->getTaskData("imported_".$class."_identifiers"))
            {
                $classIdentifiers = $this->parent()->getTaskData("imported_".$class."_identifiers");

                foreach( $classIdentifiers as $idx => $identifier)
                {

                    if(in_array($identifier, $identifiers)){
                        $duplicatedIdentifiers[] = $identifier;
                    }
                    else{
                        $identifiers[] = $identifier;
                    }
                }
            }
        }

        // try importedRecords if the imported_$class_ids are not populated
        // this is the case for ManualImport pipeline
        if (sizeof($ids) == 0) {
            $importedRecords = $this->parent()->getTaskData('importedRecords');
            $ids = $importedRecords;
            $keys = RegistryObject::whereIn('registry_object_id', $ids)
                ->get()->pluck('key')->toArray();
        }

        $total = count($ids);

        $this->log("Getting affectedIDs for $total records");

        $affectedRecordIDs = [];

        for($start = 0 ; $start <= sizeof($ids); $start += $this->chunkLimit)
        {
            $result = RelationshipProvider::getAffectedIDsFromIDs(array_slice($ids, $start , $this->chunkLimit), array_slice($keys, $start , $this->chunkLimit));
            if(is_array($result))
                $affectedRecordIDs = array_merge($affectedRecordIDs, $result);
        }

        $affectedRecordIDs = collect($affectedRecordIDs)
            ->flatten()->values()->unique()
            ->toArray();

        $affectedRecordDuplicatesIDs = [];

        for($start = 0 ; $start <= sizeof($affectedRecordIDs); $start += $this->chunkLimit)
        {
            $affectedRecordDuplicatesRecords = RelationshipProvider::getDuplicateRecordsFromIDs(array_slice($affectedRecordIDs, $start , $this->chunkLimit));
            if(count($affectedRecordDuplicatesRecords) > 0){
                foreach($affectedRecordDuplicatesRecords as $record){
                    $affectedRecordDuplicatesIDs[] = $record->registry_object_id;
                }

            }
        }

        if(sizeof($affectedRecordDuplicatesIDs)>0){
            $affectedRecordIDs = array_merge($affectedRecordIDs, $affectedRecordDuplicatesIDs);
        }

        $affectedRecordIDs = collect($affectedRecordIDs)
            ->flatten()->values()->unique()
            ->toArray();

        $duplicateRecordIDs = [];

        for($start = 0 ; $start <= sizeof($duplicatedIdentifiers); $start += $this->chunkLimit)
        {
            $result = RelationshipProvider::getDuplicateRecordsFromIdentifiers(array_slice($duplicatedIdentifiers, $start , $this->chunkLimit));
            if(is_array($result))
                $duplicateRecordIDs = array_merge($duplicateRecordIDs, $result);
        }

        $duplicateRecordIDs= collect($duplicateRecordIDs)
            ->flatten()->values()->unique()
            ->toArray();


        if(sizeof($duplicateRecordIDs) > 0){
            $this->parent()->setTaskData("duplicateRecords", $duplicateRecordIDs);
        }

        $countAffected = count($affectedRecordIDs);
        $this->log("Found $countAffected affected records");

        // values
        $affectedRecordIDs = array_values($affectedRecordIDs);

        $this->parent()->setTaskData("affectedRecords", $affectedRecordIDs);
    }
}