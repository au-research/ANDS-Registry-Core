<?php

namespace ANDS\Registry\Backup;

use ANDS\DataSource;
use ANDS\Log\Log;
use ANDS\RecordData;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use ANDS\Util\XMLUtil;
use Symfony\Component\Stopwatch\Stopwatch;

class BackupRepository
{

    private static $config = null;

    public static function init()
    {
        static::$config = Config::get("backup");

        if (! is_readable(static::getBackupPath())) {
            throw new \Exception("Backup path is not readable");
        }
        if (! is_writable(static::getBackupPath())) {
            throw new \Exception("Backup path is not writable");
        }
    }

    public static function getBackupPath()
    {
        if (!static::$config) {
            static::init();
        }
        return static::$config['path'];
    }

    public static function getAllBackups()
    {

    }

    public static function getBackupById($id)
    {
        $path = static::getBackupPath();
        $backupPath = "$path/$id";
        if (! is_dir($backupPath) || ! is_readable($backupPath)) {
            Log::error("$backupPath not accessible");
            throw new \Exception("$backupPath is not readable");
        }

        $metaFile = "$backupPath/meta.json";
        if (!is_file($metaFile) || !is_readable($metaFile)) {
            Log::error("$metaFile not accessible");
            throw new \Exception("$metaFile is not readable");
        }
        $metaFileContent = file_get_contents($metaFile);

        // parse backup meta
        $backupMeta = json_decode($metaFileContent, true);

        return Backup::parse($backupMeta);
    }


    /**
     * @param \ANDS\Registry\Backup\Backup $backup
     * @return array
     * @throws \Exception
     */
    public static function storeBackup(Backup $backup)
    {
        $path = static::getBackupPath();
        Log::info(__METHOD__. " Storing backup to $path", $backup->toMetaArray());

        $stopwatch = new Stopwatch();
        $stopwatch->start('storing_backup');

        // create directory for backup path
        try {
            $backupDirectoryPath = static::getBackupPath() . $backup->getId();
            Log::debug("DirectoryPath: $backupDirectoryPath");
            if (! is_dir($backupDirectoryPath)) {
                mkdir($backupDirectoryPath, 0755);
                Log::debug("Created $backupDirectoryPath");
            }
        } catch (\Exception $e) {
            Log::error(__METHOD__. " Failed to create directory $backupDirectoryPath");
            throw $e;
        }

        // write backup meta
        file_put_contents($backupDirectoryPath .'/meta.json', json_encode($backup->toMetaArray()));
        Log::debug("Created backup meta at $backupDirectoryPath/meta.json");

        $dataSourceBackupStats = [];
        foreach ($backup->getDataSources() as $dataSource) {

            // export data sources
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
            Log::debug("Created DataSource[id={$dataSource->id}] meta at $dataSourceMetaPath");

            // export records
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

            $dataSourceBackupStats[$dataSource->id] = [
                'path' => $backupDirectoryPath,
                'records_count' => $count
            ];

            Log::info(__METHOD__ . " Finished creating backup at $backupDirectoryPath");
        }

        $event = $stopwatch->stop('storing_backup');

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
            'memory_usage_mb' => $event->getMemory() / 1000000
        ];
    }

    public static function restoreBackupById($id)
    {
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
                    $dataSourceBackupStats[$file] = static::restoreDataSourcePath($dataSourcePath);
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
            'memory_usage_mb' => $event->getMemory() / 1000000
        ];

    }

    public static function restoreDataSourcePath($dataSourcePath) {
        $metaFile = "$dataSourcePath/meta.json";
        if (! is_file($metaFile) || ! is_readable($metaFile)) {
            throw new \Exception("$metaFile is not accessible");
        }

        $metaContent = json_decode(file_get_contents($metaFile), true);
        $dataSourceMeta = $metaContent['metadata'];

        // check existing datasource by key
        $existing = DataSourceRepository::getByKey($dataSourceMeta['key']);
        if ($existing) {
            $dataSource = $existing;
            $dataSource->update($dataSourceMeta);
        } else {
            $dataSource = DataSource::create($dataSourceMeta);
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
                    static::restoreRecordPath($recordPath);
                }
            }
        }

        return [
            'path' => $metaFile,
            'records_count' => $recordFileCount
        ];

    }

    public static function restoreRecordPath($recordPath) {
        $metaContent = json_decode(file_get_contents($recordPath), true);
        $recordMeta = $metaContent['metadata'];

        // check existing record by key & status
        $existing = RegistryObject::where('key', $recordMeta['key'])->where('status', $recordMeta['status'])->first();
        if ($existing) {
            $record = $existing;
            $record->update($metaContent);
        } else {
            $record = RegistryObject::create($recordMeta);
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
            \ANDS\Repository\RegistryObjectsRepository::addNewVersion($record->id, $xml);
        }

        // if there is a current data and the current data is different, insert a new version
        if ($currentData && $currentData->hash != $hash) {
            RecordData::where('registry_object_id', $record->id)->update(['current' => 'FALSE']);
            \ANDS\Repository\RegistryObjectsRepository::addNewVersion($record->id, $xml);
        }
    }

    /**
     * Delete a backup by id
     *
     * @throws \Exception
     */
    public static function deleteBackupById($id)
    {
        throw new \Exception("Not Implemented");
    }
}