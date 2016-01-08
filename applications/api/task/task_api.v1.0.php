<?php
/**
 * Class:  ANDS\API\Task_api
 * Returns response for localhost/api/task/ based requests
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
namespace ANDS\API;

use \Exception as Exception;


class Task_api
{
    private $ci;
    private $params;
    private $taskManager;

    /**
     * Task_api constructor.
     */
    public function __construct()
    {
        $this->ci = &get_instance();
        $this->db = $this->ci->load->database('registry', true);
        require_once APP_PATH . 'vendor/autoload.php';

        $this->taskManager = new \ANDS\API\Task\TaskManager($this->db);
    }

    /**
     * Primary handle function
     * @param  array $method list of URL parameters
     * @return array          response
     */
    public function handle($method = array())
    {
        $this->params = array(
            'submodule' => isset($method[1]) ? $method[1] : false,
            'identifier' => isset($method[2]) ? $method[2] : false,
            'object_module' => isset($method[3]) ? $method[3] : false,
        );

        switch (strtolower($this->params['submodule'])) {
            case 'run':
                $someTask = $this->taskManager->findPendingTask();
                if ($someTask) {
                    return $this->exe($someTask['id']);
                } else {
                    //find something else to do
                    return "Nothing to do";
                }
                break;
            case 'exe' :
                if ($this->params['identifier']) {
                    return $this->exe($this->params['identifier']);
                } else {
                    throw new Exception("A task ID is required");
                }
                break;
            case 'pending' :
            case 'completed' :
            case 'running' :
            case 'stopped':
                $status = strtoupper($this->params['submodule']);
                return $this->taskManager->listTasks($status, $this->ci->input->get('limit'), $this->ci->input->get('offset'));
                break;
            default:
                return $this->report();
        }
    }

    /**
     * Execute a specific task
     * @param $taskId
     * @return array
     * @throws Exception
     */
    public function exe($taskId)
    {
        $query = $this->db->get_where('tasks', ['id' => $taskId]);
        if ($query->num_rows() == 0) throw new Exception("Task " . $taskId . " not found!");
        $taskResult = $query->first_row(true);

        $taskType = ucfirst($taskResult['name']);
        $className = "ANDS\\API\\Task\\" . $taskType . 'Task';
        $task = new $className($taskId);
        $task->init($taskResult);

        if ($taskType == 'Sync') {
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
                'task' => $task->getId(),
                'status' => $task->getStatus(),
                'params' => $task->getParams(),
                'message' => $task->getMessage()
            ];
            return $result;
        }

        $result = [
            'task' => $task->getId(),
            'status' => $task->getStatus(),
            'params' => $task->getParams(),
            'message' => $task->getMessage()
        ];
        return $result;
    }

    /**
     * Display a report of all the tasks
     * @return mixed
     */
    public function report()
    {
        $query = $this->db
            ->select(['status', 'count(*) as count'])
            ->group_by('status')
            ->get('tasks');
        return $query->result_array();
    }
}