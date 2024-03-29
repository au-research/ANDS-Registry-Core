<?php

namespace ANDS\Registry\API\Controller;

use ANDS\Registry\API\Middleware\IPRestrictionMiddleware;
use ANDS\Registry\API\Request;
use ANDS\Registry\Importer;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class RecordsController extends HTTPController implements RestfulController
{

    protected static $validFilters = [
        'data_source_id',
        'status',
        'class',
        'key',
        'type',
        'title',
        'slug',
        'group',
        'identifier',
        'link',
        'sync_status'
    ];

    public function index()
    {
        $filters = [
            'limit' => request('limit', 10),
            'offset' => request('offset', 0)
        ];

        foreach (static::$validFilters as $filter) {
            $filters[$filter] = request($filter, '*');
        }

        if (request('summary')) {
            return [
                'count' => RegistryObjectsRepository::getCountByFilters($filters)
            ];
        }

        return RegistryObjectsRepository::getAllByFilters($filters);
    }

    /**
     * TODO deprecate in favor of index() filter instead
     *
     * @return RegistryObject|null
     */
    public function resolve()
    {
        if ($key = request('key', null)) {
            return RegistryObjectsRepository::getPublishedByKey($key);
        }

        if ($slug = request('slug', null)) {
            return RegistryObject::where('slug', $slug)->where('status', 'PUBLISHED')->get();
        }

        if ($title = request('title', null)) {
            return RegistryObject::where('title', $title)->where('status', 'PUBLISHED')->get();
        }

        return null;
    }

    public function show($id = null)
    {
        return RegistryObjectsRepository::getRecordByID($id)->toArray();
    }

    /**
     * @throws \Exception
     */
    public function sync($id = null)
    {
        $this->middlewares([IPRestrictionMiddleware::class]);

        $workflow = Request::value('workflow', 'SyncWorkflow');

        $record = RegistryObjectsRepository::getRecordByID($id);
        $task = Importer::instantSyncRecord($record, $workflow);
        return $task->toArray();
    }

    public function update($id = null)
    {
        throw new \Exception("PUT not implemented");
    }

    /**
     * @throws \Exception
     */
    public function destroy($id = null)
    {
        $this->middlewares([IPRestrictionMiddleware::class]);

        $record = RegistryObjectsRepository::getRecordByID($id);
        $task = Importer::instantDeleteRecords($record->datasource, [
            'ids' => [$record->id]
        ]);

        return $task->toArray();
    }

    public function add()
    {
        throw new \Exception("POST not implemented");
    }


}