<?php
namespace ANDS\API\Registry\Handler;
use ANDS\API\Task\TaskManager;
use ANDS\Repository\DataSourceRepository;
use \Exception as Exception;

/**
 * Handles registry/import
 * Endpoint for Harvester
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class ImportHandler extends Handler
{

    /**
     * Handles registry/import
     * registry/import/?ds_id={id}&batch_id={batch_id}
     * @return array
     * @throws Exception
     */
    public function handle()
    {
        // $_GET
        $params = $this->ci->input->get();

        // setup eloquent models
        initEloquent();

        // get Data Source
        $dataSourceID = $params['ds_id'] ? $params['ds_id'] : null;
        if (!$dataSourceID) {
            throw new Exception('Data Source ID must be provided');
        }

        $dataSource = DataSourceRepository::getByID($params['ds_id']);
        if ($dataSource === null) {
            throw new Exception("Data Source $dataSourceID Not Found");
        }

        $batchID = $params['batch_id'];

        // get Harvest
        $harvest = $dataSource->harvest()->first();

        $task = [
            'name' => "HARVESTER INITIATED IMPORT - $dataSource->title($dataSource->data_source_id) - $batchID",
            'type' => 'POKE',
            'frequency' => 'ONCE',
            'priority' => 2,
            'params' => http_build_query([
                'class' => 'import',
                'ds_id' => $dataSource->data_source_id,
                'batch_id' => $params['batch_id'],
                'harvest_id' => $harvest->harvest_id
            ])
        ];

        $taskManager = new TaskManager($this->ci->db, $this);
        $taskCreated = $taskManager->addTask($task);

        return $taskCreated;
    }
}
