<?php

namespace ANDS\Queue\Job;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Queue\Job;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use MinhD\SolrClient\SolrClient;

class IndexRegistryObjectRelationshipsJob extends Job
{
    private $registryObjectId;

    function init(array $payload)
    {
        $this->registryObjectId = $payload['registry_object_id'];
    }

    /**
     * @throws \Exception
     */
    function run()
    {
        $record = RegistryObjectsRepository::getRecordByID($this->registryObjectId);

        if (!$record) {
            throw new \Exception("No RegistryObject[registryObjectId=$this->registryObjectId] found");
        }

        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));

        // if this record is deleted, remove mycelium data (& relationships) and solr
        if ($record->status === 'DELETED') {
            $result = $myceliumClient->deleteRecord($this->registryObjectId);
            if ($result->getStatusCode() != 200) {
                $reason = $result->getBody()->getContents();
                throw new \Exception("Failed to Delete RegistryObject[registryObjectId=$this->registryObjectId] to Mycelium. Reason: $reason");
            }
            return;
        }
        // index relationships with retries
        // this offers some breathing room for import task to fully committed the change and avoid 404s on the vertex
        retry(function() use($record, $myceliumClient) {
            $result = $myceliumClient->indexRecord($record);
            if ($result->getStatusCode() != 200) {
                $reason = $result->getBody()->getContents();
                throw new \Exception("Failed to Index Relationship for RegistryObject[registryObjectId=$this->registryObjectId] to Mycelium. Reason: $reason");
            }
        }, 2, 3);

    }

    function toArray() {
        return [
            'registry_object_id' => $this->registryObjectId
        ];
    }

    public function __toString()
    {
        return "Job[class=".get_class($this).", registryObjectId=$this->registryObjectId]";
    }

}