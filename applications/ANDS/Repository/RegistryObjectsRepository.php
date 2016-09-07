<?php

namespace ANDS\Repository;

use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;
use ANDS\RegistryObjectAttribute;
use ANDS\RegistryObject\Metadata;
use ANDS\RegistryObject\Identifier;
use ANDS\RegistryObject\Relationship;
use ANDS\RecordData;

class RegistryObjectsRepository
{
    /**
     * Delete a single record by ID
     * uses ProcessDelete task to complete the job
     * Does not give more information than true or false
     *
     * @param $id
     * @return bool
     */
    public static function deleteRecord($id)
    {
        $importTask = new ImportTask();
        $importTask->init([])->bootEloquentModels();

        $importTask
            ->setTaskData('deletedRecords', [$id])
            ->setTaskData('subtasks', [['name'=>'ProcessDelete', 'status'=>'PENDING']])
            ->initialiseTask();
        $deleteTask = $importTask->getTaskByName('ProcessDelete');
        $deleteTask->run();

        if ($deleteTask->hasError()) {
            return false;
        }

        return true;
    }

    /**
     * Completely erase the existence of a record by key
     *
     * @param $key
     */
    public static function completelyEraseRecord($key)
    {
        $records = RegistryObject::where('key', $key)->get();
        foreach ($records as $record) {

            // delete attributes
            RegistryObjectAttribute::where('registry_object_id', $record->registry_object_id)->delete();

            // delete record_data
            RecordData::where('registry_object_id', $record->registry_object_id)->delete();

            // delete identifiers
            Identifier::where('registry_object_id', $record->registry_object_id)->delete();

            // delete metadata
            Metadata::where('registry_object_id', $record->registry_object_id)->delete();

            //delete relationship
            Relationship::where('registry_object_id', $record->registry_object_id)->delete();

            // delete record
            $record->delete();

            // TODO: delete Portal and Relation index
        }
    }

    /**
     * Get the published version of a record by key
     *
     * @param $key
     * @return mixed
     */
    public static function getPublishedByKey($key)
    {
        return self::getByKeyAndStatus($key, 'PUBLISHED');
    }

    /**
     * Useful function to get record by key and status
     *
     * @param $key
     * @param string $status
     * @return mixed
     */
    public static function getByKeyAndStatus($key, $status = "PUBLISHED")
    {
        $importTask = new ImportTask();
        $importTask->init([])->bootEloquentModels();

        return RegistryObject::where('key', $key)->where('status', $status)->first();
    }


}