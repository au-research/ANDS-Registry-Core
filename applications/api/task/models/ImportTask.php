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
                $this->saveSubTasks();
                $this->save();
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
        $this->save();
    }

    public function hook_end()
    {
        if ($this->getStatus() === "STOPPED") {
            return;
        }

        if ($nextTask = $this->getNextTask()) {
            $this->setStatus("PENDING")->save();
        }

        if ($this->getStatus() == "COMPLETED") {
            $this->writeLog("ImportCompleted");
        }
    }

    /**
     * Uses the global monolog functionality to write to import.log
     * to content of this task
     *
     * @param string $event
     */
    public function writeLog($event = "ImportCompleted")
    {
        /*monolog([
            'event' => $event,
            'data' => $this->toArray()
        ], 'import');*/
    }

    /**
     * Run a specific SubTask
     * @param $subTask
     * @throws Exception
     */
    public function runSubTask($subTask)
    {
        try {
            $this->log("Running task". $subTask->name)->save();
            $subTask->run();
        } catch (Exception $e) {
            $subTask->stoppedWithError($e->getMessage());
            $this->stoppedWithError($e->getMessage());
            $this->saveSubTaskData($subTask);
            $this->saveSubTasks();
            // throw new Exception($e->getMessage());
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
        // does not have any subtasks
        if (!$this->getTaskData('subtasks')) {
            $this->setPipeline("default");
        }

        $subTasks = $this->getTaskData('subtasks');

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
     * Set a respective subtasks pipeline for this importTask
     *
     * @param $pipeline
     * @return $this
     */
    public function setPipeline($pipeline = "default")
    {
        switch($pipeline) {
            case "ManualImport":
                $tasks = [
                    "PopulateImportOptions",
                    "ValidatePayload",
                    "ProcessPayload",
                    "Ingest",
                    "ProcessCoreMetadata",
                    "ProcessIdentifiers",
                    "ProcessRelationships",
                    "ProcessQualityMetadata",
                    "IndexPortal",
                    "IndexRelationship",
                    "FinishImport",
                ];
                break;
            case "PublishingWorkflow":
                $tasks = [
                    "HandleStatusChange",
                    "ValidatePayload",
                    "ProcessPayload",
                    "Ingest",
                    "ProcessCoreMetadata",
                    "PreserveCoreMetadata",
                    "ProcessDelete",
                    "ProcessIdentifiers",
                    "ProcessRelationships",
                    "ProcessQualityMetadata",
                    "IndexPortal",
                    "IndexRelationship",
                    "FinishImport",
                ];
                break;
            case "UpdateRelationshipWorkflow":
                $tasks = [
                    "ProcessRelationships",
                    "IndexRelationship"
                ];
                break;
            case "ErrorWorkflow":
                $tasks = [
                    "PopulateImportOptions",
                    "FinishImport",
                    "ScheduleHarvest"
                    //"Report"
                ];
                // error will not load payloads
                $this->skipLoadingPayload();
                $this->setTaskData("skipLoadingPayload", true);
                break;
            case "default":
            default:
                $tasks = [
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
                    "IndexRelationship",
                    //"OptimizeRelationship",
                    //"HandleIncrementalHarvest",
                    "FinishImport",
                    "ScheduleHarvest"
                    //"Report"
                ];
                break;
        }

        $pipelineTasks = [];
        foreach ($tasks as $task) {
            $pipelineTasks[] = [
                'name' => $task,
                'status' => "PENDING"
            ];
        }

        $this->setTaskData('pipeline', $pipeline);
        $this->setTaskData('subtasks', $pipelineTasks);

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

        if ($this->getTaskData('pipeline') !== null && !$this->getTaskData('subtasks')) {
            $this->setPipeline($this->getTaskData('pipeline'));
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
        if (!$this->harvestID) {
            return;
        }
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
        $this->updateHarvest([
            'importer_message'=> $message
        ]);

        if ($dataSource = DataSource::find($this->dataSourceID)) {
            $dataSource->appendDataSourceLog($this->name." Stopped with Error". NL . $message, "error", "IMPORTER");
        }

        $source = $this->getTaskData('source');
        if ($source == 'url' || $source == 'xml') {
            $this->setTaskData('dataSourceLog', $message.NL.$this->getDataSourceMessage());
        }

        // if this is a harvest, run FinishImportTask and then ScheduleHarvest
        if ($source == 'harvester') {
            $nextTask = $this->getTaskByName("FinishImport");
            $nextTask->disableLoggingToDatasourceLogs();
            $this->runSubTask($nextTask);
            $this->saveSubTaskData($nextTask);

            $nextTask = $this->getTaskByName("ScheduleHarvest");
            $this->runSubTask($nextTask);
            $this->saveSubTaskData($nextTask);
        }

        $this->writeLog("ImportStopped");

        parent::stoppedWithError($message);
    }

    public function getDataSourceMessage()
    {
        $targetStatus = $this->getTaskData("targetStatus");
        $selectedKeys = [
            "dataSourceDefaultStatus" => "Default Import Status for Data Source",
            "targetStatus" => "Target Status for Import",
            "recordsInFeedCount" => "Valid Records Received in Harvest",
            "invalidRegistryObjectsCount" => "Failed to Validate",
            "duplicateKeyinFeedCount" => "Duplicated Records",
            "recordsExistOtherDataSourceCount" => "Record exist in other Datasource(s)",
            "missingRegistryObjectKeyCount" => "Invalid due to Missing key",
            "missingOriginatingSourceCount" => "Invalid due to missing OriginatingSource",
            "missingGroupAttributeCount" => "Invalid missing group Attribute",
            "recordsCreatedCount" => "New Records Created",
            "recordsUpdatedCount" => "Records updated",
            "refreshHarvestStatus" => "Refresh Harvest Status",
            "recordsNotUpdatedCount" => "Records content unchanged",
            "recordsDeletedCount" => "Records deleted",
            "datasourceRecordBeforeCount" => "Number of " . $targetStatus . " records Before Import",
            "datasourceRecordAfterCount" => "Number of " . $targetStatus . " records After Import",
            "url" => "URL"
        ];

        $message = [];
        $message[] = "Batch ID: ".$this->batchID;
        $message[] = "Time: ".date("Y-m-d\TH:i:s\Z", time());
        if ($this->getId()) {
            $message[] = "TaskID: ".$this->getId();
        }

        foreach ($selectedKeys as $key => $title) {
            $taskData = $this->getTaskData($key);
            if($taskData !== 0 && $taskData !== null && $taskData != "") {
                $message[] = $title . ": " . $taskData;
            }
        }

        if ($errorList = $this->getError()) {
            $message[] = NL."Error: ";
            foreach ($errorList as $error) {
                $message[] = $error;
            }
        }


        $message = implode(NL, $message);
        return $message;
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

    /**
     * Benchmark data of all subtasks
     *
     * @return array
     */
    public function getBenchmarkData()
    {
        $benchmark = [];
        foreach($this->getSubtasks() as $subtask) {
            if (array_key_exists('benchmark', $subtask['data'])) {
                $benchmark[$subtask['name']] = $subtask['data']['benchmark'];
            }
        }
        return $benchmark;
    }
}