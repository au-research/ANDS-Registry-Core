<?php
/**
 * Class:  ImportTask
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;

use ANDS\API\Task\ImportSubTask\ImportSubTask;
use ANDS\DataSource;
use ANDS\DataSource\Harvest as Harvest;
use ANDS\Util\NotifyUtil;
use Illuminate\Database\Capsule\Manager as Capsule;
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

    use ManagePayload;

    public $dataSourceID;
    public $batchID;
    public $harvestID;
    public $runAll = false;

    private $subtasks = [];

    /**
     * @Overwrite
     */
    public function run_task()
    {
        $this->log('Import Task started');

        $this->initialiseTask();

        if ($this->runAll) {
            foreach ($this->getSubtasks() as $task){
                if ($this->getStatus() !== "STOPPED") {
                    $nextTask = $this->constructTaskObject($task);
                    $this->runSubTask($nextTask);
                    $this->saveSubTaskData($nextTask);
                }
            }
        } else {
            $nextTask = $this->getNextTask();
            if ($nextTask === null) {
                $this->setStatus("COMPLETED");
                return true;
            }
            $this->runSubTask($nextTask);
            $this->saveSubTaskData($nextTask);
        }
        $this->saveSubTasks();
        return true;
    }

    public function saveSubTaskData($taskObject)
    {
        foreach ($this->subtasks as &$task) {
            if ($task['name'] === $taskObject->name) {
                $task = $taskObject->toArray();
            }
        }
    }

    public function hook_end()
    {
        if ($this->getStatus() === "STOPPED") {
            return;
        }

        if ($nextTask = $this->getNextTask()) {
            $this->setStatus("PENDING")->save();
        }
    }

    /**
     * Run a specific SubTask
     * @param $subTask
     * @throws Exception
     */
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
     * @param $taskData
     * @return mixed
     * @throws Exception
     * @internal param $name
     * @internal param $task
     */
    public function constructTaskObject($taskData)
    {
        $name = $taskData['name'];
        if (!is_string($name)) return $name;
        $className = 'ANDS\\API\\Task\\ImportSubTask\\' . $name;
        if (!class_exists($className)) {
            throw new Exception("Class ". $className. " not found");
        }
        try {
            $taskObject = new $className;
            $taskObject->setParentTask($this);
            $taskObject->init($taskData);
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
        $loadedSubTasks = [];
        foreach ($subTasks as $task) {
            $taskData = $task;
            $task = $this->constructTaskObject($taskData);
            $loadedSubTasks[] = $task->toArray();
        }
        $this->setSubtasks($loadedSubTasks);

        return $this;
    }

    /**
     * Requires loadTasks to be done first
     * @param $name
     * @return ImportSubTask
     */
    public function getTaskByName($name)
    {
        foreach ($this->getSubtasks() as $task) {
            if ($task['name'] === $name) {
                return $this->constructTaskObject($task);
            }
        }
        return null;
    }

    /**
     *
     */
    public function saveSubTasks()
    {
        $this->setTaskData("subtasks", $this->subtasks);
    }

    /**
     * Returns the next task to be performed
     * null if there is no next task
     * @return mixed
     */
    public function getNextTask()
    {
        $pendings = array_filter($this->getSubtasks(), function($task) {
            return $task['status'] == "PENDING";
        });

        $firstPendingTask = array_first($pendings);
        if ($firstPendingTask) {
            $taskObject = $this->constructTaskObject($firstPendingTask);
            return $taskObject;
        }

        return null;
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
        $defaultSubtasks = [
            "PopulateImportOptions",
            "ValidatePayload",
            "ProcessPayload",
            "Ingest",
            "ProcessCoreMetadata",
            "HandleRefreshHarvest",
            "ProcessDelete",
            "ProcessIdentifiers",
            "ProcessRelationships",
            "ProcessQualityMetadata",
            "IndexPortal",
            "OptimizeRelationship",
            "FinishImport"
        ];

        foreach ($defaultSubtasks as $subtaskName) {
            $pipeline[] = [
                'name' => $subtaskName,
                'status' => "PENDING"
            ];
        }
        return $pipeline;
    }

    /**
     * Set a respective subtasks pipeline for this importTask
     *
     * @param $pipeline
     * @return $this
     */
    public function setPipeline($pipeline)
    {
        switch($pipeline) {
            case "PublishingWorkflow":
                $this->setTaskData('subtasks',
                    [
                        ['name' => "HandleStatusChange", 'status' => 'PENDING']
                    ]
                );
                break;
            case "DeletingWorkflow":
                $this->setTaskData('subtasks',
                    [
                        ['name' => 'ProcessDelete', 'status' => 'PENDING']
                        // TODO: Remove Index of affected records
                        // TODO: FixRelationship of affected records
                    ]
                );
                break;
            case "UpdateRelationshipWorkflow":
                $this->setTaskData('subtasks',
                    [
                        ['name' => 'ProcessRelationships', 'status' => 'PENDING'],
                        ['name' => 'OptimizeRelationship', 'status' => 'PENDING']
                    ]
                );
                break;
            case "ErrorWorkflow":
                $this->setTaskData('subtasks',
                    [
                        ['name' => 'PopulateImportOptions', 'status' => 'PENDING'],
                        ['name' => 'FinishImport', 'status' => 'PENDING']
                    ]
                );

                // error will not load payloads
                $this->skipLoadingPayload();
                $this->setTaskData("skipLoadingPayload", true);
                break;
            default:
                $this->setTaskData('subtasks', $this->getDefaultImportSubtasks());
                break;
        }
        return $this;
    }

    /**
     * Prime the task for execution
     *
     * @return $this
     */
    public function initialiseTask()
    {
        $this
            ->bootEloquentModels()
            ->loadParams();

        if ($this->harvestID) {
            $this->checkHarvesterMessages();
        }

        $this->loadSubTasks();

        if ($this->skipLoading === false) {
            $this->loadPayload();
        }

        return $this;
    }

    /**
     * Boot all eloquent model
     * Set the default connection to match the default CI connection
     * TODO: (soon) remove reference of CI here because it is not needed
     * TODO: (soon) move boot eloquent model to database.php autoload
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
            [ 'subtasks' => $this->subtasks ]
        );
    }

    /**
     * Return the subtasks as array
     *
     * @return array
     */
    public function subTasksArray()
    {
        $result = [];
        foreach ($this->getSubtasks() as $task) {
            if (get_class($task) == ImportSubTask::class) {
                $result[] = $task->toArray();
            }
        }
        return $result;
    }

    /**
     * Load the parameters defined in the task to be reused
     *
     * @return $this
     */
    public function loadParams()
    {
        parse_str($this->getParams(), $parameters);

        if ($this->dataSourceID === null) {
            $this->dataSourceID = array_key_exists('ds_id', $parameters) ? $parameters['ds_id']: null;
        }

        $this->batchID = array_key_exists('batch_id', $parameters) ? $parameters['batch_id'] : null;
        $this->harvestID = array_key_exists('harvest_id', $parameters) ? $parameters['harvest_id'] : null;

        $this->setTaskData(
            'dataSourceID',
            array_key_exists('ds_id', $parameters) ? $parameters['ds_id']: null
        );

        $this->setTaskData(
            'batchID',
            array_key_exists('batch_id', $parameters) ? $parameters['batch_id'] : null
        );

        $this->setTaskData(
            'harvestID',
            array_key_exists('harvest_id', $parameters) ? $parameters['harvest_id'] : null
        );

        if (array_key_exists('runAll', $parameters)) {
            $this->enableRunAllSubTask();
        }

        foreach ($parameters as $key => $value) {
            $this->setTaskData($key, $value);
        }

        if ($this->getTaskData('skipLoadingPayload')) {
            $this->skipLoadingPayload();
        }

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

    /**
     * Remove a specific subtask from the pipeline by name
     *
     * @param $name
     */
    public function removeSubtaskByname($name)
    {
        $this->subtasks = array_filter($this->subtasks, function($task) use ($name){
           return $task['name'] !== $name;
        });
    }

    /**
     * Set a flag to run all tasks synchronously
     *
     * @return $this
     */
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
        $this->setTaskData('batchID', $batchID);
        return $this;
    }

    public function updateHarvest($args)
    {
        Harvest::where('harvest_id', $this->harvestID)->update($args);
        NotifyUtil::notify(
            "datasource.".$this->dataSourceID.'.harvest',
            json_encode(Harvest::find($this->harvestID), true)
        );
    }

    public function checkHarvesterMessages()
    {
        $harvest = Harvest::where('harvest_id', $this->harvestID)->first();
        if ($harvest === null) {
            return;
        }

        $message = json_decode($harvest->getMessage(), true);
        if (is_string($message)) {
            $message = json_decode($message, true);
        }

        if ($message === null) {
            return;
        }

        if (array_key_exists('error', $message) && $message['error']['errored'] === true) {
            $this->setPipeline("ErrorWorkflow");
            $this->addError($message['error']['log']);
        }
    }


    public function stoppedWithError($message)
    {
        $this->updateHarvest(['status' => 'STOPPED', 'importer_message'=> $message, 'message' => '']);
        if ($dataSource = DataSource::find($this->dataSourceID)) {
            $dataSource->appendDataSourceLog("IMPORT STOPPED WITH ERROR". NL . $message, "error", "IMPORTER");
        }
        parent::stoppedWithError($message);
    }

    /**
     * @param mixed $dataSourceID
     * @return ImportTask
     */
    public function setDataSourceID($dataSourceID)
    {
        $this->dataSourceID = $dataSourceID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataSourceID()
    {
        return $this->dataSourceID;
    }

    /**
     * @return mixed
     */
    public function getBatchID()
    {
        return $this->batchID;
    }

    /**
     * @return mixed
     */
    public function getHarvestID()
    {
        return $this->harvestID;
    }

    /**
     * @param mixed $harvestID
     * @return $this
     */
    public function setHarvestID($harvestID)
    {
        $this->harvestID = $harvestID;
        return $this;
    }
}