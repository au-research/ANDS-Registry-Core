<?php


namespace ANDS\Registry\Events\Listener;
use ANDS\Registry\Events\Event;
use ANDS\Util\Config;
use Exception;
use MinhD\SolrClient\SolrClient;

/**
 * Class UpdatePortalIndex
 * @package ANDS\Registry\Events\Listener
 */
class UpdatePortalIndex
{

    /**
     * @param Event\PortalIndexUpdateEvent $event
     * @throws Exception
     */
    public function handle(Event\PortalIndexUpdateEvent $event)
    {
        if($event->registry_object_id == null && $event->search_value == null ){
            throw new Exception("registry_object_id and search_value should not be null together");
        }
        $this->processEvent($event);
    }

    /**
     * Process The PortaIndexUpdate Events
     * @param Event\PortalIndexUpdateEvent $event
     */
    public function processEvent(Event\PortalIndexUpdateEvent $event){

        $event->indexed_field;
        $event->search_value;
        $event->new_value;
        $json = array();

        if($event->registry_object_id != null){
            // single record update
            $this->updatePortalIndex($event);
        }else{
            // this is a batch update we need to find all instances of the old value in every documents
            //and update them in a bacth of say 400
            $this->updateAllMatchingPortalIndex($event);
        }
    }

    /**
     * the Event handler if the event contains a registry_object_id
     * @param $event
     */
    public function updatePortalIndex($event){
        $jsonPackets = array();
        $json["id"] = $event->registry_object_id;

        if($event->search_value == null){
            $json[$event->indexed_field] = ["set" => $event->new_value];
        }
        else{
            $json[$event->indexed_field] = ["remove" => $event->search_value, "add-distinct" => $event->new_value];
        }
        $jsonPackets[] = $json;
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");
        $solrClient->request("POST", "portal/update/json", ['commit' => 'true'],
            json_encode($jsonPackets), "body");
    }


    /**
     * the Event handler if the event contains no registry_object_id
     * it finds ALL records in the portal Index that has the given search value in the given index field
     * and replaces them with the new_value
     * @param $event
     */
    public function updateAllMatchingPortalIndex($event){
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");
        $query = array();
        $batchSize = 1;
        $query["fl"] = "id";
        $query["rows"] = $batchSize;
        $query["start"] = 0;
        $query["q"] = $event->indexed_field.':"'.$event->search_value.'"';

        // finally a case for a do while loop !!!
        do {
            $result = $solrClient->request("GET", "portal/select", $query);
            $targetRecordIds = array();
            foreach($result["response"]["docs"] as $record){
                $targetRecordIds[] = $record["id"];
            }
            $this->updatePortalIndexes($targetRecordIds, $event->indexed_field, $event->search_value, $event->new_value);
        } while (sizeof($result["response"]["docs"]) > 0);

    }

    /**
     * updates Portal indexes for all ids in the idList with the specified values
     * removes the search_value and adds the new value in the given index field
     * @param $idList
     * @param $indexed_field
     * @param $search_value
     * @param $new_value
     */
    public function updatePortalIndexes($idList, $indexed_field, $search_value, $new_value ){

        $jsonPackets = array();
        foreach($idList as $id){
            $json = array();
            $json["id"] = $id;
            $json[$indexed_field] = ["remove" => $search_value, "add-distinct" => $new_value];
            $jsonPackets[] = $json;
        }
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");
        $solrClient->request("POST", "portal/update/json", ['commit' => 'true'],
            json_encode($jsonPackets), "body");
    }


}