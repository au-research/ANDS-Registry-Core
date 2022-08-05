<?php


namespace ANDS\Registry\Events\Listener;
use ANDS\Registry\Events\Event;
use ANDS\Util\Config;
use Exception;
use MinhD\SolrClient\SolrClient;
use ANDS\Repository\RegistryObjectsRepository;

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
        debug("PortalIndexUpdateEvent f:".$event->indexed_field ." s:". $event->search_value
            ." n:".$event->new_value . " i:" . $event->registry_object_id ." t". $event->relationship_types);
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
            if($event->indexed_field == "related_party_one_title" || $event->indexed_field == "related_party_multi_title"){
                $this->updateGrantsNetworkPortalIndex($event);
            }
        }else{
            // this is a batch update we need to find all instances of the old value in every documents
            //and update them in a bacth of say 400
            $this->updateAllMatchingPortalIndex($event);
            if($event->indexed_field == "related_party_one_title" || $event->indexed_field == "related_party_multi_title"){
                $this->updateMatchingGrantsNetworkPortalIndexes($event);
            }

        }
    }

    /**
     * the Event handler if the event contains a registry_object_id
     * @param $event
     */
    public function updatePortalIndex($event){
        $jsonPackets = array();
        $json["id"] = $event->registry_object_id;
        $actions = array();
        if($event->search_value === null){
            $actions["set"] = $event->new_value;
        }
        else{
            if($event->new_value !== null){
                $actions["add-distinct"] = $event->new_value;
            }
            if($event->search_value !== ""){
                $actions["remove"] = $event->search_value;
            }
        }
        $json[$event->indexed_field] = $actions;
        $jsonPackets[] = $json;
        $this->updateSolr(json_encode($jsonPackets));
    }


    /**
     * updateGrantsNetworkPortalIndex
     * If the record is an activity and it's related to a party
     * with a specific relationship type
     * other fields may contain the related party's title
     * for more info read GrantsmetadataProvider
     * the Event handler if the event contains a registry_object_id
     * @param $event
     */
    public function updateGrantsNetworkPortalIndex($event){
        $jsonPackets = array();
        $record = RegistryObjectsRepository::getRecordByID($event->registry_object_id);

        if($record->class == "activity" && $event->relationship_types != null){
            $aRelationshipTypes = explode (",", $event->relationship_types );

            foreach($aRelationshipTypes as $relationshiType)
            {
                $indexed_fields = [];
                // multi aka groups can be funders or administering institutions
                // add the reverse type as well
                if($event->indexed_field == "related_party_multi_title"){
                    switch (trim($relationshiType)){
                        case "isFundedBy":
                        case "isFunderOf":
                            $indexed_fields = ["funders"];
                            break;
                        case "isManagedBy":
                        case "isManagerOf":
                            $indexed_fields = ["administering_institution","institutions"];
                            break;
                        default:
                            $indexed_fields = ["institutions"];
                            break;
                    }
                }else{
                    // people can be researchers or principal Investigators
                    switch (trim($relationshiType)){
                        case "hasPrincipalInvestigator":
                        case "isPrincipalInvestigatorOf":
                        case "Chief Investigator":
                        case "Principal Investigator":
                            $indexed_fields = ["principal_investigator","researchers"];
                            break;
                        case "Partner Investigator":
                        case "hasParticipant":
                        case "isParticipantIn":
                        case "isAssociatedWith":
                            $indexed_fields = ["researchers"];
                            break;
                    }
                }

                // if the relationship type resulted in a grantsnetwork relationship update the corresponding field
                if(sizeof($indexed_fields) > 0) {
                    foreach($indexed_fields as $indexed_field){
                        $json["id"] = $event->registry_object_id;
                        $actions = array();
                        if ($event->search_value == null) {
                            $actions["set"] = $event->new_value;
                        } else {
                            $actions["remove"] = $event->search_value;
                            if ($event->new_value != null) {
                                $actions["add-distinct"] = $event->new_value;
                            }
                        }
                        $json[$indexed_field] = $actions;
                        $jsonPackets[] = $json;
                        $this->updateSolr(json_encode($jsonPackets));
                    }
                }
            }
        }

    }

   private function updateMatchingGrantsNetworkPortalIndexes($event){
        // this is a bit trickier since we don't know the relationship types
       // so we find ALL activities based on specific fields that may contain the title we seek to update


       // find all activities that have the given party record as any of the grant network index
       if($event->indexed_field == "related_party_multi_title"){
           $indexed_fields = ["funders", "administering_institution", "institutions"];
       }else{
            $indexed_fields = ["principal_investigator","researchers"];
       }
       $solrClient = new SolrClient(Config::get('app.solr_url'));
       $solrClient->setCore("portal");

       foreach($indexed_fields as $indexed_field){
           $query = array();
           $batchSize = 400;
           $query["fl"] = "id";
           $query["rows"] = $batchSize;
           $query["start"] = 0;
           $query["q"] = $indexed_field.':"'.$event->search_value.'"';
           $query["fq"] = 'class:activity';
           // keep all records processed in case we end up looping back to them
           $processedRecordIds = array();
           // finally a case for a do while loop !!!
           do {
               $result = $solrClient->request("GET", "portal/select", $query);
               $targetRecordIds = array();
               foreach($result["response"]["docs"] as $record){
                   if(!in_array($record["id"], $processedRecordIds)){
                       $targetRecordIds[] = $record["id"];
                       $processedRecordIds[] = $record["id"];
                   }

               }
               if(sizeof($targetRecordIds) > 0){
                   $this->updatePortalIndexes($targetRecordIds, $indexed_field, $event->search_value, $event->new_value);
               }
               $query["start"] += $batchSize;
           } while (sizeof($result["response"]["docs"]) > 0 && sizeof($targetRecordIds) > 0);
       }
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
        $batchSize = 400;
        $query["fl"] = "id";
        $query["rows"] = $batchSize;
        $query["start"] = 0;
        $query["q"] = $event->indexed_field.':"'.$event->search_value.'"';
        // keep all records processed in case we end up looping back to them
        $processedRecordIds = array();
        // finally a case for a do while loop !!!
        do {
            $result = $solrClient->request("GET", "portal/select", $query);
            $targetRecordIds = array();
            foreach($result["response"]["docs"] as $record){
                if(!in_array($record["id"], $processedRecordIds)){
                    $targetRecordIds[] = $record["id"];
                    $processedRecordIds[] = $record["id"];
                }

            }
            if(sizeof($targetRecordIds) > 0){
                $this->updatePortalIndexes($targetRecordIds, $event->indexed_field, $event->search_value, $event->new_value);
            }
            $query["start"] += $batchSize;
        } while (sizeof($result["response"]["docs"]) > 0 && sizeof($targetRecordIds) > 0);

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
            if(!$this->hasPortalIndex($id)){
                continue;
            }
            $json = array();
            $actions = array();
            $json["id"] = $id;
            $actions["remove"] = $search_value;

            if($new_value != null){
                $actions["add-distinct"] = $new_value;
            }
            $json[$indexed_field] = $actions;
            $jsonPackets[] = $json;
        }
        if(sizeof($jsonPackets) > 0){
            $this->updateSolr(json_encode($jsonPackets));
        }

    }

    private function updateSolr($jsonBody){
        debug("updatePortalIndexe(s) q:".$jsonBody);
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");
        $solrClient->request("POST", "portal/update/json", ['commit' => 'true'], $jsonBody, "body");
    }

    /** Check if the record exists, and it is PUBLISHED before attempting to update its portal index
     * @param $ro_id
     * @return false|void
     */
    public function hasPortalIndex($ro_id){
        // we assume every PUBLISHED record has a portal index
        // if bug RDA-720 still exists we should test the actual portal index (but that is too slow)
        $record = RegistryObjectsRepository::getRecordByID($ro_id);
        if($record === null || $record->isDraftStatus()){
            return false;
        }
        return true;
    }
}