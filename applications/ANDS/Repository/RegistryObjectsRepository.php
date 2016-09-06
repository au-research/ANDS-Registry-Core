<?php


namespace ANDS\Repository;

use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;

class RegistryObjectsRepository
{
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

    public static function getPublishedByKey($key)
    {
        $importTask = new ImportTask();
        $importTask->init([])->bootEloquentModels();

        return RegistryObject::where('key', $key)
            ->where('status', 'PUBLISHED')->first();
    }
}