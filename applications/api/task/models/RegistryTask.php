<?php
/**
 * Class:  RegistryTask
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;

use \Exception as Exception;


class RegistryTask extends Task
{
    private $chunkSize = 500;
    private $ci;
    private $db;

    /**
     * RegistryTask constructor.
     */
    public function __construct()
    {
        parrent::__construct();
        $this->ci = &get_instance();
        $this->db = $this->ci->load->database('registry', true);
    }


    public function run_task()
    {
        $ids = $this->collect();
        if (sizeof($ids) > $this - $this->getChunkSize()) {
            $this->spawnChilds($ids);
        } else {
            $this->execute($ids);
        }
    }

    public function spawnChilds($ids)
    {
        $this->taskManager = new TaskManager($this->db);
        $total = sizeof($ids);
        $numChunk = ceil(($this->chunkSize < $total ? ('total' / $this->chunkSize) : 1));
        $this->log('Analyzing Data Source ' . $dsID);
        //spawn new tasks
        for ($i = 1; $i <= $numChunk ; $i++) {
            $params = [
                'type' => 'ds',
                'id' => $dsID,
                'chunkPos' => $i
            ];
            $task = array(
                'name' => 'sync',
                'priority' => $this->getPriority(),
                'frequency' => 'ONCE',
                'type' => 'POKE',
                'params' => http_build_query($params),
            );
            $this->taskManager->addTask($task);
        }
    }

    public function collect()
    {
        return [];
    }

    public function execute($ids)
    {

    }

    /**
     * @return int
     */
    public function getChunkSize()
    {
        return $this->chunkSize;
    }
}