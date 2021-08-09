<?php

namespace ANDS\Mycelium;


use ANDS\RegistryObject;
use GuzzleHttp\Client;

class MyceliumServiceClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * MyceliumServiceClient constructor.
     */
    public function __construct($url)
    {
        $this->setClient(new Client([
            'base_uri' => $url
        ]));
    }

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    public function ping()
    {
        $response = $this->client->get("api/info");
        return $response->getStatusCode() === 200;
    }

    /**
     * Import the record into Mycelium
     *
     * @param \ANDS\RegistryObject $record
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function importRecord(RegistryObject $record)
    {
        return $this->client->post("api/services/mycelium/import-record", [
            "headers" => ['Content-Type' => 'application/json'],
            "body" => json_encode(MyceliumImportPayloadProvider::get($record))
        ]);
    }

    /**
     * Perform a relationship indexing for a record via Mycelium
     *
     * @param \ANDS\RegistryObject $record
     * @return \Psr\Http\Message\ResponseInterface
     */
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