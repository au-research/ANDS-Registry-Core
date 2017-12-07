<?php


namespace ANDS\Registry;


use ANDS\DataSource;
use ANDS\Payload;
use ANDS\API\Task\ImportTask;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class Importer
 * @package ANDS\Registry
 */
class Importer
{
    /**
     * @param DataSource $dataSource
     * @param Payload $payload
     * @param array $customParameters
     *
     * @return ImportTask
     */
    public static function instantImportRecord(
        DataSource $dataSource,
        Payload $payload,
        $customParameters = []
    ) {
        $params = [
            'ds_id' => $dataSource->data_source_id
        ];

        $params = array_merge($params, $customParameters);

        $importTask = new ImportTask();
        $importTask->init([
            'name' => "Instant Import Task for $dataSource->title",
            'params' => http_build_query($params)
        ])->skipLoadingPayload();

        $importTask->setPayload("customPayload", $payload);
        $importTask->initialiseTask();
        $importTask->enableRunAllSubTask();

        $importTask->setCI($ci =& get_instance());
        $importTask->setDb($ci->db);
        $importTask->sendToBackground();

        $importTask->run();

        return $importTask;
    }

    /**
     * Schedule an Import, given a chunk of XML
     *
     * @param DataSource $dataSource
     * @param $xml
     * @param array $customParameters
     * @return ImportTask
     */
    public static function scheduleImportRecord(
        DataSource $dataSource,
        $xml,
        $customParameters = []
    ) {
        $params = [
            'ds_id' => $dataSource->data_source_id
        ];

        $params = array_merge($params, $customParameters);
        $name = $params['name'] ?: "Scheduled Import Task for {$dataSource->title}";

        // make Payload
        $xml = trim($xml);
        $batchID = "SCHEDULED-XML-".md5($xml).'-'.time();
        Payload::write($dataSource->data_source_id, $batchID, $xml);

        $importTask = new ImportTask();
        $importTask->init([
            'name' => $name,
            'params' => [
                'class' => 'import',
                'pipeline' => 'ManualImport',
                'source' => 'xml',
                'ds_id' => $dataSource->data_source_id,
                'batch_id' => $batchID
            ]
        ]);

        $importTask->initialiseTask();
//        $importTask->enableRunAllSubTask();

//        $importTask->setCI($ci =& get_instance());
//        $importTask->setDb($ci->db);
        $importTask->sendToBackground();

        return $importTask;
    }

    /**
     * @param DataSource $dataSource
     * @param $batchID
     * @param array $customParameters
     * @return ImportTask
     */
    public static function instantImportRecordFromBatchID(DataSource $dataSource, $batchID, $customParameters = [])
    {
        $params = [
            'ds_id' => $dataSource->data_source_id,
            'batch_id' => $batchID
        ];

        $params = array_merge($params, $customParameters);
        $importTask = new ImportTask();
        $importTask->init([
            'name' => "Import Task for $dataSource->title ($batchID)",
            'params' => http_build_query($params)
        ]);

        $importTask->initialiseTask();
        $importTask->enableRunAllSubTask();

        $importTask->setCI($ci =& get_instance());
        $importTask->setDb($ci->db);
        $importTask->sendToBackground();

        $importTask->run();

        return $importTask;
    }

    /**
     * @param DataSource $dataSource
     * @param array $customParams
     * @return ImportTask|null
     */
    public static function instantDeleteRecords(DataSource $dataSource, $customParams = [])
    {
        $ids = [];
        if (array_key_exists('ids', $customParams)) {
            $ids = $customParams['ids'];
        }

        if (array_key_exists('status', $customParams)) {
            $records = RegistryObjectsRepository::getRecordsByDataSourceIDAndStatus($dataSource->data_source_id, "PUBLISHED", 0, 1000);
            $ids = collect($records)->pluck('registry_object_id')->toArray();
        }

        $importTask = new ImportTask();
        $importTask->init([
            'params' => http_build_query([
                'ds_id' => $dataSource->data_source_id,
                'pipeline' => 'PublishingWorkflow'
            ])
        ])->skipLoadingPayload()->enableRunAllSubTask()->initialiseTask();
        $importTask->setTaskData("deletedRecords", $ids);

        $importTask->setCI($ci =& get_instance());
        $importTask->setDb($ci->db);
        $importTask->sendToBackground();

        $importTask->run();

        return $importTask;
    }

    public static function instantSyncRecord(RegistryObject $record)
    {
        return static::syncRecord($record, false);
    }

    public static function syncRecord(RegistryObject $record, $background = true)
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => "Manual Sync Record $record->title($record->id)",
            'params' => http_build_query([
                'ds_id' => $record->datasource->data_source_id,
                'targetStatus' => 'PUBLISHED',
                'pipeline' => 'SyncWorkflow'
            ])
        ])->skipLoadingPayload()->enableRunAllSubTask()->initialiseTask();
        $importTask->setTaskData("importedRecords", [$record->id]);

        $importTask->setCI($ci =& get_instance());
        $importTask->setDb($ci->db);
        $importTask->sendToBackground();

        if (!$background) {
            $importTask->run();
        }

        return $importTask;
    }

    public static function syncDataSource(DataSource $dataSource, $background = true)
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => "Manual Sync DataSource {$dataSource->meaningfulTitle}",
            'params' => http_build_query([
                'ds_id' => $dataSource->id,
                'targetStatus' => 'PUBLISHED',
                'pipeline' => 'SyncWorkflow'
            ])
        ])->skipLoadingPayload()->initialiseTask();

        $ids = RegistryObject::where('data_source_id', $dataSource->id)
            ->where('status', 'PUBLISHED')
            ->pluck('registry_object_id')
            ->toArray();

        $importTask->setTaskData("importedRecords", $ids);

        $importTask->setCI($ci =& get_instance());
        $importTask->setDb($ci->db);
        $importTask->sendToBackground();

        if ($background === false) {
            $importTask->run();
        }

        return $importTask;
    }
}