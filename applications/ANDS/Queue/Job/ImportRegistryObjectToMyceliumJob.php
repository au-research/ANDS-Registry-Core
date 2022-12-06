<?php

namespace ANDS\Queue\Job;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Queue\Job;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use MinhD\SolrClient\SolrClient;

class ImportRegistryObjectToMyceliumJob extends Job
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
        // process core metadata
        CoreMetadataProvider::process($record);

        // import graph
        $result = $myceliumClient->importRecord($record);
        if ($result->getStatusCode() != 200) {
            $reason = $result->getBody()->getContents();
            throw new \Exception("Failed to Import RegistryObject[registryObjectId=$this->registryObjectId] to Mycelium. Reason: $reason");
        }
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