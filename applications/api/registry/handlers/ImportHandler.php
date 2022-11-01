<?php
namespace ANDS\API\Registry\Handler;
use ANDS\API\Registry\Handler\errorPipeline;
use ANDS\API\Task\Task;
use ANDS\DataSource;
use ANDS\Payload;
use ANDS\Repository\DataSourceRepository;
use ANDS\Task\TaskRepository;
use \Exception as Exception;

/**
 * Handles registry/import
 * Endpoint for Harvester
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
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
//        $dataSourceID = array_key_exists('ds_id', $params) ? $params['ds_id'] : false;
//        if (!$dataSourceID) {
//            throw new Exception('Data Source ID must be provided');
//        }

        // setup eloquent models
        initEloquent();

        $dataSourceID = array_key_exists('ds_id', $params) ? $params['ds_id'] : null;
        if ($dataSourceID === null && array_key_exists('ds_name', $params)) {
            $dataSourceID = DataSource::where('title', $params['ds_name'])->first()->data_source_id;
        }

        // get Data Source
        $dataSource = DataSourceRepository::getByID($dataSourceID);
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

        $name = array_key_exists('name', $params) ? $params['name'] : "Harvester initiated import - $dataSource->title($dataSource->data_source_id) - $batchID";

        $task = TaskRepository::create([
            'name' => $name,
            'type' => Task::$TYPE_SHELL,
            'params' => http_build_query([
                'class' => 'import',
                'ds_id' => $dataSource->data_source_id,
                'batch_id' => $batchID,
                'harvest_id' => $harvest ? $harvest->harvest_id : null,
                'source' => $from
            ])
        ], true);

        return $task->toArray();
    }

    /**
     * registry/import/?ds_id={id}&from=url&url={url}
     *
     * @param $dataSource
     * @param $url
     * @return array
     * @throws \Exception
     */
    private function importFromUrl($dataSource, $url)
    {
        // generate a batchID for this operation
        $batchID = "MANUAL-URL-".str_slug($url).'-'.time();

        // download & write the payload to the batchID location
        $content = @file_get_contents($url);
        Payload::write($dataSource->data_source_id, $batchID, $content);

        /** @var \ANDS\API\Task\ImportTask $task */
        $task = TaskRepository::create([
            'name' => "Import via URL - $dataSource->title($dataSource->data_source_id) - $url",
            'params' => http_build_query([
                'class' => 'import',
                'pipeline' => 'ManualImport',
                'source' => 'url',
                'url' => $url,
                'user_name' => $this->ci->user->name(),
                'ds_id' => $dataSource->data_source_id,
                'batch_id' => $batchID
            ])
        ], true);

        // initialise the ImportTask and prep for all subtasks to run immediately
        try {
            $task->initialiseTask()->enableRunAllSubTask()->run();
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
     * @return array
     * @throws \Exception
     */
    public function importFromXML($dataSource, $xml)
    {
        $xml = trim($xml);

        // generate a batchID for this operation & save the payload
        $batchID = "MANUAL-XML-".md5($xml).'-'.time();
        Payload::write($dataSource->data_source_id, $batchID, $xml);

        /** @var \ANDS\API\Task\ImportTask $task */
        $task = TaskRepository::create([
            'name' => "Import via pasted XML - $dataSource->title($dataSource->data_source_id)",
            'params' => http_build_query([
                'class' => 'import',
                'user_name' => $this->ci->user->name(),
                'pipeline' => 'ManualImport',
                'source' => 'xml',
                'ds_id' => $dataSource->data_source_id,
                'batch_id' => $batchID
            ])
        ], true);

        // initialise the ImportTask and prep for all subtasks to run immediately
        try {
            $task->initialiseTask()->enableRunAllSubTask()->run();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $task->toArray();
    }

    /**
     * Execute pipeline when error is reported by the harvester
     *
     * @param $dataSource
     * @param $batchID
     * @param bool $noRecords
     * @return array
     */
    private function errorPipeline($dataSource, $batchID, $noRecords = false)
    {
        $title = "Harvest error";

        $params = [
            'class' => 'import',
            'pipeline' => 'ErrorWorkflow',
            'source' => 'harvester',
            'ds_id' => $dataSource->data_source_id,
            'batch_id' => $batchID,
            'harvest_id' => $dataSource->harvest()->first()->harvest_id
        ];

        if ($noRecords) {
            $title = "Harvester initiated import";
            $params['noRecords'] = true;
        }

        /** @var \ANDS\API\Task\ImportTask $task */
        $task = TaskRepository::create([
            'name' =>  "$title - $dataSource->title($dataSource->data_source_id)",
            'type' => 'POKE',
            'frequency' => 'ONCE',
            'priority' => 2,
            'params' => http_build_query($params)
        ], true);

        $task->initialiseTask()->enableRunAllSubTask()->run();

        return $task->toArray();
    }
}
