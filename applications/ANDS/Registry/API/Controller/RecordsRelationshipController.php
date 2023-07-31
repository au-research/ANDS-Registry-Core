<?php

namespace ANDS\Registry\API\Controller;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;

class RecordsRelationshipController
{
    public function index($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        $relationships = RelationshipProvider::get($record);

        // filters
        $validFilters = ['to_class', 'to_type', 'relation_type', 'relation_origin', 'to_group', 'to_data_source_id', 'to_id', 'to_key', 'to_title'];
        foreach ($validFilters as $filter) {
            if ($value = request($filter)) {
                $relationships = collect($relationships)->filter(function($item) use ($value, $filter) {
                    return $item->prop($filter) == $value;
                });
            }
        }

        // format the response
        return $relationships;
    }
}