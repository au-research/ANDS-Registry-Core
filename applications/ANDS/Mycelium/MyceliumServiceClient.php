<?php

namespace ANDS\Mycelium;


use ANDS\RegistryObject;
use GraphAware\Neo4j\Client\ClientBuilder;
use GuzzleHttp\Client;

class MyceliumServiceClient
{
    private $client;

    /**
     * MyceliumServiceClient constructor.
     */
    public function __construct($url)
    {
        $this->client = new Client([
            'base_uri' => $url
        ]);
    }

    public function ping() {
        $response = $this->client->get("api/info");
        return $response->getStatusCode() === 200;
    }

    public function importRecord(RegistryObject $record)
    {
        return $this->client->post("api/services/import/", [
            "headers" => [],
            "body" => $record->getCurrentData()->data
        ]);
    }

    public function indexRecord(RegistryObject $record)
    {
        return $this->client->post("api/services/mycelium/index-record", [
            "headers" => [],
            "query" => [
                "registryObjectId" => $record->id
            ]
        ]);
    }
}