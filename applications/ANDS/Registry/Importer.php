<?php


namespace ANDS\Registry;


use ANDS\DataSource;
use ANDS\Log\Log;
use ANDS\Payload;
use ANDS\API\Task\ImportTask;
use ANDS\RecordData;
use ANDS\Registry\Providers\DCI\DCI;
use ANDS\Registry\Providers\Scholix\Scholix;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\SolrIndex;

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

    public static function instantSyncRecord(RegistryObject $record, $workflow = 'SyncWorkflow')
    {
        Log::debug(__FUNCTION__ . " Syncing RegistryObject upon request", [
            'id' => $record->id, 'workflow' => $workflow,
        ]);
        return static::syncRecord($record, false, $workflow);
    }

    public static function syncRecord(RegistryObject $record, $background = true, $workflow = 'SyncWorkflow')
    {
        $importTask = new ImportTask();
        $importTask->init([
            'name' => "Manual Sync Record $record->title($record->id) - Workflow $workflow",
            'params' => http_build_query([
                'ds_id' => $record->datasource->data_source_id,
                'targetStatus' => 'PUBLISHED',
                'pipeline' => $workflow
            ])
        ])->skipLoadingPayload()->enableRunAllSubTask()->initialiseTask();
        $importTask->setTaskData("importedRecords", [$record->id]);

        $importTask->setCI($ci =& get_instance());
        $importTask->setDb($ci->db);

        if ($background) {
            $importTask->sendToBackground();
        } else {
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

    public static function importDataSourceFromFile($path, $overwrite = true) {

        if (! is_readable($path)) {
            throw new \Exception("$path must be accessible");
        }

        if (is_file($path)) {
            $dataSourceFile = $path;
        } elseif (is_dir($path)) {
            $dataSourceFile = $path .'/dataSource.json';
        }

        $dataSourceExport = json_decode(file_get_contents($dataSourceFile), true);

        $dataSourceMeta = $dataSourceExport['metadata'];
        if ($overwrite) {
            DataSource::unguard(true);
        }
        $dataSource = DataSource::firstOrCreate($dataSourceMeta);

        $dataSourceAttributes = $dataSourceExport['attributes'];
        foreach ($dataSourceAttributes as $attribute) {
            $dataSource->setDataSourceAttribute($attribute['attribute'], $attribute['value']);
        }

        return $dataSource;
    }

    public static function importRecordsFromDirectory($path) {
        if (! is_dir($path) || is_readable($path)) {
            throw new \Exception("$path must be accessible");
        }

        $recordsDirectory = $path .'/records';
        $files = scandir($recordsDirectory);

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            self::importRecordFromFile($file);
        }
    }

    public static function importRecordFromFile($filePath, $overwrite = true) {
        $recordExport = json_decode(file_get_contents($filePath), true);

        $id = $recordExport['metadata']['registry_object_id'];

        // quit if the record already exists if not overwriting
        if (RegistryObject::where('registry_object_id', $id)->exists() && !$overwrite) {
            return;
        }

        if ($overwrite) {
            RegistryObject::unguard(true);
        }

        if (array_key_exists('registry_object_attributes', $recordExport['metadata'])) {
            unset($recordExport['metadata']['registry_object_attributes']);
        }

        // create the records
        $record = RegistryObject::firstOrCreate($recordExport['metadata']);

        // create the attributes
        foreach ($recordExport['attributes'] as $attribute) {
            $record->setRegistryObjectAttribute($attribute['attribute'], $attribute['value']);
        }

        // create the record data
        RecordData::firstOrCreate([
            'registry_object_id' => $record->id,
            'current' => TRUE,
            'data' => base64_decode($recordExport['xml'])
        ]);
    }

    public static function wipeDataSourceRecords(DataSource $dataSource, $softDelete = true) {

        // delete portal index
        SolrIndex::getClient('portal')->removeByQuery("data_source_id:$dataSource->id");

        // delete mycelium vertices & relationship index
        $myceliumServiceClient = new \ANDS\Mycelium\MyceliumServiceClient(\ANDS\Util\Config::get('mycelium.url'));
        $myceliumServiceClient->deleteDataSourceRecords($dataSource);

        // set the status of registryObjects to DELETED to prevent them from being accessed
        RegistryObject::where('data_source_id', $dataSource->id)->update(['status' => 'DELETED']);

        // delete record data & every other field, chunk by 100 for performance
        $idQuery = function($query) use ($dataSource) {
            $query->select('registry_object_id')->from('registry_objects')->where('data_source_id', '=', $dataSource->id);
        };
        $keyQuery = function($query) use ($dataSource) {
            $query->select('key')->from('registry_objects')->where('data_source_id', '=', $dataSource->id);
        };

        RecordData::whereIn('registry_object_id', $idQuery)->delete();
        Scholix::whereIn('registry_object_id', $idQuery)->delete();
        DCI::whereIn('registry_object_id', $idQuery)->delete();
        RegistryObject\Tag::whereIn('key', $keyQuery)->delete();

        // delete the records afterwards
        if ($softDelete === false) {
            RegistryObject::where('data_source_id', $dataSource->id)->delete();
        }
    }
}