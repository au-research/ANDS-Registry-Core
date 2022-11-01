<?php
/**
 * Class:  ANDS\API\Task_api
 * Returns response for localhost/api/task/ based requests
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
namespace ANDS\API;

use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;
use ANDS\Task\TaskRepository;
use \Exception as Exception;
use ANDS\Registry\Providers\RelationshipProvider;
use Illuminate\Database\Capsule\Manager as Capsule;

class Task_api
{
    private $params;
    private $taskManager;

    /**
     * Task_api constructor.
     */
    public function __construct()
    {
        require_once BASE . 'vendor/autoload.php';

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
            case 'stop':
                $taskID = array_key_exists('identifier', $this->params) ? $this->params['identifier'] : null;
                if (!$taskID) {
                    throw new Exception("A task ID is required");
                }
                $task = TaskRepository::getById($taskID);
                $task->stop();
                return $task->toArray();
            case 'run':
                // run random pending tasks
                // todo implement
                throw new Exception("Not Implemented!");
            case 'exe' :
                $taskID = array_key_exists('identifier', $this->params) ? $this->params['identifier'] : null;
                if (!$taskID) {
                    throw new Exception("A task ID is required");
                }
                $task = TaskRepository::getById($taskID);
                $task->run();
                return $task->toArray();
            case 'restart':
                // restart a task & subtasks
                // todo implement restart
                if (!$this->params['identifier']) {
                    throw new Exception("A task ID is required");
                }
                throw new Exception("Not Implemented!");
            case 'all' :
            case 'pending' :
            case 'completed' :
            case 'running' :
            case 'stopped':
                // todo implement filter by status
                throw new Exception("Not Implemented!");
            default:
                //return task api/task/:id if exists
                $taskID = $this->params['submodule'];
                $task = TaskRepository::getById($taskID);
                return $task->toArray();
        }
    }
}