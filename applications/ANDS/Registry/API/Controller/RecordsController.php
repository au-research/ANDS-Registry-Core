<?php

namespace ANDS\Registry\API\Controller;


use ANDS\Repository\RegistryObjectsRepository;

class RecordsController implements RestfulController
{

    public function index()
    {
        return RegistryObjectsRepository::getPublishedBy([
            'limit' => request('limit', 10),
            'offset' => request('offset', 0),
            'data_source_id' => request('data_source_id', "*"),
            'class' => request('class', '*')
        ]);
    }

    public function show($id = null)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        return $record;
    }

    public function update($id = null)
    {
        // update an object
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