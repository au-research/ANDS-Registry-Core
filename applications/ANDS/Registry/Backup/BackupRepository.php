<?php

namespace ANDS\Registry\Backup;

use ANDS\DataSource;
use ANDS\Log\Log;
use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Mycelium\RelationshipSearchService;
use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use Exception;
use MinhD\SolrClient\SolrClient;
use MinhD\SolrClient\SolrDocument;
use Symfony\Component\Stopwatch\Stopwatch;

class BackupRepository
{

    private static $config = null;

    private static $defaultOptions = [
        'includeGraphs' => true,
        'includePortalIndex' => true,
        'includeRelationshipsIndex' => true
    ];

    /**
     * Initialise the BackupRepository
     *
     * Set up the proper backup path from the config/backup.php file
     * @return void
     * @throws \Exception
     */
    public static function init($config = null)
    {
        static::$config = $config === null ? Config::get("backup") : $config;

        if (! is_readable(static::getBackupPath())) {
            throw new Exception("Backup path is not readable");
        }
        if (! is_writable(static::getBackupPath())) {
            throw new Exception("Backup path is not writable");
        }
    }

    /**
     * Obtain the backup path from the configuration
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getBackupPath()
    {
        // Runs init to cache the value if it's not already cached
        if (!static::$config) {
            static::init();
        }

        return static::$config['path'];
    }

    /**
     * Returns a list of Backups that is currently available in the system
     * @return \ANDS\Registry\Backup\Backup[]
     * @throws \Exception
     */
    public static function getAllBackups()
    {
        $backups = [];
        $files = scandir(self::getBackupPath());
        foreach ($files as $file) {
            if ($file == '.' || $file == '..' || $file == '.git') continue;
            $backups[] = self::getBackupById($file);
        }
        return $backups;
    }

    /**
     * Create & store new Backup
     *
     * @param $id string backup id
     * @param $dataSourceIds array datasource id
     * @param $options array kvp of options
     * @return array a kvp associated array of stats
     * @throws \Exception
     */
    public static function create($id, $dataSourceIds, $options = null)
    {
        if (!$options) {
            $options = static::$defaultOptions;
        }

        // todo, allow passing title, descriptions and authors via $options
        $title = "No title";
        $description = "No Description";
        $authors = [
            [
                'name' => 'SYSTEM',
                'email' => null
            ]
        ];

        // obtain actual DataSource instance, filter out those that are not found
        /** @var DataSource[] $dataSources */
        $dataSources = collect($dataSourceIds)->map(function($id) {
            return DataSourceRepository::getByID($id);
        })->filter(function($dataSource) {
            return $dataSource != null;
        });

        // create the Backup
        $backup = Backup::create($id, $title, $description, $authors, $dataSources);

        // store the Backup
        return static::storeBackup($backup, $options);
    }

    /**
     * Restore a Backup by ID
     *
     * @param $id string the backup id
     * @param $options array kvp associated array of options
     * @return array a kvp associated array of stats
     * @throws \Exception
     */
    public static function restore($id, $options = null)
    {
        if (!$options) {
            $options = static::$defaultOptions;
        }

        return static::restoreBackupById($id, $options);
    }

    /**
     * Get a particular Backup by ID
     *
     * @param $id string backup id
     * @return \ANDS\Registry\Backup\Backup
     * @throws \Exception
     */
    public static function getBackupById($id)
    {
        $path = static::getBackupPath();
        $backupPath = "$path/$id";
        if (! is_dir($backupPath) || ! is_readable($backupPath)) {
            Log::error("$backupPath not accessible");
            throw new Exception("$backupPath is not readable");
        }

        $metaFile = "$backupPath/meta.json";
        if (!is_file($metaFile) || !is_readable($metaFile)) {
            Log::error("$metaFile not accessible");
            throw new Exception("$metaFile is not readable");
        }
        $metaFileContent = file_get_contents($metaFile);

        // parse backup meta
        $backupMeta = json_decode($metaFileContent, true);

        return Backup::parse($backupMeta);
    }


    /**
     * Store a Backup to the file system.
     *
     * @param \ANDS\Registry\Backup\Backup $backup
     * @param null $options kvp associated array of options
     * @return array a kvp associated array of stats
     * @throws \Exception
     */
    public static function storeBackup(Backup $backup, $options = null)
    {
        if (!$options) {
            $options = static::$defaultOptions;
        }

        $path = static::getBackupPath();
        Log::info(__METHOD__. " Storing backup to $path", $backup->toMetaArray());

        $stopwatch = new Stopwatch();
        $stopwatch->start('storing_backup');

        // create directory for backup path
        $backupDirectoryPath = static::getBackupPath() . $backup->getId();
        if (! is_dir($backupDirectoryPath)) {
            try {
                mkdir($backupDirectoryPath, 0755);
                Log::debug("Created $backupDirectoryPath");
            } catch (Exception $e) {
                Log::error(__METHOD__ . " Failed to create directory $backupDirectoryPath");
                throw $e;
            }
        }
        Log::debug("DirectoryPath: $backupDirectoryPath");

        // write backup meta
        file_put_contents($backupDirectoryPath .'/meta.json', json_encode($backup->toMetaArray()));
        Log::debug("Created backup meta at $backupDirectoryPath/meta.json");

        $dataSourceBackupStats = [];
        foreach ($backup->getDataSources() as $dataSource) {

            // backup data sources
            $dataSourcePath = "$backupDirectoryPath/datasources/$dataSource->id";
            if (! is_dir($dataSourcePath)) {
                mkdir($dataSourcePath, 0755, true);
            }
            $dataSourceMetaPath = "$dataSourcePath/meta.json";
            $attributes = $dataSource->dataSourceAttributes->toArray();
            $exported = [
                'metadata' => $dataSource->toArray(),
                'attributes' => $attributes
            ];
            unset($exported['metadata']['data_source_attributes']);
            file_put_contents($dataSourceMetaPath, json_encode($exported));
            Log::debug("Created DataSource[id=$dataSource->id] meta at $dataSourceMetaPath");

            // backup records
            // todo store backup_records_time_taken_in_seconds
            $recordsPath = "$backupDirectoryPath/datasources/$dataSource->id/records";
            if (! is_dir($recordsPath)) {
                mkdir($recordsPath, 0755, true);
            }
            $records = RegistryObject::where('data_source_id', $dataSource->id)
                ->orderBy('registry_object_id')->pluck('registry_object_id');
            $count = $records->count();
            Log::debug("Exporting $count records for DataSource[id=$dataSource->id}]");
            foreach ($records as $id) {
                $filePath = "$recordsPath/$id.json";
                $record = RegistryObjectsRepository::getRecordByID($id);
                if (!$record) {
                    Log::warning(__METHOD__. " No record with id $id found");
                    continue;
                }
                $exported = [
                    'metadata' => $record,
                    'attributes' => $record->registryObjectAttributes->toArray(),
                    'xml' => base64_encode($record->getCurrentData()->data)
                ];
                unset($exported['metadata']['registry_object_attributes']);
                file_put_contents($filePath, json_encode($exported));
                Log::debug("Created RegistryObject[id=$record->id] meta at $filePath");
            }


            // todo backup_graphs_time_taken_in_seconds
            if ($options['includeGraphs']) {
                // todo backupGraphs on Mycelium
                static::backupGraphs($backup->getId(), $dataSource->id);
            }

            // todo backup_portal_index_time_taken_in_seconds
            if ($options['includePortalIndex']) {
                static::backupPortalIndex($backup->getId(), $dataSource->id);
            }

            // todo backup_relationships_index_time_taken_in_seconds
            if ($options['includeRelationshipsIndex']) {
                static::backupRelationshipsIndex($backup->getId(), $dataSource->id);
            }

            // collect stats
            $dataSourceBackupStats[$dataSource->id] = [
                'path' => $backupDirectoryPath,
                'records_count' => $count
            ];

            Log::info(__METHOD__ . " Finished creating backup at $backupDirectoryPath");
        }

        $event = $stopwatch->stop('storing_backup');

        // total records count = sum of all the records_count of all the dataSourceBackupStats
        $totalRecordsCount = collect($dataSourceBackupStats)->pluck('records_count')->reduce(function($carry, $item) {
            return $carry + $item;
        });

        return [
            'id' => $backup->getId(),
            'title' => $backup->getTitle(),
            'description' => $backup->getDescription(),
            'path' => $backupDirectoryPath,
            'data_sources_count' => count($backup->getDataSources()),
            'records_count' => $totalRecordsCount,
            'time_taken_in_seconds' => $event->getDuration() / 1000,
            'memory_usage_mb' => $event->getMemory() / 1000000,
            'include_graphs' => $options['includeGraphs'] ? "yes" : "no",
            'include_portal_index' => $options['includePortalIndex'] ? "yes" : "no",
            'include_relationships_index' => $options['includeRelationshipsIndex'] ? "yes" : "no"
        ];
    }

    /**
     * Restore a specific data source by ID
     *
     * @param $id string the backup id
     * @param $options array kvp associated array of options
     * @return array a kvp associated array of stats
     * @throws \Exception
     */
    public static function restoreBackupById($id, $options = null)
    {
        if (!$options) {
            $options = static::$defaultOptions;
        }
        Log::info(__METHOD__. " Restoring Backup[id=$id]");

        $stopwatch = new Stopwatch();
        $stopwatch->start('restoring_backup');

        // parse backup (mainly for validation)
        $backup = static::getBackupById($id);

        // restore each data sources found
        $dataSourceBackupStats = [];
        $path = static::getBackupPath();
        $dataSourcesPath = "$path/$id/datasources/";
        if (is_dir($dataSourcesPath) && is_readable($dataSourcesPath)) {
            $files = scandir($dataSourcesPath);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') continue;
                $dataSourcePath = "$dataSourcesPath/$file";
                if (is_dir($dataSourcePath) && is_readable($dataSourcePath)) {
                    $dataSourceBackupStats[$file] = static::restoreDataSourcePath($dataSourcePath, $options, $id);
                }
            }
        }

        $event = $stopwatch->stop('restoring_backup');

        $totalRecordsCount = collect($dataSourceBackupStats)->pluck('records_count')->reduce(function($carry, $item) {
            return $carry + $item;
        });

        return [
            'id' => $backup->getId(),
            'title' => $backup->getTitle(),
            'description' => $backup->getDescription(),
            'path' => static::getBackupPath().$backup->getId(),
            'data_sources_count' => count($dataSourceBackupStats),
            'records_count' => $totalRecordsCount,
            'time_taken_in_seconds' => $event->getDuration() / 1000,
            'memory_usage_mb' => $event->getMemory() / 1000000,
            'include_graphs' => $options['includeGraphs'] ? "yes" : "no",
            'include_portal_index' => $options['includePortalIndex'] ? "yes" : "no",
            'include_relationships_index' => $options['includeRelationshipsIndex'] ? "yes" : "no"
        ];

    }

    /**
     * Restore a data source given the path
     *
     * @param $dataSourcePath string data source path
     * @param $options array kvp associated array of options
     * @param $backupId string backup id
     * @return array a kvp associated array of stats
     * @throws \Exception
     */
    public static function restoreDataSourcePath($dataSourcePath, $options, $backupId) {
        $metaFile = "$dataSourcePath/meta.json";
        if (! is_file($metaFile) || ! is_readable($metaFile)) {
            throw new Exception("$metaFile is not accessible");
        }

        // dataSourceMeta contains original data source metadata
        // if there's already an existing data source with the same id, this would be wrong
        $metaContent = json_decode(file_get_contents($metaFile), true);
        $dataSourceMeta = $metaContent['metadata'];

        // overwrite datasource
        $existing = DataSourceRepository::getByID($dataSourceMeta['data_source_id']);
        if ($existing) {
            $dataSource = $existing;
            $dataSource->update($dataSourceMeta);
        } else {
            $dataSource = DataSource::forceCreate($dataSourceMeta);
        }

        // update/create attributes
        $dataSourceAttributes = $metaContent['attributes'];
        foreach ($dataSourceAttributes as $attribute) {
            $dataSource->setDataSourceAttribute($attribute['attribute'], $attribute['value']);
        }

        // restore records
        $recordsPath = "$dataSourcePath/records";
        $recordFileCount = 0;
        if (is_dir($recordsPath) && is_readable($recordsPath)) {
            $files = scandir($recordsPath);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') continue;
                $recordPath = "$recordsPath/$file";
                if (is_file($recordPath) && is_readable($recordPath)) {
                    $recordFileCount++;
                    static::restoreRecordPath($recordPath, $dataSource);
                }
            }
        }

        // restore graphs from the original data source id, Mycelium only has knowledge of
        // the original data source id
        if ($options['includeGraphs']) {
            static::restoreGraphs($backupId, $dataSourceMeta['data_source_id'], $dataSource->id);
        }

        // restore portal documents
        if ($options['includePortalIndex']) {
            $portalPath = "$dataSourcePath/portal";
            if (is_dir($portalPath) && is_readable($portalPath)) {
                $files = scandir($portalPath);
                $client = new SolrClient(Config::get('app.solr_url'));
                $client->setCore("portal");
                foreach ($files as $file) {
                    if ($file == '.' || $file == '..') continue;
                    $recordPath = "$portalPath/$file";
                    if (is_file($recordPath) && is_readable($recordPath)) {
                        static::restorePortalPath($recordPath, $client, $dataSource->id);
                    }
                }
                $client->commit();
            }
        }

        // restore relationship documents
        if ($options['includeRelationshipsIndex']) {
            $relationshipsPath = "$dataSourcePath/relationships";
            if (is_dir($relationshipsPath) && is_readable($relationshipsPath)) {
                $files = scandir($relationshipsPath);
                $client = new SolrClient(Config::get('app.solr_url'));
                $client->setCore("relationships");
                foreach ($files as $file) {
                    if ($file == '.' || $file == '..') continue;
                    $recordPath = "$relationshipsPath/$file";
                    if (is_file($recordPath) && is_readable($recordPath)) {
                        static::restoreRelationshipPath($recordPath, $client, $dataSource->id);
                    }
                }
                $client->commit();
            }
        }

        return [
            'path' => $metaFile,
            'records_count' => $recordFileCount
        ];

    }

    /**
     * Restore a particular record given a path
     *
     * Restore entries to the RegistryObject model, RegistryObjectAttributes and RecordData
     * @param $recordPath string path to the record json file
     * @return void
     */
    public static function restoreRecordPath($recordPath, DataSource $dataSource = null) {
        $metaContent = json_decode(file_get_contents($recordPath), true);
        $recordMeta = $metaContent['metadata'];

        // overwrite
        $recordId = $recordMeta['registry_object_id'];
        $existing = RegistryObject::find($recordId);
        unset($recordMeta['registry_object_attributes']);
        RegistryObject::unguard();
        if ($existing) {
            $record = $existing;
            $record->update($recordMeta);
        } else {
            $record = RegistryObject::create($recordMeta);
        }

        if ($dataSource != null) {
            $record->update(['data_source_id' => $dataSource->id]);
        }

        // update/create attributes
        $recordAttributes = $metaContent['attributes'];
        foreach ($recordAttributes as $attribute) {
            $record->setRegistryObjectAttribute($attribute['attribute'], $attribute['value']);
        }

        $xml = base64_decode($metaContent['xml']);
        $hash = md5($xml);

        $currentData = $record->getCurrentData();

        // if there's no previous current data, then insert a new current version
        if (!$currentData) {
            RegistryObjectsRepository::addNewVersion($record->id, $xml);
        }

        // if there is a current data and the current data is different, insert a new version
        if ($currentData && $currentData->hash != $hash) {
            RecordData::where('registry_object_id', $record->id)->update(['current' => 'FALSE']);
            RegistryObjectsRepository::addNewVersion($record->id, $xml);
        }
    }

    /**
     * Backup Portal Index for a data source
     *
     * Backup a data source worth of portal index into a series of json file stored in the backup id
     * @param $backupId string backup id
     * @param $dataSourceId string data source id
     * @return void
     * @throws \Exception
     */
    public static function backupPortalIndex($backupId, $dataSourceId)
    {
        $backupPath = static::getBackupPath();
        $portalBackupPath = "$backupPath/$backupId/datasources/$dataSourceId/portal";
        if (! is_dir($portalBackupPath)) {
            mkdir($portalBackupPath, 0755, true);
        }

        $client = new SolrClient(Config::get('app.solr_url'));
        $client->setCore("portal");

        // use the cursor to go through the "data_source_id:$dataSourceId" query and store each file in {$doc->id}.json
        $done = false;
        $cursor = "*";
        while ($done === false) {
            $payload = $client->cursor($cursor, 100, [
                'q' => "data_source_id:$dataSourceId"
            ]);
            foreach ($payload->getDocs() as $doc) {
                file_put_contents("$portalBackupPath/{$doc->id}.json", $doc->toJson());
            }
            $done = $payload->getNextCursorMark() == $payload->getCursorMark();
            $cursor = $payload->getNextCursorMark();
        }
    }

    /**
     * Backup all relationships document for a particular data source
     *
     * @param $backupId string backup id
     * @param $dataSourceId string data source id
     * @return void
     * @throws \Exception
     */
    public static function backupRelationshipsIndex($backupId, $dataSourceId)
    {
        $backupPath = static::getBackupPath();
        $relationshipsPath = "$backupPath/$backupId/datasources/$dataSourceId/relationships";
        if (! is_dir($relationshipsPath)) {
            mkdir($relationshipsPath, 0755, true);
        }

        $client = new SolrClient(Config::get('app.solr_url'));
        $client->setCore("relationships");

        // use RelationshipSearchService::getSolrParameters, so we'll also get the _nested_documents_
        // only backup parent type:relationship document
        $filters = RelationshipSearchService::getSolrParameters([
            'q' => "(from_data_source_id:$dataSourceId OR to_data_source_id:$dataSourceId) AND type:relationship"
        ], ['sort' => 'id desc']);

        // use the cursor to go through the "data_source_id:$dataSourceId" query and store each file in {$doc->id}.json
        $done = false;
        $cursor = "*";
        while ($done === false) {
            $payload = $client->cursor($cursor, 100, $filters);
            foreach ($payload->getDocs() as $doc) {
                file_put_contents("$relationshipsPath/{$doc->id}.json", $doc->toJson());
            }
            $done = $payload->getNextCursorMark() == $payload->getCursorMark();
            $cursor = $payload->getNextCursorMark();
        }
    }

    /**
     * Delete a backup by id
     *
     * @throws \Exception
     */
    public static function deleteBackupById($id)
    {
        throw new Exception("Not Implemented");
    }

    /**
     * Restore a portal index document
     *
     * @param $docPath string the document path
     * @param $client \MinhD\SolrClient\SolrClient|null the client to be reused
     * @return void
     */
    private static function restorePortalPath($docPath, SolrClient $client = null, $dataSourceId = null)
    {
        if ($client === null) {
            $client = new SolrClient(Config::get('app.solr_url'));
            $client->setCore("portal");
        }

        $content = json_decode(file_get_contents($docPath), true);

        // title is a copyField of display_title but is stored, so gets backed up anyway
        // need to be removed or else it'll cause an error upon indexing
        if (array_key_exists('display_title', $content)) {
            unset($content['title']);
        }

        // if the dataSourceId is defined, then the portal document would have that data source id
        if ($dataSourceId != null) {
            $content['data_source_id'] = $dataSourceId;
        }

        $doc = new SolrDocument($content);
        $result = $client->add($doc);
        if ($result['responseHeader']['status'] != 0) {
            $errorMessage = $result['error']['msg'];
            Log::error(__METHOD__. " Failed indexing $docPath. Message: $errorMessage");
        }
    }

    /**
     * Restore a relationship document by path
     *
     * @param string $docPath the document path
     * @param \MinhD\SolrClient\SolrClient|null $client the client to be reused
     * @return void
     */
    private static function restoreRelationshipPath($docPath, SolrClient $client = null, $dataSourceId = null)
    {
        if ($client === null) {
            $client = new SolrClient(Config::get('app.solr_url'));
            $client->setCore("relationships");
        }

        $content = json_decode(file_get_contents($docPath), true);

        // allow overwriting of data source id
        if ($dataSourceId != null) {
            $content['from_data_source_id'] = $dataSourceId;
        }

        $doc = new SolrDocument($content);
        $result = $client->add($doc);

        if ($result['responseHeader']['status'] != 0) {
            $errorMessage = $result['error']['msg'];
            Log::error(__METHOD__. " Failed indexing $docPath. Message: $errorMessage");
        }
    }

    /**
     * Uses Mycelium Backup API to back up the graphs
     *
     * @param string $backupId the backup id
     * @param string $dataSourceId the data source id
     * @return void
     */
    public static function backupGraphs($backupId, $dataSourceId)
    {
        $myceliumURL = Config::get('mycelium.url');
        $client = new MyceliumServiceClient($myceliumURL);

        $client->createBackup($backupId, $dataSourceId);
    }

    /**
     * Uses Mycelium Backup API to restore the graphs for a data source
     *
     * @param string $backupId the backup id
     * @param string $dataSourceId the data source id for path
     * @return void
     */
    public static function restoreGraphs($backupId, $dataSourceId, $correctedDataSourceId)
    {
        $myceliumURL = Config::get('mycelium.url');
        $client = new MyceliumServiceClient($myceliumURL);

        $client->restoreBackup($backupId, $dataSourceId, $correctedDataSourceId);
    }

    /**
     * Validate a backup by Id
     *
     * @throws \Exception when the backup is not valid
     */
    public static function validateBackup($backupId)
    {
        // validate each data source
        $path = static::getBackupPath();

        $dataSourcesPath = "$path/$backupId/datasources/";
        if (is_dir($dataSourcesPath) && is_readable($dataSourcesPath)) {
            $files = scandir($dataSourcesPath);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') continue;
                $dataSourcePath = "$dataSourcesPath/$file";
                self::validateDataSourceBackup($dataSourcePath);
            }
        }
    }

    /**
     * Validates a backup data source path
     *
     * @throws \Exception when it's not valid
     */
    public static function validateDataSourceBackup($dataSourcePath)
    {
        if (!is_dir($dataSourcePath)) {
            throw new Exception("DataSource[path=$dataSourcePath] is not a valid directory");
        }

        if (!is_readable($dataSourcePath)) {
            throw new Exception("DataSource[path=$dataSourcePath] is not a readable");
        }

        // check if the data source id already exist
        $metaFile = "$dataSourcePath/meta.json";
        if (! is_file($metaFile) || ! is_readable($metaFile)) {
            throw new Exception("DataSource[meta=$metaFile] is not accessible");
        }

        // dataSourceMeta contains original data source metadata
        $metaContent = json_decode(file_get_contents($metaFile), true);
        $dataSourceMeta = $metaContent['metadata'];
        $dataSourceId = $dataSourceMeta['data_source_id'];
        $existing = DataSourceRepository::getByID($dataSourceId);
        if ($existing) {
            throw new Exception("DataSource[id=$dataSourceId] already exists");
        }

        // restore records
        $recordsPath = "$dataSourcePath/records";
        if (is_dir($recordsPath) && is_readable($recordsPath)) {
            $files = scandir($recordsPath);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') continue;
                $recordPath = "$recordsPath/$file";

                if (!is_file($recordPath) || !is_readable($recordPath)) {
                    throw new Exception("RecordPath[path=$recordPath] is not accessible");
                }

                $metaContent = json_decode(file_get_contents($recordPath), true);
                $recordMeta = $metaContent['metadata'];
                $recordID = $recordMeta['registry_object_id'];

                if (RegistryObjectsRepository::getRecordByID($recordID)) {
                    throw new Exception("RegistryObject[id=$recordID] already exists");
                }

                $recordKey = $recordMeta['key'];
                $recordStatus = $recordMeta['status'];
                if (RegistryObject::where('key', $recordKey)->where('status', $recordStatus)->first()) {
                    throw new Exception("RegistryObject[key=$recordKey, status=$recordStatus] already exists");
                }
            }
        }

        return true;
    }
}