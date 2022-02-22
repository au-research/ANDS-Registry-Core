<?php
/**
 * Class:  TaskManager
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;

use \Exception as Exception;

/**
 * Class TaskManager
 *
 * @package ANDS\API\Task
 */
class TaskManager
{
    private $db;
    private $ci;

    /**
     * List all the tasks that satisfy the status
     * @param bool|string $status
     * @param int $limit
     * @param int $offset
     * @return string
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
    public function deleteTask($id)
    {
        $query = $this->db->where('id', $id)->delete('tasks');
        if ($query) {
            return true;
        } else {
            return $this->db->_error_message();
        }
    }

    /**
     * Update all tasks with a status to another status
     * Useful for mass reassigning, mass rescheduling
     * @param $byStatus
     * @param $status
     * @return string
     * @throws Exception
     */
    public function changeTasksStatus($byStatus, $status)
    {
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
     * @return bool
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

    /**
     * @param $db
     * @param $ci
     * @return static
     */
    public static function create($db, $ci)
    {
        return new static($db, $ci);
    }

    /**
     * Get a particular task from the database
     * Returns as a mysql row
     * @param $id
     * @return bool
     */
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
    public function runTask($taskId, $subTaskName = null)
    {
        $query = $this->db->get_where('tasks', ['id' => $taskId]);
        if ($query->num_rows() == 0) throw new Exception("Task " . $taskId . " not found!");
        $taskResult = $query->first_row(true);

        /** @var ImportTask $task */
        $task = $this->getTaskObject($taskResult);

        $task
            ->setDb($this->db)
            ->setCI($this->ci);

        try {
            if ($subTaskName) {
                debug($subTaskName . "is starting");
                $task->initialiseTask();
                $subTask = $task->getTaskByName($subTaskName);
                $subTask->run();
                $task->saveSubTaskData($subTask);
                $task->saveSubTasks();
                return $subTask->toArray();
            } else {
                $task->run();
            }
        } catch (Exception $e) {
            $task->setStatus("STOPPED");
            $task->log("Error: " . $e->getMessage());
            $task->save();
            return $task->toArray();
        }

        return $task->toArray();
    }


    public function stopTask($taskId)
    {
        $query = $this->db->get_where('tasks', ['id' => $taskId]);
        if ($query->num_rows() == 0) throw new Exception("Task " . $taskId . " not found!");
        $taskResult = $query->first_row(true);

        $task = $this->getTaskObject($taskResult);
        $task
            ->setDb($this->db)
            ->setCI($this->ci);

        try {
            $task->stopWithError();
        } catch (Exception $e) {
            $task->setStatus("STOPPED");
            $task->log("Error: " . $e->getMessage());
            $task->save();
            return $task->toArray();
        }

        return $task->toArray();
    }

    /**
     * Generate a Task object based on the task mysql row
     * Checks the params for the `class` first
     * then refer to the `name` column to determine the class type
     * @param $taskResult
     * @return Task
     * @throws Exception
     */
    public function getTaskObject($taskResult)
    {
        parse_str($taskResult['params'], $params);

        $taskType = isset($params['class']) ? ucfirst($params['class']) : false;

        if (!$taskType) {
            $taskType = ucfirst($taskResult['name']);
        }

        $className = "ANDS\\API\\Task\\" . $taskType . 'Task';
        if (class_exists($className)) {
            $className = "ANDS\\API\\Task\\" . $taskType . 'Task';
            $task = new $className($taskResult['id']);
            $task->init($taskResult);
            return $task;
        } else {
            throw new Exception("Task type ".$taskType. " not found!");
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
     * Find records that are not PUBLISHED in the index
     * @param $limit
     * @return array|bool
     */
    private function findNonPublishedIndexedRecords($limit){
        $solrResult = $this->ci->solr->init()
            ->setOpt('fl', 'id')
            ->setOpt('rows', $limit)
            ->setOpt('q', '-status:PUBLISHED')
            ->executeSearch(true);
        if ($solrResult['response']['numFound'] == 0) return false;
        $ids = array();
        foreach ($solrResult['response']['docs'] as $doc) {
            $ids[] = $doc['id'];
        }
        return $ids;
    }

    /**
     * Find Random Records from the database
     * @param int $limit
     * @return array
     * @throws Exception
     */
    private function findRandomRecords($limit = 50)
    {
        $query = $this->db
            ->select('registry_object_id')
            ->limit($limit)
            ->order_by('registry_object_id', 'random')
            ->where('status', 'PUBLISHED')
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
     * Find records that are missing/unindexed/bad
     * @param int $limit
     * @return array
     */
    private function findUnIndexedRecords($limit = 50)
    {
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $this->ci->load->model('registry/data_source/data_sources', 'ds');

        //collect database counts
        $query = $this->db->select('data_source_id, value')
            ->from('data_source_attributes')
            ->where('attribute', 'count_PUBLISHED')
            ->where('value >', 0)->get();
        $dbData = array();
        foreach ($query->result_array() as $row) {
            $dbData[$row['data_source_id']] = $row['value'];
        }

        //collect SOLR count via facets
        $solrResult = $this->ci->solr
            ->init()
            ->setOpt('rows', 0)
            ->setOpt('fl', 'id')
            ->setFacetOpt('field', 'data_source_id')
            ->setFacetOpt('limit', '-1')
            ->executeSearch(true);
        $solrData = array();
        $solrFacetResult = $solrResult['facet_counts']['facet_fields']['data_source_id'];
        for ($i = 0; $i < sizeof($solrFacetResult) - 1; $i += 2) {
            $solrData[$solrFacetResult[$i]] = $solrFacetResult[$i + 1];
        }

        //get the differences
        $diff = array_diff($dbData, $solrData);
        if (sizeof($diff) == 0) $diff = array_diff($solrData, $dbData);
        $result = array();
        foreach ($diff as $key => $value) {
            $result[$key] = [
                'dbCount' => $dbData[$key],
                'solrCount' => $solrData[$key],
                'missing' => $dbData[$key] - $solrData[$key]
            ];
        }

        // Populate the return IDs with the right differences
        // This requires generating a list of IDs from SOLR and from DBs and compare them
        $ids = array();
        if (sizeof($result) > 0) {
            foreach ($result as $key => $value) {
                if ((int)$value['missing'] != 0 && sizeof($ids) < $limit) {
                    $solrIDs = $this->getIDsFromSOLRByDataSource($key);
                    $dataSourceIDs = $this->ci->ro->getIDsByDataSourceID($key, false, 'PUBLISHED');
                    $difference = array_diff($solrIDs, $dataSourceIDs);
                    if (sizeof($difference) == 0) $difference = array_diff($dataSourceIDs, $solrIDs);
                    if (sizeof($difference) == 0) {
                        //need a recount
                        $dataSource = $this->ci->ds->getByID($key);
                        $dataSource->updateStats();
                        unset($dataSource);
                    } else {
                        $ids = array_merge($ids, $difference);
                    }
                }
            }
        }

        /*
         * Hard coded
         * Reason: not indexing PROV group, refer to indexable_json
         */
        foreach ($ids as $key=>$id) {
            $ro = $this->ci->ro->getByID($id);
            if ($ro) {
                if ($ro->class=='activity' && $ro->group=="Public Record Office Victoria") {
                    unset($ids[$key]);
                }
            }
            unset($ro);
        }

        return $ids;
    }

    /**
     * Returns a list of ID that belongs to a data source
     * @param $dataSourceID
     * @return array
     */
    private function getIDsFromSOLRByDataSource($dataSourceID)
    {
        $solrResult = $this->ci->solr
            ->init()
            ->setOpt('rows', 100000)
            ->setOpt('fl', 'id')
            ->setOpt('fq', '+data_source_id:' . $dataSourceID)->executeSearch(true);
        $result = array();
        foreach ($solrResult['response']['docs'] as $doc) {
            $result[] = $doc['id'];
        }
        return $result;
    }

    /**
     * TaskManager constructor.
     * @param $db
     * @param bool $ci
     */
    public function __construct($db, $ci = false)
    {
        $this->db = $db;
        if (!$ci) $ci =& get_instance();
        $this->ci = $ci;
    }

    /**
     * @param mixed $db
     * @return $this
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
     * @return $this
     */
    public function setCi($ci)
    {
        $this->ci = $ci;
        return $this;
    }

}