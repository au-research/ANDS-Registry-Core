<?php
/**
 * Class:  Task
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;


use ANDS\Util\NotifyUtil;
use Symfony\Component\Stopwatch\Stopwatch;
use \Exception as Exception;

class Task
{
    private $id;
    public $name;
    public $status;
    public $priority;
    public $params;
    public $lastRun;
    public $message = ['log' => [], 'error' => []];
    public $taskData = [];
    private $db;
    private $memoryLimit = '256M';
    private $dateFormat = 'Y-m-d | h:i:sa';
    public $dateAdded;

    /**
     * Intialisation of this task
     * @param $task
     * @return $this
     */
    function init($task)
    {
        $this->id = isset($task['id']) ? $task['id'] : false;
        $this->name = isset($task['name']) ? $task['name'] : false;
        $this->status = isset($task['status']) ? $task['status'] : false;
        $this->priority = isset($task['priority']) ? $task['priority'] : false;
        $this->params = isset($task['params']) ? $task['params'] : false;
        if (isset($task['data'])) {
            $this->taskData = is_array($task['data']) ? $task['data'] : json_decode($task['data'], true);
        }
        if (isset($task['message'])) {
            $this->message = is_array($task['message']) ? $task['message'] : json_decode($task['message'], true);
        } else {
            $this->message = ['log' => [], 'error' => []];
        }

        $this->lastRun = isset($task['last_run']) ? $task['last_run'] : false;
        $this->dateAdded = array_key_exists('date_added', $task) ? $task['date_added'] : null;

        $this->dateFormat = 'Y-m-d | h:i:sa';

        return $this;
    }

    /**
     * Primary task running function
     * @return $this
     */
    public function run()
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start($this->getName());
        $start = microtime(true);

        $this->hook_start();

//        if ($this->getStatus() === "STOPPED") {
//            $this->log("Task is STOPPED");
//            return;
//        }

        $this
            ->setStatus('RUNNING')
            ->setLastRun(date('Y-m-d H:i:s', time()))
            ->log("Task run at " . date($this->dateFormat, $start))
            ->save();

        // high memory limit and execution time prep for big tasks
        // web server can still reclaim worker thread and terminate PHP script execution
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 2 * ONE_HOUR);
        set_time_limit(0);
        ignore_user_abort(true);

        //overwrite this method
        try {
            $this->run_task();
        } catch (Exception $e) {
            $message = $e->getMessage();
            if (!$message) {
                $message = implode(" ", array_first($e->getTrace())['args']);
            }
            $this->stoppedWithError($message);
        }

        $event = $stopwatch->stop($this->getName());

        $this->setTaskData("benchmark", [
            'origin' => $event->getOrigin(),
            'duration' => $event->getDuration(),
            'duration_seconds' => $event->getDuration() / 1000,
            'memory' => $event->getMemory(),
            'memory_mb' => $event->getMemory() / 1048576
        ]);

        try {
            $this->finalize($start);
        }
        catch (Exception $e){
            $stderr = fopen('php://stderr','a');
            fwrite($stderr,'Unable to Finalise Task' . $e->getMessage());
            exit(1);
        }
        return $this;
    }

    public function finalize($start)
    {

        try {
            $end = microtime(true);
            if ($this->getStatus() !== "STOPPED") {
                $this->setStatus('COMPLETED');
            } else {
                $this->log("Task completed with error");
            }
            $this
                ->log("Task finished at " . date($this->dateFormat, $end))
                ->log("Peak memory usage: " . memory_get_peak_usage() . " bytes")
                ->log("Took: " . $this->formatPeriod($end, $start))
                ->save();
            $this->hook_end();
        }
        catch(Exception $e){
            throw new Exception($e);
        }
    }

    /**
     * Add a log message to the internal log array
     * Can chain save() after
     * @param $log
     * @return $this
     */
    public function log($log)
    {
        $this->message['log'][] = $log;
        if ($this->getId()) {
            NotifyUtil::notify('task.'.$this->getId(), $log);
        }
        return $this;
    }

    public function getLog()
    {
        return array_key_exists('log', $this->message) ? $this->message['log'] : null;
    }

    public function getError()
    {
        return array_key_exists('error', $this->message) ? $this->message['error'] : null;
    }

    public function hasError()
    {
        $error = $this->getError();
        if ($error != null && count($error) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Stop a task when an error is encountered
     * Log the error and save
     * @param $error
     * @return $this
     */
    public function stoppedWithError($error)
    {
        $this
            ->setStatus("STOPPED")
            ->log("Task stopped with error: " . $error)
            ->addError($error)
            ->save();
        return $this;
    }

    public function addError($log)
    {
        if (!array_key_exists('error', $this->message)) $this->message['error'] = [];
        if (!in_array($log, $this->message['error'])) {
            $this->message['error'][] = $log;
        }
        return $this;
    }


    public function setTaskData($key, $val)
    {
        $this->taskData[$key] = $val;
        return $this;
    }

    public function clearTaskData()
    {
        $this->taskData = [];
        return $this;
    }

    public function addTaskData($key, $val)
    {
        if (array_key_exists($key, $this->taskData)) {
            $this->taskData[$key][] = $val;
        } else {
            $this->taskData[$key] = [$val];
        }
    }

    public function incrementTaskData($key, $value = 1)
    {
        if (!array_key_exists($key, $this->taskData)) {
            $this->taskData[$key] = (integer)$value;
        } else {
            $this->taskData[$key] = (integer)$this->taskData[$key] + (integer)$value;
        }
        return $this;
    }

    public function getTaskData($key = null)
    {
        if ($key === null) {
            return $this->taskData;
        }

        return array_key_exists($key, $this->taskData) ? $this->taskData[$key] : null;
    }


    public function printTaskData()
    {
        $message = "DETAILS:".NL;
        foreach($this->taskData as $key => $value){
            $message .= $key.": ".$value.NL;
        }
        return $message;
    }
    /**
     * Helper method
     * Format a time period nicely
     * @param $endtime
     * @param $starttime
     * @return string
     */
    private function formatPeriod($endtime, $starttime)
    {
        $duration = $endtime - $starttime;
        $hours = (int)($duration / 60 / 60);
        $minutes = (int)($duration / 60) - $hours * 60;
        $seconds = (int)$duration - $hours * 60 * 60 - $minutes * 60;
        return ($hours == 0 ? "00" : $hours) . ":" . ($minutes == 0 ? "00" : ($minutes < 10 ? "0" . $minutes : $minutes)) . ":" . ($seconds == 0 ? "00" : ($seconds < 10 ? "0" . $seconds : $seconds));
    }

    /**
     * Save the status and the message of this task
     * @return Task
     */
    public function save()
    {
        $data = [
            'status' => $this->status,
            'priority' => $this->priority,
            'message' => json_encode($this->message),
            'data' => json_encode($this->taskData)
        ];

        if ($this->getLastRun()) $data['last_run'] = $this->getLastRun();

        if ($this->getId() === false || $this->getId() == "") {
            // $this->log('This task does not have an ID, does not save');
            return true;
        }
        $updateStatus = null;
        try {
            $updateStatus = $this->update_db($data);
        }
        catch(Exception $e){
            // if we can't update the database throwing exception and
            // trying to finalize will just get into an infinite loop!!
            // just print error and die
            $stderr = fopen('php://stderr','a'); //both (a)ppending, and (w)riting will work
            fwrite($stderr,'Task data failed to update to the database' . $e->getMessage());
            exit(1);
        }

//        NotifyUtil::notify(
//            $channel = "task.".$this->getId(),
//            json_encode($this->toArray(), true)
//        );

        return $this;
    }

    public function sendToBackground()
    {
        if ($this->getId()) {
            return;
        }

        $params = [];

        if ($this instanceof ImportTask) {
            $params['class'] = 'import';
            $params['ds_id'] = $this->getDataSourceID();
            if ($this->getBatchID()) {
                $params['batch_id'] = $this->getBatchID();
            }
            if ($this->getHarvestID()) {
                $params['harvest_id'] = $this->getHarvestID();
            }
            if ($this->skipLoading) {
                $params['skipLoadingPayload'] = true;
            }
            if ($this->runAll) {
                $params['runAll'] = true;
            }
            if ($this->getTaskData('pipeline')) {
                $params['pipeline'] = $this->getTaskData('pipeline');
            }
        }

        $task = [
            'name' => $this->getName(),
            'priority' => 5,
            'status' => 'PENDING',
            'type' => "PHPSHELL",
            'params' => http_build_query($params),
            'message' => json_encode($this->message),
            'data' => json_encode($this->taskData),
        ];

        $taskResult = TaskManager::create($this->db, $this->ci)->addTask($task);

        $this->id = $taskResult['id'];

        return $this;
    }

    /**
     * Update the database with task information
     * @param $stuff
     * @return $this
     */
    public function update_db($stuff)
    {
        $result = null;
        try {
            $this->db->reconnect();
            $result = $this->db
                ->where('id', $this->getId())
                ->update('tasks', $stuff);
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }
        if(!$result){
            throw new Exception(" Update DB failed: ". $this->db->_error_message());
        }
        return $result;
    }

    /**
     * @Overwrite
     */
    public function run_task()
    {
    }

    /**
     * Hooks
     */
    public function hook_start()
    {
    }

    public function hook_end()
    {
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'status' => $this->getStatus(),
            'message' => $this->getMessage(),
            'data' => $this->taskData
        ];
    }

    /**
     * Setters and Getters
     */

    /**
     * Set the database for use
     * Works with Codeigniter DB class
     * @param $db
     * @return $this
     */
    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }

    public function getCI()
    {
        if (isset($this->ci)) {
            return $this->ci;
        }
        $ci =& get_instance();
        return $ci;
    }

    /**
     * Set the Codeigniter instance
     * @param $ci
     * @return $this
     */
    public function setCI($ci)
    {
        $this->ci = $ci;
        return $this;
    }

    /**
     * Set the status of the task
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = ucwords($status);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return isset($this->id) ? $this->id : null;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return boolean
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @return array
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getMemoryLimit()
    {
        return $this->memoryLimit;
    }

    /**
     * @param string $memoryLimit
     */
    public function setMemoryLimit($memoryLimit)
    {
        $this->memoryLimit = $memoryLimit;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getLastRun()
    {
        return $this->lastRun;
    }


    /**
     * @param $lastRun
     * @return $this
     */
    public function setLastRun($lastRun)
    {
        $this->lastRun = $lastRun;
        return $this;
    }

    /**
     * @param array $message
     */
    public function setMessage($message = false)
    {
        if (!$message) $message = ['log' => [], 'error' => []];
        $this->message = $message;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

}