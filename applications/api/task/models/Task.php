<?php
/**
 * Class:  Task
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * Date: 7/01/2016
 * Time: 10:49 AM
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

    public function log($log)
    {
        $this->message['log'][] = $log;
        return $this;
    }

    public function run()
    {
        $start = microtime(true);

        $this->hook_start();
        $this->setStatus('RUNNING');
        $this->log("Task run at " . date($this->dateFormat, $start));

        //overwrite this method
        try {
            $this->run_task();
        } catch (Exception $e) {
            $this
                ->setStatus("STOPPED")
                ->log("Task stopped with error " . $e->getMessage())
                ->save();
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

    function formatPeriod($endtime, $starttime)
    {
        $duration = $endtime - $starttime;
        $hours = (int)($duration / 60 / 60);
        $minutes = (int)($duration / 60) - $hours * 60;
        $seconds = (int)$duration - $hours * 60 * 60 - $minutes * 60;
        return ($hours == 0 ? "00" : $hours) . ":" . ($minutes == 0 ? "00" : ($minutes < 10 ? "0" . $minutes : $minutes)) . ":" . ($seconds == 0 ? "00" : ($seconds < 10 ? "0" . $seconds : $seconds));
    }

    public function save() {
        $data = [
            'status' => $this->getStatus(),
            'message' => json_encode($this->getMessage())
        ];
        return $this->update_db($data);
    }

    public function update_db($stuff)
    {
        $this->db
            ->where('id', $this->id)
            ->update('tasks', $stuff);

        return $this;
    }

    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }

    public function setCI($ci)
    {
        $this->ci = $ci;
    }

    public function setStatus($status)
    {
        $this->status = ucwords($status);
        return $this;
    }

    //hooks for some particular reason
    public function hook_start()
    {
    }

    public function hook_end()
    {
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

    //overwrite these methods
    private function load_params($task)
    {
    }

    public function run_task()
    {
    }

    public function getMessage()
    {
        return $this->message;
    }
}