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

    function run()
    {
        $record = RegistryObjectsRepository::getRecordByID($this->registryObjectId);

        if (!$record) {
            // todo log
            return;
        }

        // process
        CoreMetadataProvider::process($record);

        // todo determine the jobs that needs to run to fully sync a record
        // import graph to mycelium
        // index relationships
        // index portal

        // index relationships
//        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));
//        $myceliumClient->indexRecord($record);
//
//        try {
//            $portalIndex = RIFCSIndexProvider::get($record);
//            $solrClient = new SolrClient(Config::get('app.solr_url'));
//            $solrClient->setCore("portal");
//            $solrClient->request("POST", "portal/update/json/docs", ['commit' => 'true'],
//                json_encode($portalIndex), "body");
//        } catch (\Exception $e) {
//
//        }
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