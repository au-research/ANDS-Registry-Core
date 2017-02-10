<?php


namespace ANDS\API\Task\ImportSubTask;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;

/**
 * Class PopulateAffectedList
 * @package ANDS\API\Task\ImportSubTask
 */
class PopulateAffectedList extends ImportSubTask
{
    protected $requireImportedRecords = true;
    protected $title = "GENERATING AFFECTED LIST";
    private $chunkLimit = 100;

    public function run_task()
    {
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

        $affectedRecordIDs = RelationshipProvider::getAffectedIDsFromIDs($ids, $keys);
        $affectedRecordDuplicatesIDs = [];
        $affectedRecordDuplicatesRecords = RelationshipProvider::getDuplicateRecordsFromIDs($affectedRecordIDs);
        if(count($affectedRecordDuplicatesRecords) > 0){
            foreach($affectedRecordDuplicatesRecords as $record){
                $affectedRecordDuplicatesIDs[] = $record->registry_object_id;
            }

        }

        $affectedRecordIDs = array_merge($affectedRecordIDs, $affectedRecordDuplicatesIDs);

        $affectedRecordIDs = collect($affectedRecordIDs)
            ->flatten()->values()->unique()
            ->toArray();

        $duplicateRecordIDs = RelationshipProvider::getDuplicateRecordsFromIdentifiers($duplicatedIdentifiers);

        if($duplicateRecordIDs){
            $this->parent()->setTaskData("duplicateRecords", $duplicateRecordIDs);
        }

        $countAffected = count($affectedRecordIDs);
        $this->log("Found $countAffected affected records");

        $this->parent()->setTaskData("affectedRecords", $affectedRecordIDs);
    }
}