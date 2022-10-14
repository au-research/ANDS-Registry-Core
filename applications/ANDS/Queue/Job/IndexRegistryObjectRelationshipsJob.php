<?php

namespace ANDS\Queue\Job;

use ANDS\Log\Log;
use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Queue\Job;
use ANDS\Queue\QueueService;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use MinhD\SolrClient\SolrClient;
use function GuzzleHttp\Psr7\get_message_body_summary;

class IndexRegistryObjectRelationshipsJob extends Job
{
    private $registryObjectId;
    private $allow_supernodes;
    private $superNodesQueueID = "ardc:rda-registry:supernode-queue";
    function init(array $payload)
    {
        $this->registryObjectId = $payload['registry_object_id'];
        $this->allow_supernodes = isset($payload['allow_supernodes']) ? $payload['allow_supernodes'] : false;
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
        try{
            $result = $myceliumClient->indexRecord($record, $this->allow_supernodes);
            if ($result->getStatusCode() != 200) {
                $reason = $result->getBody()->getContents();
                throw new \Exception("Failed to Index Relationship for RegistryObject[registryObjectId=$this->registryObjectId] to Mycelium. Reason: $reason");
            }
        }catch(\Exception $e){
            // if Mycelium flagged the Record as Supernode (has more than 200 direct relationships)
            // then put the relationship indexing into a separate low priority (slow) queue
            // instead of blocking the rest of the records from getting indexed
            if(str_contains(get_exception_msg($e), "should be processed by the supernode queue")){
                //$record->setRegistryObjectAttribute("isSuperNode", true);
                $job = new IndexRegistryObjectRelationshipsJob();
                $job->init(['registry_object_id' => $this->registryObjectId, 'allow_supernodes' => true]);
                QueueService::init();
                QueueService::push($job, $this->superNodesQueueID);
                $job = new IndexPortalRegistryObjectJob();
                $job->init(['registry_object_id' => $this->registryObjectId]);
                QueueService::push($job, $this->superNodesQueueID);
                Log::info("Registry Object id:$this->registryObjectId will be processed by $this->superNodesQueueID");
            }else{
                throw new \Exception("Failed to Index Relationship for RegistryObject[registryObjectId=$this->registryObjectId] to Mycelium. Reason: $reason");
            }
        }

    }

    function toArray() {
        return [
            'registry_object_id' => $this->registryObjectId,
            'allow_supernodes' => $this->allow_supernodes
        ];
    }

    public function __toString()
    {
        return "Job[class=".get_class($this).", registryObjectId=$this->registryObjectId,allow_supernodes=$this->allow_supernodes]";
    }

}