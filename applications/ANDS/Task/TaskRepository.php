<?php

namespace ANDS\Task;

use ANDS\API\Task\DoiBulkTask;
use ANDS\API\Task\ImportTask;
use ANDS\API\Task\Task;
use ANDS\Log\Log;

class TaskRepository
{
    public static function getById($id)
    {
        $model = TaskModel::find($id);
        if (! $model) {
            return null;
        }
        return self::getTaskObject($model['attributes']);
    }

    /**
     * obtain an executable task object for a given array
     *
     * @return null|Task
     */
    public static function getTaskObject($taskArray)
    {

        parse_str($taskArray['params'], $params);

        $taskClass = $params['class'];

        switch ($taskClass)
        {
            case "import":
                $className = ImportTask::class;
                break;
            case "DoiBulkTask":
                $className = DoiBulkTask::class;
                break;
            default:
                $className = null;
                break;
        }

        if (! $className || ! class_exists($className)) {
            Log::error(__METHOD__. " Failed creating Task Model for className: $className");
            return null;
        }

        $task = new $className();
        $task->init($taskArray);
        return $task;
    }

    public static function create($data, $resolve = false)
    {
        $task = self::make($data, $resolve);
        return self::save($task);
    }

    public static function make($data, $resolve = false)
    {
        $task = new Task();
        if ($resolve) {
            $task = self::getTaskObject($data);
        }
        $task->init($data);
        return $task;
    }

    public static function save(Task $task)
    {
        $model = $task->getId() ? TaskModel::find($task->getId()) : null;
        if (! $model) {
            $model = new TaskModel();
        }

        $model->fill([
            'name' => $task->getName(),
            'status' => $task->getStatus(),
            'message' => $task->getMessage(),
            'data' => $task->getTaskData(),
            'params' => $task->getParams(),
            'last_run' => $task->getLastRun(),
            'type' => $task->getType()
        ]);

        $model->save();
        $task->setId($model->id);

        return $task;
    }
}