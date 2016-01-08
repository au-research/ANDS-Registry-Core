<?php
/**
 * Class:  Task
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;


class Task
{
    private $id;
    public $name;
    public $status;
    public $priority;
    public $params;
    public $message = ['log' => []];

    private $db;
    private $memoryLimit = '256M';

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
        $this->message = isset($task['message']) ? json_decode($task['message'], true) : ['log' => []];

        $this->dateFormat = 'Y-m-d | h:i:sa';

        return $this;
    }

    /**
     * Primary task running function
     * @return $this
     */
    public function run()
    {
        $start = microtime(true);

        $this->hook_start();
        $this->setStatus('RUNNING');
        $this->log("Task run at " . date($this->dateFormat, $start));

        ini_set('memory_limit', $this->getMemoryLimit());

        //overwrite this method
        try {
            $this->run_task();
        } catch (Exception $e) {
            $this->stoppedWithError($e->getMessage());
        } finally {
            $this->hook_end();
            $end = microtime(true);
            $this->setStatus('COMPLETED')
                ->log("Task finished at " . date($this->dateFormat, $end))
                ->log("Took: " . $this->formatPeriod($end, $start))
                ->save();
        }

        return $this;
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
        return $this;
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
            ->log("Task stopped with error " . $error)
            ->save();
        return $this;
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
            'status' => $this->getStatus(),
            'message' => json_encode($this->getMessage())
        ];
        return $this->update_db($data);
    }

    /**
     * Update the database with task information
     * @param $stuff
     * @return $this
     */
    public function update_db($stuff)
    {
        $this->db
            ->where('id', $this->id)
            ->update('tasks', $stuff);

        return $this;
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
}