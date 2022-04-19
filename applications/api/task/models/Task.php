<?php

namespace ANDS\API\Task;


use ANDS\Log\Log;
use ANDS\Task\TaskRepository;
use ANDS\Util\Config;
use ANDS\Util\NotifyUtil;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;
use Symfony\Component\Stopwatch\Stopwatch;
use \Exception as Exception;

/**
 * God object Task
 *
 * Manage business rules of running tasks.
 * @see \ANDS\Task\TaskRepository for persistence model
 */
class Task
{
    private $id;
    public $name;
    public $status;
    public $params;
    public $lastRun;
    public $type;
    public $message = ['log' => [], 'error' => []];
    public $taskData = [];

    private $memoryLimit = '256M';
    private $dateFormat = 'Y-m-d | h:i:sa';
    public $dateAdded;

    /** @var \Monolog\Logger */
    private $logger = null;

    public static $STATUS_STOPPED = "STOPPED";
    public static $STATUS_PENDING = "PENDING";
    public static $STATUS_RUNNING = "RUNNING";
    public static $STATUS_SCHEDULED = "SCHEDULED";
    public static $STATUS_COMPLETED = "COMPLETED";

    public static $TYPE_SHELL = "PHPSHELL";
    public static $TYPE_POKE = "POKE";
    public static $TYPE_NONE = "NONE";

    /**
     * Initialisation of this task
     * @param $task
     * @return $this
     */
    function init($task)
    {
        $this->id = isset($task['id']) ? $task['id'] : null;
        $this->name = isset($task['name']) ? $task['name'] : null;
        $this->status = isset($task['status']) ? $task['status'] : static::$STATUS_PENDING;
        $this->params = isset($task['params']) ? $task['params'] : null;
        $this->type = isset($task['type']) ? $task['type']: static::$TYPE_NONE;

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

        $this->setTaskData('log_path', $this->getLogPath());
        $this->initLogger();

        return $this;
    }

    /**
     * Primary task running function
     * @return null|\ANDS\API\Task\Task
     */
    public function run()
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start($this->getName());
        $start = microtime(true);

        $this->hook_start();

        if ($this->getStatus() === static::$STATUS_STOPPED) {
            $this->log("Task is STOPPED");
            return null;
        }

        if (!ini_get('date.timezone')) {
            $timezone = Config::get('app.timezone') ?: 'UTC';
            date_default_timezone_set($timezone);
        }

        $this
            ->setStatus(static::$STATUS_RUNNING)
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

    public function stop()
    {
        $this->setStatus(static::$STATUS_STOPPED);
        $this->save();
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
        if ($logger = $this->getLogger()) {
            $logger->info($log);
        }

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
        if ($this->id) {
            return TaskRepository::save($this);
        }

        // todo dispatch task saved event
//        NotifyUtil::notify(
//            $channel = "task.".$this->getId(),
//            json_encode($this->toArray(), true)
//        );

        return $this;
    }

    public function sendToBackground()
    {
        if ($this->getId()) {
            return null;
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

        return TaskRepository::create([
            'name' => $this->getName(),
            'status' => Task::$STATUS_PENDING,
            'type' => Task::$TYPE_SHELL,
            'params' => http_build_query($params),
            'message' => json_encode($this->message),
            'data' => $this->taskData,
        ], true);
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
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
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

    /**
     * @param mixed $id
     * @return Task
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return Task
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getLogPath() {
        if (! $this->id) {
            // no id, no specific logger to write to
            return null;
        }

        $storageConfig = Config::get('app.storage');
        $globalLogPath = array_key_exists('logs', $storageConfig) ? $storageConfig['logs']['path'] : null;
        if (!$globalLogPath) {
            return null;
        }

        return rtrim($globalLogPath, '/') . "/tasks/$this->id.log";
    }

    public function initLogger()
    {
        $path = $this->getLogPath();
        if (!$path) {
            return;
        }

        $logger = new Monolog("task.$this->id.log");

        try {
            $handler = new StreamHandler($path, Monolog::DEBUG);

            // formatter
            $format =  LineFormatter::SIMPLE_FORMAT;
            $formatter = new LineFormatter($format);
            $handler->setFormatter($formatter);

            $logger->pushHandler($handler);

            $this->setTaskData('log_path', $path);
            $this->setLogger($logger);
        } catch (Exception $e) {
            $msg = get_exception_msg($e);
            Log::error(__METHOD__. " Failed to create logger for Task[id=$this->id], reason: $msg");
            return;
        }
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->initLogger();
        }
        return $this->logger;
    }

    /**
     * @param \Monolog\Logger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

}