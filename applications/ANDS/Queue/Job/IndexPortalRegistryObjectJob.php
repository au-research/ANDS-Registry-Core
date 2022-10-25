<?php

namespace ANDS\Queue\Job;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Queue\Job;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use MinhD\SolrClient\SolrClient;

class IndexPortalRegistryObjectJob extends Job
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
        if ($record->isDraftStatus()) {
            return;
        }
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");
        // if this record is deleted, remove mycelium data (& relationships) and solr
        if ($record->status === 'DELETED') {
            $solrClient->remove($this->registryObjectId);
            if ($solrClient->hasError()) {
                $reason = join(',', $solrClient->getErrors());
                throw new \Exception("Failed to delete portal SOLR for RegistryObject[registryObjectId=$this->registryObjectId]. Reason: $reason");
            }
            return;
        }
        $portalIndex = RIFCSIndexProvider::get($record);
        $solrClient->request("POST", "portal/update/json/docs", ['commit' => 'true'],
            json_encode($portalIndex), "body");
        if ($solrClient->hasError()) {
            $reason = join(',', $solrClient->getErrors());
            // if topology Exception occurred crate a solr index without spatial data
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