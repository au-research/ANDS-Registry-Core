<?php


namespace ANDS\Registry;


use ANDS\DataSource;
use ANDS\Payload;
use ANDS\API\Task\ImportTask;
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

        if (count($ids) === 0) {
            return null;
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
}