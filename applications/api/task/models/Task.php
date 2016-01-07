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
    public $message = ['log'=>[]];

    private $db;

    function init($task)
    {
        $this->id = isset($task['id']) ? $task['id'] : false;
        $this->name = isset($task['name']) ? $task['name'] : false;
        $this->status = isset($task['status']) ? $task['status'] : false;
        $this->priority = isset($task['priority']) ? $task['priority'] : false;
        $this->params = isset($task['params']) ? $task['params'] : false;
        $this->message = isset($task['message']) ? json_decode($task['message'], true) : ['log'=>[]];
        return $this;
    }

    public function log($log)
    {
        $this->message['log'][] = $log;
        return $this;
    }

    public function run()
    {
//        $this->benchmark->mark('code_start');
//        $this->hook_start();

        $this->setStatus('RUNNING');

        //overwrite this method
        $this->run_task();

//        $this->hook_end();
//        $this->benchmark->mark('code_end');
//        $this->elapsed = $this->benchmark->elapsed_time('code_start', 'code_end');
//        $this->messages['elapsed'] = $this->elapsed;

        $this->setStatus('COMPLETED');
        $this->update_db(['message' => json_encode($this->getMessage())]);

        return $this;
    }

    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }

    public function setCI($ci) {
        $this->ci = $ci;
    }

    public function update_db($stuff)
    {
        $this->db
            ->where('id', $this->id)
            ->update('tasks', $stuff);

        return $this;
    }

    public function setStatus($status)
    {
        $this->status = ucwords($status);
        $this->update_db(
            $stuff = [
                'status' => $status,
            ]
        );
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