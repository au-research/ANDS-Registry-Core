<?php

namespace ANDS\Queue\Job;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Queue\Job;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use MinhD\SolrClient\SolrClient;

class SyncRegistryObjectJob extends Job
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
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");

        // if this record is deleted, remove mycelium data (& relationships) and solr
        if ($record->status === 'DELETED') {
            $result = $myceliumClient->deleteRecord($this->registryObjectId);
            if ($result->getStatusCode() != 200) {
                $reason = $result->getBody()->getContents();
                throw new \Exception("Failed to Delete RegistryObject[registryObjectId=$this->registryObjectId] to Mycelium. Reason: $reason");
            }
            $solrClient->remove($this->registryObjectId);
            if ($solrClient->hasError()) {
                $reason = join(',', $solrClient->getErrors());
                throw new \Exception("Failed to delete portal SOLR for RegistryObject[registryObjectId=$this->registryObjectId]. Reason: $reason");
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

        // index relationships with retries
        // this offers some breathing room for import task to fully committed the change and avoid 404s on the vertex
        retry(function() use($record, $myceliumClient) {
            $result = $myceliumClient->indexRecord($record);
            if ($result->getStatusCode() != 200) {
                $reason = $result->getBody()->getContents();
                throw new \Exception("Failed to Index Relationship for RegistryObject[registryObjectId=$this->registryObjectId] to Mycelium. Reason: $reason");
            }
        }, 2, 3);

        // only PUBLISHED records proceed past this point
        if ($record->isDraftStatus()) {
            return;
        }

        // index portal
        $portalIndex = RIFCSIndexProvider::get($record);
        $solrClient->request("POST", "portal/update/json/docs", ['commit' => 'true'],
            json_encode($portalIndex), "body");
        if ($solrClient->hasError()) {
            $reason = join(',', $solrClient->getErrors());
            // id topology Exception occured crate a solr index without spatial data
            if(str_contains($reason, 'org.locationtech.jts.geom.TopologyException')){
                $portalIndex = RIFCSIndexProvider::get($record, false);
                $solrClient->request("POST", "portal/update/json/docs", ['commit' => 'true'],
                    json_encode($portalIndex), "body");
                if ($solrClient->hasError()) {
                    throw new \Exception("Failed to index portal SOLR for RegistryObject[registryObjectId=$this->registryObjectId]. Reason: $reason");
                }
            }else{
                throw new \Exception("Failed to index portal SOLR for RegistryObject[registryObjectId=$this->registryObjectId]. Reason: $reason");
            }
        }

        // todo alternate versions
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