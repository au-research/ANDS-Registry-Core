<?php
namespace ANDS\API;

use \Exception as Exception;


/**
 * ANDS\Task_api
 * for use with the ANDS API application
 *
 * Returns response for localhost/api/task/ based requests
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Task_api
{
    private $ci;
    private $params;

    private $taskManager;

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
                $status = strtoupper($this->params['submodule']);
                return $this->taskManager->listTasks($status, $this->ci->input->get('limit'), $this->ci->input->get('offset'));
                break;
            default:
                return $this->report();
        }
    }

    public function exe($taskId)
    {
        $query = $this->db->get_where('tasks', ['id'=>$taskId]);
        if ($query->num_rows() == 0) throw new Exception("Task ". $taskId. " not found!");
        $taskResult = $query->first_row(true);

        $taskType = ucfirst($taskResult['name']);
        $className = "ANDS\\API\\Task\\".$taskType.'Task';
        $task = new $className($taskId);
        $task->init($taskResult);

        if ($taskType=='Sync') {
            $task
                ->setDb($this->db)
                ->setCI($this->ci);
        }
        try {
            $task->run();
        } catch (Exception $e) {
            throw new Exception($e);
        }

        $result = [
            'task' => $task->getId(),
            'status' => $task->getStatus(),
            'params' => $task->getParams(),
            'message' => $task->getMessage()
        ];
        return $result;
    }

    public function report()
    {
        $query = $this->db
            ->select(['status', 'count(*) as count'])
            ->group_by('status')
            ->get('tasks');
        return $query->result_array();
    }
}