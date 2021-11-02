<?php

namespace ANDS\Mycelium;


use ANDS\DataSource;
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


    public function getRecordGraph($recordId)
    {
        return $this->client->get("api/services/mycelium/get-record-graph", [
            "query" => ["registryObjectId" => $recordId]
        ]);

    }
    /**
     * Get duplicates for a record via Mycelium
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getDuplicateRecords($registryObjectId)
    {
        $response = $this->client->get("api/services/mycelium/get-duplicate-records", [
            "headers" => [],
            "query" => [
                "registryObjectId" => $registryObjectId
            ]
        ]);

        return $response->getBody();
    }

    /**
     * Create a new dataSource in Mycelium
     *
     * @param \ANDS\DataSource $dataSource
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createDataSource(DataSource $dataSource) {
        return $this->client->post("api/resources/mycelium-datasources/", [
            "headers" => ['Content-Type' => 'application/json'],
            "body" => json_encode(MyceliumDataSourcePayloadProvider::get($dataSource))
        ]);
    }

    /**
     * Update existing DataSource in Mycelium
     *
     * @param \ANDS\DataSource $dataSource
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function updateDataSource(DataSource $dataSource) {
        return $this->client->put("api/resources/mycelium-datasources/$dataSource->id", [
            "headers" => ['Content-Type' => 'application/json'],
            "body" => json_encode(MyceliumDataSourcePayloadProvider::get($dataSource))
        ]);
    }

    /**
     * Delete existing DataSource in Mycelium
     *
     * @param \ANDS\DataSource $dataSource
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteDataSource(DataSource $dataSource) {
        return $this->client->delete("api/resources/mycelium-datasources/$dataSource->id", [
            "headers" => ['Content-Type' => 'application/json']
        ]);
    }
}