<?php

namespace ANDS\Mycelium;


use ANDS\DataSource;
use ANDS\Log\Log;
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

    public function info()
    {
        return $this->client->get("api/info");
    }

    /**
     * Import the record into Mycelium
     *
     * @param \ANDS\RegistryObject $record
     * @param $requestId
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function importRecord(RegistryObject $record, $requestId = null)
    {
        Log::debug(__METHOD__ . " Importing Record to Mycelium", ["id" => $record->id, 'requestId' => $requestId]);

        $query = [];
        if ($requestId !== null) {
            $query = [
                'requestId' => $requestId
            ];
        }

        return $this->client->post("api/resources/mycelium-registry-objects/", [
            "headers" => ['Content-Type' => 'application/json'],
            "body" => json_encode(MyceliumImportPayloadProvider::get($record)),
            "query" => $query
        ]);
    }

    public function deleteRecord($registryObjectId, $requestId = null) {

        Log::debug(__METHOD__ . " Deleting Record in Mycelium", ["id" => $registryObjectId, 'requestId' => $requestId]);

        $query = [];
        if ($requestId !== null) {
            $query = [
                'requestId' => $requestId
            ];
        }

        return $this->client->delete("api/resources/mycelium-registry-objects/$registryObjectId", [
            "headers" => ['Content-Type' => 'application/json'],
            "query" => $query
        ]);
    }

    /**
     * Perform a relationship indexing for a record via Mycelium
     *
     * @param \ANDS\RegistryObject $record
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function indexRecord(RegistryObject $record, $allowSuperNodes = false)
    {
        Log::debug(__METHOD__ . " Indexing Record in Mycelium", ["id" => $record->id]);
        return $this->client->post("api/services/mycelium/index-record", [
            "headers" => [],
            "query" => [
                "registryObjectId" => $record->id,
                'allowSuperNodes' => $allowSuperNodes
            ]
        ]);
    }


    /**
     * Perform a relationship indexing for a record via Mycelium
     *
     * @param \ANDS\RegistryObject $record
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function findRecordByIdentifier($identifier_value, $identifier_type)
    {
        Log::debug(__METHOD__ . " looking for a record with identifier and type Record in Mycelium", ["id" => $identifier_value, "type"=>$identifier_type]);
        return $this->client->get("api/resources/mycelium-registry-objects/find_by_identifier", [
            "headers" => [],
            "query" => [
                "identifier" => $identifier_value,
                'identifier_type' => $identifier_type
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

    public function createNewDeleteRecordRequest()
    {
        return $this->client->post("api/resources/mycelium-requests/", [
            "headers" => ['Content-Type' => 'application/json'],
            "body" => json_encode(["type" => "mycelium-delete"])
        ]);
    }

    public function createNewImportRecordRequest($batchID) {
        Log::debug(__METHOD__ . " Creating ImportRequest in Mycelium", ["batchId" => $batchID]);

        return $this->client->post("api/resources/mycelium-requests/", [
            "headers" => ['Content-Type' => 'application/json'],
            "query" => [
                "batchID" => $batchID
            ],
            "body" => json_encode([
                "type" => "mycelium-import"
            ])
        ]);
    }

    public function getRequestById($uuid) {
        return $this->client->get("api/resources/mycelium-requests/$uuid");
    }

    public function getRequestLogById($uuid) {
        return $this->client->get("api/resources/mycelium-requests/$uuid/logs");
    }

    public function getRequestQueueById($uuid) {
        return $this->client->get("api/resources/mycelium-requests/$uuid/queue");
    }

    public function startProcessingSideEffectQueue($requestId) {
        return $this->client->post("api/services/mycelium/start-queue-processing", [
            "query" => ["requestId" => $requestId]
        ]);
    }

    /**
     * get a graph fro any given Identifier and type
     * (doesn't have to be an actual RegistryObject)
     * @param $identifier_value
     * @param $identifier_type
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getIdentifierGraph($identifier_value, $identifier_type)
    {
        Log::debug(__METHOD__ . " Obtaining Identifier Graph", ["identifier" => $identifier_value]);
        try{
            // if identifier vertex doesn't exist an exception is thrown
            return $this->client->get("api/resources/mycelium-identifier-objects/graph?identifier_value={$identifier_value}&identifier_type={$identifier_type}");
        }catch(\Exception $e){
            return null;
        }
    }

    public function getRecordGraph($recordId)
    {
        Log::debug(__METHOD__ . " Obtaining Record Graph", ["id" => $recordId]);
        try{
            // if vertex doesn't exist an exception is thrown
            return $this->client->get("api/resources/mycelium-registry-objects/{$recordId}/graph");
        }catch(\Exception $e){
            return null;
        }
    }
    /**
     * Get duplicates for a record via Mycelium
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getDuplicateRecords($registryObjectId)
    {
        Log::debug(__METHOD__ . " Get Duplicate Records", ["id" => $registryObjectId]);

        return $this->client->get("api/resources/mycelium-registry-objects/{$registryObjectId}/duplicates");
    }

    /**
     * Create a new dataSource in Mycelium
     *
     * @param \ANDS\DataSource $dataSource
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createDataSource(DataSource $dataSource) {
        Log::debug(__METHOD__ . " Creating new DataSource", ["id" => $dataSource->id]);

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
        Log::debug(__METHOD__ . " Updating DataSource", ["id" => $dataSource->id]);

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
        Log::debug(__METHOD__ . " Deleting DataSource", ["id" => $dataSource->id]);

        return $this->client->delete("api/resources/mycelium-datasources/$dataSource->id", [
            "headers" => ['Content-Type' => 'application/json']
        ]);
    }

    public function getNestedCollectionParents($registryObjectId)
    {
        Log::debug(__METHOD__, ["id" => $registryObjectId]);

        return $this->client->get("api/resources/mycelium-registry-objects/{$registryObjectId}/nested-collection-parents");
    }

    public function getNestedCollectionChildren($registryObjectId, $offset, $limit, $excludeIdentifiers)
    {
        Log::debug(__METHOD__ , ["id" => $registryObjectId]);

        return $this->client->get("api/resources/mycelium-registry-objects/{$registryObjectId}/nested-collection-children", [
            "headers" => [],
            "query" => [
                "offset" => $offset,
                "limit" => $limit,
                "excludeIdentifiers" => $excludeIdentifiers
            ]
        ]);
    }

    public function deleteDataSourceRecords(DataSource $dataSource) {
        Log::debug(__METHOD__ , ["id" => $dataSource->id]);

        return $this->client->delete("api/resources/mycelium-datasources/{$dataSource->id}/vertices", [
            "headers" => ['Content-Type' => 'application/json']
        ]);
    }

    public function createBackup($backupId, $dataSourceId) {
        Log::debug(__METHOD__ , ["backupId" => $backupId, "dataSourceId" => $dataSourceId]);

        return $this->client->post("api/resources/mycelium-backups/", [
            "headers" => [],
            "query" => [
                "backupId" => $backupId,
                "dataSourceId" => $dataSourceId
            ]
        ]);
    }

    public function restoreBackup($backupId, $dataSourceId, $correctedDataSourceId) {
        Log::debug(__METHOD__ , ["backupId" => $backupId, "dataSourceId" => $dataSourceId]);

        return $this->client->post("api/resources/mycelium-backups/$backupId/_restore", [
            "headers" => [],
            "query" => [
                "dataSourceId" => $dataSourceId,
                "correctedDataSourceId" => $correctedDataSourceId
            ]
        ]);
    }

    public function getIdentifiers(RegistryObject $record){
        Log::debug(__METHOD__ . "Getting Identifiers for", ["id" => $record->id]);
        return $this->client->get("api/resources/mycelium-registry-objects/$record->id/identifiers");
    }

    public function getVertex(RegistryObject $record){
        Log::debug(__METHOD__ . "Getting Vertex for", ["id" => $record->id]);
        return $this->client->get("api/resources/mycelium-registry-objects/$record->id");
    }

    public function resolveIdentifier($value, $type){
        Log::debug(__METHOD__ . "Resolving ".$type. " identifer ".$value, []);
        return $this->client->get("api/services/mycelium/resolve-identifiers?value=".$value."&type=".$type);
    }


    public function normaliseIdentifier($value, $type){
        Log::debug(__METHOD__ . "Normalising ".$type. " identifer ".$value, []);
        return $this->client->get("api/services/mycelium/normalise-identifiers?value=".$value."&type=".$type);
    }

    public function updateIdentifierTitle($identifier_value, $identifier_type, $title)
    {
        Log::debug(__METHOD__ . " Updating Identifier's title", ["identifier" => $identifier_value, "title" => $title]);
        try{
            // if identifier vertex doesn't exist an exception is thrown
            return $this->client->post("api/resources/mycelium-identifier-objects/update-title?identifier_value={$identifier_value}&identifier_type={$identifier_type}&title={$title}");
        }catch(\Exception $e){
            return null;
        }
    }
}