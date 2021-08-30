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
     * @param $sideEffectRequestId
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function importRecord(RegistryObject $record, $sideEffectRequestId)
    {
        return $this->client->post("api/services/mycelium/import-record", [
            "headers" => ['Content-Type' => 'application/json'],
            "body" => json_encode(MyceliumImportPayloadProvider::get($record)),
            "query" => [
                "sideEffectRequestID" => $sideEffectRequestId
            ]
        ]);
    }

    public function deleteRecord($registryObjectId, $sideEffectRequestId) {
        return $this->client->post("api/services/mycelium/delete-record", [
            "headers" => [],
            "query" => [
                "registryObjectId" => $registryObjectId,
                "sideEffectRequestID" => $sideEffectRequestId
            ]
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

    public function createNewAffectedRelationshipRequest()
    {
        return $this->client->post("api/resources/mycelium-requests/", [
            "headers" => ['Content-Type' => 'application/json'],
            "body" => json_encode(["type" => "mycelium-affected_relationships"])
        ]);
    }

    public function getRequestById($uuid) {
        return $this->client->get("api/resources/mycelium-requests/$uuid");
    }

    public function startProcessingSideEffectQueue($requestId) {
        return $this->client->post("api/services/mycelium/start-queue-processing", [
            "query" => ["requestId" => $requestId]
        ]);
    }
}