<?php
/**
 * Class:  TaskManager
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;

use \Exception as Exception;

class TaskManager
{
    private $db;
    private $ci;

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
        if ($status) {
            $this->db->where('status', $status);
        }
        $query = $this->db
            ->order_by('last_run desc')
            ->limit($limit, $offset)
            ->get('tasks');
        if ($query->num_rows() == 0) {
            return [];
        }
        $result = $query->result_array();
        foreach ($result as &$row) {
            if ($row['message']) {
                $row['message'] = json_decode($row['message'], true);
            }
        }
        return $result;
    }

    /**
     * Delete tasks by status
     * @param $status
     * @return string
     */
    public function deleteTasks($status)
    {
        if ($status != 'all') {
            $this->db->where('status', $status);
        }
        $query = $this->db->delete('tasks');
        if ($query) {
            return $this->listTasks($status);
        } else {
            return $this->db->_error_message();
        }
    }

    /**
     * Delete a task by ID
     * @param $id
     * @return bool
     */
    public function deleteTask($id){
        $query = $this->db->where('id', $id)->delete('tasks');
        if ($query) {
            return true;
        } else {
            return $this->db->_error_message();
        }
    }

    public function changeTasksStatus($byStatus, $status){
        if (strtolower($byStatus) == 'all') {
            throw new Exception("Cannot change status of all tasks");
        }

        $result = $this->db
            ->where('status', $byStatus)
            ->update('tasks', ['status' => $status]);

        if ($result) {
            return $this->listTasks($status);
        } else {
            return $this->db->_error_message();
        }
    }

    /**
     * Add a task to the database
     * @param $task
     */
    public function addTask($task)
    {
        if (!isset($task['priority'])) {
            $task['priority'] = 1;
        }
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
        $query = $this->db->get_where('tasks', ['id' => $id]);
        if ($query->num_rows() > 0) {
            return $query->first_row(true);
        } else {
            return false;
        }
    }

    /**
     * Execute a specific task
     * @param $taskId
     * @return array
     * @throws Exception
     */
    public function runTask($taskId)
    {
        $query = $this->db->get_where('tasks', ['id' => $taskId]);
        if ($query->num_rows() == 0) throw new Exception("Task " . $taskId . " not found!");
        $taskResult = $query->first_row(true);

        $task = $this->getTaskObject($taskResult);

        if (ucfirst($taskResult['name']) == 'Sync') {
            $task
                ->setDb($this->db)
                ->setCI($this->ci);
        }
        try {
            $task->run();
        } catch (Exception $e) {
            $task->setStatus("STOPPED");
            $task->log("Error: " . $e->getMessage());
            $task->save();
            $result = [
                'id' => $task->getId(),
                'status' => $task->getStatus(),
                'params' => $task->getParams(),
                'message' => $task->getMessage()
            ];
            return $result;
        }

        $result = [
            'id' => $task->getId(),
            'status' => $task->getStatus(),
            'params' => $task->getParams(),
            'message' => $task->getMessage()
        ];
        return $result;
    }

    public function getTaskObject($taskResult)
    {
        $taskType = ucfirst($taskResult['name']);
        $className = "ANDS\\API\\Task\\" . $taskType . 'Task';
        $task = new $className($taskResult['id']);
        $task->init($taskResult);
        return $task;
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

    public function findRandomTask()
    {
        /**
         * Find any missing records and sync them
         * Find random 50 records and sync them (maintain synchronisity)
         * Fix bad records (records that are in the index but not in database)
         */
        $limit = 50;

        //find random records that are not indexed yet
        $records = $this->findUnIndexedRecords($limit);
        //find random bad indices
        if (!$records || sizeof($records)==0) $records = $this->findBadIndicies($limit);
        //find random records as last resort
        if (!$records || sizeof($records)==0) $records = $this->findRandomRecords($limit);

        // sync records
        if ($records && sizeof($records) > 0) {
            $params = [
                'type' => 'ro',
                'id' => implode(',', $records)
            ];
            $taskId = $this->addTask(
                [
                    'name' => 'sync',
                    'type' => 'POKE',
                    'frequency' => 'ONCE',
                    'priority' => 1,
                    'params' => http_build_query($params)
                ]
            );
            return $taskId;
        }
        return false;
    }

    private function findRandomRecords($limit = 50)
    {
        $query = $this->db
            ->select('registry_object_id')
            ->limit($limit)
            ->order_by('registry_object_id', 'random')
            ->get('registry_objects');
        if ($query) {
            $result = array();
            foreach ($query->result_array() as $row) {
                $result[] = $row['registry_object_id'];
            }
            return $result;
        } else {
            throw new Exception($this->db->_error_message());
        }
    }

    /**
     * Find bad index
     * take a sample of 1000 records in SOLR
     * find if they exist in the DB and has status of PUBLISHED
     * @param int $limit
     * @return array
     */
    private function findBadIndicies($limit = 50){
        $this->ci->load->library('solr');
        $solrResult = $this->ci->solr
            ->setOpt('rows', 10000)
            ->setOpt('fl', 'id')
            ->setOpt('sort', 'random_'.time().' desc')
            ->executeSearch(true);
        $idSOLR = array();
        foreach($solrResult['response']['docs'] as $doc) {
            $idSOLR[] = $doc['id'];
        }


        $dbResult = $this->db->select('registry_object_id')
            ->from('registry_objects')
            ->where('status', 'PUBLISHED')
            ->where_in('registry_object_id', $idSOLR)
            ->get();
        $idDB = array();
        foreach($dbResult->result_array() as $row) {
            $idDB[] = $row['registry_object_id'];
        }

        $badIndicies = array_diff($idDB, $idSOLR);
        $badIndicies = array_splice($badIndicies, 0, $limit);
        return $badIndicies;

    }

    private function findUnIndexedRecords($limit = 50) {
        //require last_sync in registry_objects
        return false;
    }

    /**
     * TaskManager constructor.
     * @param $db
     */
    public function __construct($db, $ci = false)
    {
        $this->db = $db;
        if (!$ci) $ci =& get_instance();
        $this->ci = $ci;
    }

    /**
     * @param mixed $db
     */
    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCi()
    {
        return $this->ci;
    }

    /**
     * @param mixed $ci
     */
    public function setCi($ci)
    {
        $this->ci = $ci;
        return $this;
    }

}