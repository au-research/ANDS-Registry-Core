<?php
/**
 * Class:  ImportTask
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;

use \Exception as Exception;

/**
 * Class ImportTask
 * Import a payload into the registry
 * Contains a pipeline that houses several ImportSubTask
 * That can run in sequences to provide better control over the data
 * and improve synchronisity
 *  *
 * @package ANDS\API\Task
 */
class ImportTask extends Task
{

    public $dataSourceID;
    public $batchID;
    private $payload;
    private $subtasks;

    public function run_task()
    {
        $this->log('Import Task started');
        $this->loadParams();
        $this->loadSubtasks();
        $nextTask = $this->getNextTask();

        if ($nextTask === null) {
            $this->setStatus("COMPLETED");
            return true;
        }

        $task = $this->constructTaskObject($nextTask);
        try {
            $this->log("Running task". $nextTask['name']);
            $task->run_task();
        } catch (Exception $e) {
            $task->stoppedWithError($e->getMessage());
            throw new Exception($e->getMessage());
        } catch (NonFatalException $e) {
            $task->addError($e->getMessage());
        }
    }

    public function constructTaskObject($task)
    {
        $className = 'ANDS\\API\\Task\\ImportSubTask\\' . $task['name'];
        try {
            $taskObject = new $className;
            $taskObject->setParentTask($this);
            return $taskObject;
        } catch (Exception $e) {
            $task->stoppedWithError($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function loadSubTasks()
    {
        if ($this->getTaskData('subtasks')) {
            $this->setSubtasks($this->getTaskData('subtasks'));
        } else {
            $this->setSubtasks($this->getDefaultImportSubtasks());
        }
    }

    public function getNextTask()
    {
        $pendings = array_filter($this->getSubtasks(), function($task) {
            return $task['status'] == "PENDING";
        });
        return array_first($pendings);
    }

    public function getDefaultImportSubtasks()
    {
        $pipeline = [];
        $defaultSubtasks = ["PopulateImportOptions", "ValidatePayload"];
        foreach ($defaultSubtasks as $subtaskName) {
            $pipeline[] = [
                'name' => $subtaskName,
                'status' => "PENDING"
            ];
        }
        return $pipeline;
    }

    /**
     * @param $payload
     * @return mixed
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $payload;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     *
     */
    public function loadParams()
    {
        parse_str($this->getParams(), $parameters);
        $this->dataSourceID = $parameters['ds_id'] ? : null;
        $this->batchID = $parameters['batch_id'] ? : null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubtasks()
    {
        return $this->subtasks;
    }

    /**
     * @param mixed $subtasks
     * @return $this
     */
    public function setSubtasks($subtasks)
    {
        $this->subtasks = $subtasks;
        return $this;
    }
}