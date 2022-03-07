<?php

namespace ANDS\Registry\API\Controller;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Registry\API\Request;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;

class RecordsNestedCollectionController
{
    /**
     * Serves /api/registry/records/:id/nested-collection
     *
     * todo caching
     * @param $id
     * @return array
     */
    public function index($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);

        // obtain the graph data from MyceliumService
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));

        // get parents graph
        $result = $myceliumClient->getNestedCollectionParents($record->id);
        $graph = json_decode($result->getBody()->getContents(), true);

        return [$graph];
    }

    public function children($id) {
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));

        $offset = Request::get('offset');
        $limit = Request::get('limit');
        $excludeIDs = Request::get('excludeIDs');

        $result = $myceliumClient->getNestedCollectionChildren($id, $offset, $limit, $excludeIDs);
        $children = json_decode($result->getBody()->getContents(), true);
        return $children;
    }
}