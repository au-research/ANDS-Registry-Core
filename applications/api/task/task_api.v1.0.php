<?php
/**
 * Class:  ANDS\API\Task_api
 * Returns response for localhost/api/task/ based requests
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
namespace ANDS\API;

use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;
use \Exception as Exception;
use ANDS\Registry\Providers\RelationshipProvider;
use Illuminate\Database\Capsule\Manager as Capsule;

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
        require_once BASE . 'vendor/autoload.php';

        $this->taskManager = new Task\TaskManager($this->db, $this->ci);

        // task operation takes a long time
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 2 * ONE_HOUR);
        set_time_limit(0);
    }

    /**
     * Primary handle function
     * @param  array $method list of URL parameters
     * @return array response
     * @throws Exception
     */
    public function handle($method = array())
    {
        $this->params = array(
            'submodule' => isset($method[1]) ? $method[1] : false,
            'identifier' => isset($method[2]) ? $method[2] : false,
            'object_module' => isset($method[3]) ? $method[3] : false,
        );
        switch (strtolower($this->params['submodule'])) {
            case 'test':
                return $this->test();
                break;
            case 'stop':
                if ($this->params['identifier']) {
                    return $this->taskManager->stopTask($this->params['identifier']);
                } else {
                    throw new Exception("A task ID is required");
                }
                break;
            case 'run':
                $someTask = $this->taskManager->findPendingTask();
                // if (!$someTask) $someTask = $this->taskManager->findRandomTask();
                if (!$someTask) {
                    return "Nothing to do";
                } else {
                    return $this->taskManager->runTask($someTask['id']);
                }

                break;
            case 'exe' :
                if ($this->params['identifier']) {
                    if ($this->ci->input->get('subtask')) {
                        return $this->taskManager->runTask($this->params['identifier'], $this->ci->input->get('subtask'));
                    }
                    return $this->taskManager->runTask($this->params['identifier']);
                } else {
                    throw new Exception("A task ID is required");
                }
                break;
            case 'restart':
                if (!$this->params['identifier']) {
                    throw new Exception("A task ID is required");
                }

                $task = $this->taskManager->getTask($this->params['identifier']);
                $taskObject = $this->taskManager->getTaskObject($task);
                $taskObject
                    ->setCI($this->ci)
                    ->setDb($this->ci->db)
                    ->setStatus('PENDING')
                    ->enableRunAllSubTask()
                    ->setMessage()
                    ->clearTaskData()
                    ->save();

                return $taskObject->run();

                // return $this->taskManager->runTask($this->params['identifier']);

                break;
            case 'all' :
            case 'pending' :
            case 'completed' :
            case 'running' :
            case 'stopped':
                $status = strtoupper($this->params['submodule']);
                if ($status=='ALL') $status = false;
                if ($this->params['identifier'] == 'clear') {
                    return $this->taskManager->deleteTasks($status);
                } elseif($this->params['identifier'] == 'reschedule') {
                    return $this->taskManager->changeTasksStatus($status, 'PENDING');
                }
                return $this->taskManager->listTasks($status, $this->ci->input->get('limit'), $this->ci->input->get('offset'));
                break;
            default:

                //return task api/task/:id if exists
                if ($task = $this->taskManager->getTask($this->params['submodule'])) {
                    /**
                     * api/task/:id/message/clear
                     * Clear the message log
                     */
                    $taskObject = $this->taskManager->getTaskObject($task);
                    if ($this->params['identifier'] == 'message' && $this->params['object_module'] == 'clear') {
                        $taskObject
                            ->setDb($this->db)
                            ->setMessage()
                            ->clearTaskData()
                            ->save();
                        $task = $this->taskManager->getTask($taskObject->getId());
                    } elseif ($this->params['identifier'] == 'reschedule') {
                        $taskObject
                            ->setDb($this->db)
                            ->setStatus('PENDING')
                            ->save();
                        $task = $this->taskManager->getTask($taskObject->getId());
                    } elseif ($this->params['identifier'] == 'clear') {
                        return $this->taskManager->deleteTask($taskObject->getId());
                    }
                    if ($task['message']) $task['message'] = json_decode($task['message'], true);
                    if (array_key_exists('data', $task)) $task['data'] = json_decode($task['data'], true);
                    $task['params'] = urldecode($task['params']);
                    return $task;
                }

                if ($this->ci->input->post('name')) {
                    return $this->handleAddingTask();
                } else {
                    return $this->report();
                }
        }
    }

    private function test() {
        initEloquent();
        // $record = RegistryObject::find(570703);
        $record = RegistryObject::find(568190);
        RelationshipProvider::process($record);
    }

    /**
     * POST to api/task
     * Adding a new task on command
     * @return bool|Task
     */
    public function handleAddingTask()
    {
        $post = $this->ci->input->post();

        $params = isset($post['params']) ? $post['params'] : array();

        $params['type'] = $post['type'];
        $params['id'] = $post['id'];

        $task = [
            'name' => $post['name'],
            'type' => 'POKE',
            'frequency' => 'ONCE',
            'priority' => isset($post['priority']) ? $post['priority'] : 1,
            'params' => http_build_query($params)
        ];

        return $this->taskManager->addTask($task);
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
        $queryResult = $query->result_array();

        $result = array();
        foreach($queryResult as $row) {
            $result[$row['status']] = $row['count'];
        }
        if (!isset($result['PENDING'])) $result['PENDING'] = 0;
        if (!isset($result['RUNNING'])) $result['RUNNING'] = 0;
        if (!isset($result['COMPLETED'])) $result['COMPLETED'] = 0;
        if (!isset($result['STOPPED'])) $result['STOPPED'] = 0;
        ksort($result);
        return $result;
    }
}