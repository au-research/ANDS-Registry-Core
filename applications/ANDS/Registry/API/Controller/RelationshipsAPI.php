<?php

namespace ANDS\Registry\API\Controller;

use ANDS\Mycelium\RelationshipSearchService;
use ANDS\Registry\API\Request;

class RelationshipsAPI extends HTTPController
{
    public static $paginationParameters = [
        'rows',
        'offset',
        'sort',
        'boost_relation_type',
        'boost_to_group',
        'fl',
        'relations_fl',
        'relations_limit',
    ];

    public static $searchParameters = [
        'from_id',
        'from_key',
        'to_identifier',
        'to_identifier_type',
        'to_class',
        'to_type',
        'not_to_type',
        'relation_type',
        'include_reverse',
        'include_external',
        'relation_origin'
    ];

    public function index()
    {
        $result = RelationshipSearchService::search(
            Request::only(static::$searchParameters),
            Request::only(static::$paginationParameters)
        );

        return $result->toJson();
    }
}