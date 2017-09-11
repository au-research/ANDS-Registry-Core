<?php

namespace ANDS\Registry\API\Controller;


use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class RecordsController implements RestfulController
{

    public function index()
    {
        $filters = [
            'limit' => request('limit', 10),
            'offset' => request('offset', 0)
        ];
        $validFilters = [
            'data_source_id', 'class', 'key', 'type', 'title', 'slug', 'group'
        ];
        foreach ($validFilters as $filter) {
            $filters[$filter] = request($filter, '*');
        }
        return RegistryObjectsRepository::getPublishedBy($filters);
    }

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
        $record = RegistryObjectsRepository::getRecordByID($id);
        return $record;
    }

    public function update($id = null)
    {
        // TODO: Implement update() method.
    }

    public function delete($id = null)
    {
        // TODO: Implement delete() method.
    }

    public function store()
    {
        // TODO: Implement store() method.
    }
}