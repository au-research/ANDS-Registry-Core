<?php
/**
 * Class:  ImportTask
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;

use ANDS\API\Task\ImportSubTask\ImportSubTask;
use Illuminate\Database\Capsule\Manager as Capsule;
use \Exception as Exception;

use \Illuminate\Container\Container as Container;
use \Illuminate\Support\Facades\Facade as Facade;

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
    private $payloads = [];
    private $subtasks;

    private $runAll = false;

    public function run_task()
    {
        $this->log('Import Task started');
        $this->bootEloquentModels();
        $this->loadParams();
        $this->loadSubtasks();

        if ($this->runAll) {
            while ($task = $this->getNextTask()) {
                $this->runSubTask($task);
            }
        } else {
            $nextTask = $this->getNextTask();
            if ($nextTask === null) {
                $this->setStatus("COMPLETED");
                return true;
            }
            $this->runSubTask($nextTask);
        }

        $this->saveSubTasks();
    }

    public function runSubTask($subTask)
    {
        try {
            $this->log("Running task". $subTask->name);
            $subTask->run();
        } catch (Exception $e) {
            $subTask->stoppedWithError($e->getMessage());
            throw new Exception($e->getMessage());
        } catch (NonFatalException $e) {
            $subTask->addError($e->getMessage());
        }
    }

    /**
     * Returns a task object
     *
     * @param $name
     * @return mixed
     * @throws Exception
     * @internal param $task
     */
    public function constructTaskObject($name)
    {
        if (!is_string($name)) return $name;
        $className = 'ANDS\\API\\Task\\ImportSubTask\\' . $name;
        if (!class_exists($className)) {
            throw new Exception("Class ". $className. " not found");
        }
        try {
            $taskObject = new $className;
            $taskObject->setParentTask($this);
            return $taskObject;
        } catch (Exception $e) {
            $this->stoppedWithError($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Load the subTasks to be performed by this import task
     * If there's no existing subtasks or none defined in the task data,
     * load a set of default sub tasks
     *
     * Existing subtasks will have existing subtask details
     */
    public function loadSubTasks()
    {
        $subTasks = $this->getTaskData('subtasks') ?: $this->getDefaultImportSubtasks();

        /**
         * Load all the subtask as task object
         * Reason for this is to allow access to task internal data
         * like message, log and status
         */
        foreach ($subTasks as &$task) {
            $taskData = $task;
            $task = $this->constructTaskObject($taskData['name']);
            $task->init($taskData);
        }
        $this->setSubtasks($subTasks);
    }

    /**
     * Requires loadTasks to be done first
     * @param $name
     * @return ImportSubTask
     */
    public function getTaskByName($name)
    {
        foreach ($this->getSubtasks() as $task) {
            if (($task->name) === $name) {
                return $task;
            }
        }
        return null;
    }

    public function saveSubTasks()
    {
        $this->setSubtasks($this->getSubtasks());
    }

    /**
     * Returns the next task to be performed
     * null if there is no next task
     * @return mixed
     */
    public function getNextTask()
    {
        $pendings = array_filter($this->getSubtasks(), function($task) {
            return $task->status == "PENDING";
        });
        return array_first($pendings);
    }

    /**
     * Returns a default list of task for an Import Task
     * Can be overwriten if required
     *
     * @return array
     */
    public function getDefaultImportSubtasks()
    {
        $pipeline = [];
        $defaultSubtasks = ["PopulateImportOptions", "ValidatePayload", "ProcessPayload", "Ingest"];
        foreach ($defaultSubtasks as $subtaskName) {
            $pipeline[] = [
                'name' => $subtaskName,
                'status' => "PENDING"
            ];
        }
        return $pipeline;
    }

    public function initialiseTask()
    {
        $this->bootEloquentModels()->loadParams()->loadSubTasks();
    }

    /**
     * Boot all eloquent model
     * Set the default connection to match the default CI connection
     * TODO: (soon) remove reference of CI here because it is not needed
     * @return $this
     */
    public function bootEloquentModels()
    {
        $ci =& get_instance();
        $capsule = new Capsule;
        $capsule->addConnection(
            [
                'driver' => 'mysql',
                'host' => $ci->db->hostname,
                'database' => $ci->db->database,
                'username' => $ci->db->username,
                'password' => $ci->db->password,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
            ], 'default'
        );
        $capsule->setAsGlobal();
        $capsule->getConnection('default');
        $capsule->bootEloquent();
        return $this;
    }

    /**
     * @Override
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [ 'subtasks' => $this->subTasksArray() ]
        );
    }

    public function subTasksArray()
    {
        $result = [];
        foreach ($this->getSubtasks() as $task) {
            $result[] = $task->toArray();
        }
        return $result;
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setPayload($key, $value)
    {
        $this->payloads[$key] = $value;
        return $this;
    }

    /**
     * @param bool $key
     * @return mixed
     */
    public function getPayload($key = false)
    {
        return array_key_exists($key, $this->payloads) ? $this->payloads[$key] : null;
    }

    public function getFirstPayload()
    {
        return array_first($this->payloads);
    }

    public function getPayloads()
    {
        return $this->payloads;
    }

    public function deletePayload($key)
    {
        unset($this->payloads[$key]);
        return $this;
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

    public function enableRunAllSubTask()
    {
        $this->runAll = true;
        return $this;
    }

    /**
     * @param mixed $batchID
     * @return ImportTask
     */
    public function setBatchID($batchID)
    {
        $this->batchID = $batchID;
        return $this;
    }
}