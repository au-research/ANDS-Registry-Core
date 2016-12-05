<?php
namespace ANDS\API\Registry\Handler;
use ANDS\API\Registry\Handler\errorPipeline;
use ANDS\API\Task\TaskManager;
use ANDS\Payload;
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
     * ?from={source}
     * @return array
     * @throws Exception
     */
    public function handle()
    {
        // Parameters
        $params = is_array($this->ci->input->get()) ? $this->ci->input->get() : [];
        if (is_array($this->ci->input->post())) {
            $params = array_merge($params, $this->ci->input->post());
        }

        $from = array_key_exists('from', $params) ? $params['from'] : 'harvester';
        $dataSourceID = array_key_exists('ds_id', $params) ? $params['ds_id'] : false;
        if (!$dataSourceID) {
            throw new Exception('Data Source ID must be provided');
        }

        // setup eloquent models
        initEloquent();

        // get Data Source
        $dataSource = DataSourceRepository::getByID($params['ds_id']);
        if ($dataSource === null) {
            throw new Exception("Data Source $dataSourceID Not Found");
        }

        if ($from == "url") {
            return $this->importFromUrl($dataSource, $params['url']);
        }

        if ($from == "xml") {
            return $this->importFromXML($dataSource, $params['xml']);
        }

        $batchID = $params['batch_id'];

        $status = array_key_exists('status', $params) ? $params['status'] : null;

        if ($status === 'ERROR') {
             return $this->errorPipeline($dataSource, $batchID);
        }

        if ($status === "NORECORDS") {
            return $this->errorPipeline($dataSource, $batchID, $noRecords = true);
        }

        // get Harvest
        $harvest = $dataSource->harvest()->first();

        $task = [
            'name' => "HARVESTER INITIATED IMPORT - $dataSource->title($dataSource->data_source_id) - $batchID",
            'type' => 'PHPSHELL',
            'frequency' => 'ONCE',
            'priority' => 2,
            'params' => http_build_query([
                'class' => 'import',
                'ds_id' => $dataSource->data_source_id,
                'batch_id' => $params['batch_id'],
                'harvest_id' => $harvest->harvest_id,
                'source' => $from
            ])
        ];

        $taskManager = new TaskManager($this->ci->db, $this);
        $taskCreated = $taskManager->addTask($task);

        return $taskCreated;
    }

    /**
     * registry/import/?ds_id={id}&from=url&url={url}
     *
     * @param $dataSource
     * @param $url
     * @return mixed
     */
    private function importFromUrl($dataSource, $url)
    {
        $content = @file_get_contents($url);
        $batchID = "MANUAL-URL-".str_slug($url).'-'.time();
        Payload::write($dataSource->data_source_id, $batchID, $content);

        $task = [
            'name' => "IMPORT VIA URL - $dataSource->title($dataSource->data_source_id) - $url",
            'type' => 'POKE',
            'frequency' => 'ONCE',
            'priority' => 2,
            'params' => http_build_query([
                'class' => 'import',
                'pipeline' => 'ManualImport',
                'source' => 'url',
                'url' => $url,
                'user_name' => $this->ci->user->name(),
                'ds_id' => $dataSource->data_source_id,
                'batch_id' => $batchID
            ])
        ];

        $taskManager = new TaskManager($this->ci->db, $this);
        $taskCreated = $taskManager->addTask($task);
        $task = $taskManager->getTaskObject($taskCreated);

        $task
            ->setDb($this->ci->db)
            ->setCI($this->ci);

        $task->initialiseTask()->enableRunAllSubTask();

        try {
            $task->run();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }


        return $task->toArray();
    }

    /**
     * registry/import/?ds_id={id}&from=xml
     *
     * @param $dataSource
     * @param $xml
     * @return mixed
     */
    public function importFromXML($dataSource, $xml)
    {
        $xml = trim($xml);
        $batchID = "MANUAL-XML-".md5($xml).'-'.time();
        Payload::write($dataSource->data_source_id, $batchID, $xml);

        $task = [
            'name' => "Import via Pasted XML - $dataSource->title($dataSource->data_source_id)",
            'type' => 'POKE',
            'frequency' => 'ONCE',
            'priority' => 2,
            'params' => http_build_query([
                'class' => 'import',
                'user_name' => $this->ci->user->name(),
                'pipeline' => 'ManualImport',
                'source' => 'xml',
                'ds_id' => $dataSource->data_source_id,
                'batch_id' => $batchID
            ])
        ];

        $taskManager = new TaskManager($this->ci->db, $this);
        $taskCreated = $taskManager->addTask($task);
        $task = $taskManager->getTaskObject($taskCreated);

        $task
            ->setDb($this->ci->db)
            ->setCI($this->ci);

        $task->initialiseTask()->enableRunAllSubTask();

        $task->run();

        return $task->toArray();
    }

    /**
     * Execute pipeline when error is reported by the harvester
     *
     * @param $dataSource
     * @param $batchID
     * @return mixed
     */
    private function errorPipeline($dataSource, $batchID, $noRecords = false)
    {
        $params = [
            'class' => 'import',
            'pipeline' => 'ErrorWorkflow',
            'source' => 'harvester',
            'ds_id' => $dataSource->data_source_id,
            'batch_id' => $batchID,
            'harvest_id' => $dataSource->harvest()->first()->harvest_id
        ];

        if ($noRecords) {
            $params['noRecords'] = true;
        }

        $task = [
            'name' => "Harvest Error - $dataSource->title($dataSource->data_source_id)",
            'type' => 'POKE',
            'frequency' => 'ONCE',
            'priority' => 2,
            'params' => http_build_query($params)
        ];

        $taskManager = new TaskManager($this->ci->db, $this);
        $taskCreated = $taskManager->addTask($task);
        $task = $taskManager->getTaskObject($taskCreated);

        $task
            ->setDb($this->ci->db)
            ->setCI($this->ci);

        $task->initialiseTask()->enableRunAllSubTask();

        $task->run();

        return $task->toArray();
    }
}
