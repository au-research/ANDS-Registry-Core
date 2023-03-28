<?php

namespace ANDS\Registry\API\Controller;


use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Identifier;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;

// "/api/registry/records/503006/identifiers"
class RecordsIdentifiersController
{
    public function index($id)
    {
        $client = new MyceliumServiceClient(Config::get('mycelium.url'));
        $record = RegistryObjectsRepository::getRecordByID($id);
        $result = $client->getIdentifiers($record);
        return json_decode($result->getBody()->getContents());
    }
}