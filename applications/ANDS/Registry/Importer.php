<?php


namespace ANDS\Registry;


use ANDS\DataSource;
use ANDS\Payload;
use ANDS\API\Task\ImportTask;

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
        $importTask->run();

        return $importTask;

    }
}