<?php

namespace ANDS\Repository;

use ANDS\API\Task\ImportTask;
use ANDS\DataSource;
use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Registry\Providers\DCI\DCI;
use ANDS\Registry\Providers\Scholix\Scholix;
use ANDS\RegistryObject;
use ANDS\RegistryObjectAttribute;
use ANDS\RegistryObject\Links;
use ANDS\RegistryObject\Metadata;
use ANDS\RegistryObject\Identifier;
use ANDS\RecordData;
use ANDS\Util\Config;
use Carbon\Carbon;

class RegistryObjectsRepository
{
    /**
     * Delete a single record by ID
     * uses ProcessDelete task to complete the job
     * Does not give more information than true or false
     *
     * @param $id
     * @return bool
     */
    public static function deleteRecord($id)
    {
        $importTask = new ImportTask();
        $importTask->init([])->bootEloquentModels();

        $importTask
            ->setTaskData('deletedRecords', [$id])
            ->setTaskData('subtasks', [['name'=>'ProcessDelete', 'status'=>'PENDING']])
            ->initialiseTask();
        $deleteTask = $importTask->getTaskByName('ProcessDelete');
        $deleteTask->run();

        if ($deleteTask->hasError()) {
            return false;
        }

        return true;
    }

    /**
     * Completely delete
     *
     * @param $id
     */
    public static function completelyEraseRecordByID($id)
    {
        $record = RegistryObject::find($id);
        if ($record) {
            // delete attributes
            RegistryObjectAttribute::where('registry_object_id', $record->registry_object_id)->delete();

            // delete record_data
            RecordData::where('registry_object_id', $record->registry_object_id)->delete();

            // delete metadata
            Metadata::where('registry_object_id', $record->registry_object_id)->delete();

            //delete links
            Links::where('registry_object_id', $record->registry_object_id)->delete();

            // delete scholix documents
            Scholix::where('registry_object_id', $record->registry_object_id)->delete();

            // delete dci documents
            DCI::where('registry_object_id', $record->registry_object_id)->delete();

            // delete record
            $record->delete();

            // TODO: delete Portal and Relation index?
            return true;
        }
        return false;
    }

    /**
     * @param $id
     */
    public static function completelyEraseMetadataByID($id)
    {
        // delete metadata
        Metadata::where('registry_object_id', $id)->delete();

        //delete links
        Links::where('registry_object_id', $id)->delete();
    }


    /**
     * Completely erase the existence of a record by key
     * use with caution, deletes all status of a key
     *
     * @param $key
     */
    public static function completelyEraseRecord($key)
    {
        $records = RegistryObject::where('key', $key)->get();
        foreach ($records as $record) {
            self::completelyEraseRecordByID($record->registry_object_id);
        }
    }

    /**
     * Get the published version of a record by key
     *
     * @param $key
     * @return RegistryObject
     */
    public static function getPublishedByKey($key)
    {
        return self::getByKeyAndStatus($key, 'PUBLISHED');
    }

    /**
     * Get any record in the DRAFT Group by key
     *
     * @param $key
     * @return RegistryObject
     */
    public static function getDraftByKey($key)
    {
        return self::getByKeyAndStatus($key, self::getDraftStatusGroup());
    }

    /**
     * Useful function to get record by key and status
     *
     * @param $key
     * @param array|string $status
     * @return RegistryObject
     */
    public static function getByKeyAndStatus($key, $status = "PUBLISHED")
    {
        $importTask = new ImportTask();
        $importTask->init([])->bootEloquentModels();
        if (is_array($status)) {
            return RegistryObject::where('key', $key)->whereIn('status', $status)->first();
        }
        return RegistryObject::where('key', $key)->where('status', $status)->first();
    }


    /**
     * Useful function to get record-count by data_source_id and status
     *
     * @param $dataSourceId
     * @param string $status
     * @return integer
     */
    public static function getCountByDataSourceIDAndStatus($dataSourceId, $status)
    {
        $importTask = new ImportTask();
        $importTask->init([])->bootEloquentModels();

        return  RegistryObject::where('data_source_id', $dataSourceId)->where('status', $status)->count();
    }


    public static function getRecordsByDataSourceIDAndStatus($dataSourceId, $status, $offset=0, $limit=10)
    {
        return RegistryObject::where('data_source_id', $dataSourceId)
            ->where('status', $status)
            ->limit($limit)
            ->offset($offset)->get();
    }

    /**
     * @param $id
     * @return RegistryObject
     */
    public static function getRecordByID($id)
    {
        return RegistryObject::where('registry_object_id', $id)->first();
    }


    /**
     * @param $identifiers
     * @param $dataSourceId
     * @param string $status
     * @return RegistryObject[]
     */
    public static function getRecordsByIdentifier($identifiers, $dataSourceId, $status = "PUBLISHED")
    {

        $registryObjectIDs = [];
        $client = new MyceliumServiceClient(Config::get('mycelium.url'));
        // make sure to test for empty identifier values and types before calling mycelium
        foreach ($identifiers as $identifier){
            if(isset($identifier["identifier"]) && trim($identifier["identifier"]) != "" && isset($identifier['identifier_type']) && trim($identifier['identifier_type']) != "") {
                try {
                    $result = $client->findRecordByIdentifier(trim($identifier["identifier"]), trim($identifier['identifier_type']));
                    if ($result != null) {
                        $vertices = json_decode($result->getBody());
                        foreach ($vertices as $v) {
                            if ($v->identifierType == 'ro:id' && $v->dataSourceId == $dataSourceId && $v->status == $status) {
                                $registryObjectIDs[] = $v->identifier;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    debug("No registry object found with identifier value:" . $identifier["identifier"] . " type:" . $identifier["identifier_type"]);
                }
            }
        }
        if(sizeof($registryObjectIDs) > 0)
        {
            return RegistryObject::wherein('registry_object_id',$registryObjectIDs)->get();
        }
        return null;
    }

    public static function getRecordsByHarvestID($harvestId, $dataSourceId, $status = "PUBLISHED")
    {
        $importTask = new ImportTask();
        $importTask->init([])->bootEloquentModels();

        $registryObjects = RegistryObject::where('data_source_id', $dataSourceId)->where('status', $status)->get();

        $registryObjects = $registryObjects->filter(function($obj) use ($harvestId){
            return $obj->hasHarvestID($harvestId);
        });

        return $registryObjects;
    }

    public static function getRecordsByDifferentHarvestID($harvestId, $dataSourceId, $status = "PUBLISHED")
    {
        $importTask = new ImportTask();
        $importTask->init([])->bootEloquentModels();

        $registryObjects = RegistryObject::where('data_source_id', $dataSourceId)->where('status', $status)->get();

        $registryObjects = $registryObjects->filter(function($obj) use ($harvestId){
            return $obj->hasDifferentHarvestID($harvestId);
        });

        return $registryObjects;
    }


    public static function getDraftStatusGroup()
    {
        return [
            "MORE_WORK_REQUIRED",
            "DRAFT",
            "SUBMITTED_FOR_ASSESSMENT",
            "ASSESSMENT_IN_PROGRESS",
            "APPROVED"
        ];
    }

    public static function isDraftStatus($status)
    {
        return in_array($status, self::getDraftStatusGroup());
    }

    public static function getPublishedStatusGroup()
    {
        return ["PUBLISHED"];
    }

    public static function isPublishedStatus($status)
    {
        return in_array($status, self::getPublishedStatusGroup());
    }

    /**
     * Return a RegistryObject instance matching given status
     *
     * @param string $key
     * @param string $status
     * @return RegistryObject
     */
    public static function getMatchingRecord($key, $status)
    {
        $inStatus = self::getPublishedStatusGroup();

        if (in_array($status, self::getDraftStatusGroup())) {
            $inStatus = self::getDraftStatusGroup();
        }

        $matchingStatusRecords = RegistryObject::where('key', $key)
            ->whereIn('status', $inStatus)->first();

        return $matchingStatusRecords;
    }

    public static function getNotDeletedRecordFromOtherDataSourceByKey($key, $dataSourceId)
    {
        $matchingStatusRecords = RegistryObject::where('key', $key)->where('status', '!=', 'DELETED')
            ->where('data_source_id', '!=', $dataSourceId)->first();
        return $matchingStatusRecords;
    }


    public static function getDeletedRecord($key)
    {
        $deletedRecord = RegistryObject::where('key', $key)
            ->where('status', 'DELETED')
            ->first();
        return $deletedRecord;
    }

    public static function addNewVersion($registryObjectID, $xml)
    {
        $newVersion = new RecordData;
        $newVersion->current = true;
        $newVersion->registry_object_id = $registryObjectID;
        $newVersion->timestamp = time();
        $newVersion->saveData($xml);
        $newVersion->save();
        return $newVersion;
    }


    public static function getRecordsByDataSource(DataSource $dataSource, $limit, $offset, $filters = [])
    {

        $query = RegistryObject::where('data_source_id', $dataSource->data_source_id);

        if ($limit > 0) {
            $query = $query->limit($limit)->offset($offset);
        }

        foreach ($filters as $key => $value) {
            $query = $query->where($key, $value);
        }

        return $query->get();
    }

    public static function getPublishedBy($filters)
    {
        $query = static::getPublishedQuery($filters);

        return $query->get();
    }

    public static function getAllByFilters($filters)
    {
        $query = static::getByFilters($filters, RegistryObject::where('status', '<>', 'DELETED'));

        return $query->get();
    }

    public static function getCountByFilters($filters)
    {
        $query = static::getByFilters($filters, RegistryObject::where('status', '<>', 'DELETED'));

        return $query->count();
    }

    public static function getCountPublished($filters)
    {
        $query = static::getPublishedQuery($filters);

        return $query->count();
    }

    public static function getPublishedQuery($filters)
    {
        $query = RegistryObject::where('status', 'PUBLISHED');

        return static::getByFilters($filters, $query);
    }

    /**
     * @param $filters
     * @param null $q
     * @return RegistryObject
     */
    public static function getByFilters($filters, $q = null)
    {
        $query = $q ? $q: RegistryObject::orderBy('registry_object_id', 'asc');

        // limit and offset
        if (array_key_exists('limit', $filters)) {
            $query = $query->limit($filters['limit']);
            unset($filters['limit']);
        }

        if (array_key_exists('offset', $filters)) {
            $query = $query->offset($filters['offset']);
            unset($filters['offset']);
        }

        // identifier
        // TODO: refactor using Mycelium search
        /**
        if (array_key_exists('identifier', $filters) && $filters['identifier'] != "*") {
            $identifierQuery = Identifier::where('identifier', 'like', '%'.$filters['identifier'].'%');
            if (array_key_exists('identifier_type', $filters)) {
                $identifierQuery = Identifier::where('identifier_type', $filters['identifier_type']);
                unset($filters['identifier_type']);
            }
            $ids = $identifierQuery->pluck('registry_object_id');
            $query = $query->whereIn('registry_object_id', $ids);
            unset($filters['identifier']);
        }
        **/
        // link
        if (array_key_exists('link', $filters) && $filters['link'] != "*") {
            $linkQuery = Links::where('link', 'like', '%'.$filters['link'].'%');
            $ids = $linkQuery->pluck('registry_object_id');
            $query = $query->whereIn('registry_object_id', $ids);
            unset($filters['link']);
        }

        // sync_status [UNSYNCED, DESYNCED]
        // TODO make constants
        if (array_key_exists('sync_status', $filters) && $filters['sync_status'] != "*") {
            if ($filters['sync_status'] == "UNSYNCED") {
                $query = $query->whereNull("synced_at");
            } elseif ($filters['sync_status'] == "DESYNCED") {
                $query = $query->whereColumn("synced_at", '<', 'modified_at');
            } elseif ($filters['sync_status'] == "NEEDSYNC") {
                // records that hasn't been synced at all
                // records that hasn't been synced since it's last modified
                // records that hasn't been synced in 1 week
                // records that was not modified today
                $query = $query->where(function($query) {
                    $query->whereNull('synced_at')
                        ->orWhereColumn('synced_at', '<', 'modified_at')
                        ->orWhere('synced_at', '<', Carbon::now()->addWeek(-1));
                });
                $query = $query->where('modified_at', '<', Carbon::now()->addDay(-1));
            }

            unset($filters['sync_status']);
        }

        // core attributes
        foreach ($filters as $key => $value) {
            if ($value != "*") {
                $query = $query->where($key, 'like', '%'.$value.'%');
            }
        }

        //        var_dump($query->getBindings());
//                dd($query->toSql());

        return $query;
    }

    public static function getPublishedRecords($limit, $offset)
    {
        return RegistryObject::limit($limit, $offset)->get();
    }
}