<?php
/**
 * Class:  TaskManager
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;


class TaskManager
{
    private $db;

    /**
     * List all the tasks that satisfy the status
     * @param string $status
     * @param int $limit
     * @param int $offset
     * @return string
     * @throws Exception
     */
    public function listTasks($status = false, $limit = 10, $offset = 0)
    {
        $limit = $limit ? $limit : 10;
        $offset = $offset ? $offset : 0;
        if ($status) $this->db->where('status', $status);
        $query = $this->db->limit($limit, $offset)->get('tasks');
        if ($query->num_rows() == 0) return "No task found!";
        $result = $query->result_array();
        return $result;
    }

    /**
     * Add a task to the database
     * @param $task
     */
    public function addTask($task)
    {
        if (!isset($task['priority'])) $task['priority'] = 1;
        $task['date_added'] = date('Y-m-d H:i:s', time());
        $task['status'] = 'PENDING';
        if ($this->db->insert('tasks', $task)) {
            $id = $this->db->insert_id();
            return $this->getTask($id);
        } else {
            return false;
        }
    }

    public function getTask($id)
    {
        $query = $this->db->get_where('tasks', ['id'=>$id]);
        if ($query->num_rows() > 0) {
            return $query->first_row(true);
        } else {
            return false;
        }
    }

    /**
     * Find a task to do
     * Returns the first task that is pending of highest priority
     * @return mixed|bool
     */
    public function findPendingTask()
    {
        //get a list of pending task ordered by priority
        $query = $this->db->where('status', 'PENDING')->order_by('priority')->get('tasks');
        if ($query->num_rows() > 0) {
            return $query->first_row(true);
        }

        return false;
    }

    /**
     * TaskManager constructor.
     * @param $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @param mixed $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

}