<?php

namespace ANDS\Registry\API\Controller;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;

class RecordsRelationshipController
{
    public function index($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        $relationships = RelationshipProvider::getMergedRelationships($record);
        $result = [];
        foreach ($relationships as $key=>$relation) {
            $result[$key] = $relation->format();
        }
        return $result;
    }
}