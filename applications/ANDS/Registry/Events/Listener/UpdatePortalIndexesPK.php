<?php

namespace ANDS\Registry\Events\Listener;

use ANDS\Registry\Events\Event;
use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\Util\Config;
use Exception;
use MinhD\SolrClient\SolrClient;
use ANDS\Repository\RegistryObjectsRepository;
/**
 *
 * This Listener should be called when a datasource primary key is updated (added or removed)
 *
 */
class UpdatePortalIndexesPK
{
    /**
     * @param Event\PrimaryKeyUpdatedEvent $event
     * @throws Exception
     */
    public function handle(Event\PrimaryKeyUpdatedEvent $event)
    {
        if($event->data_source_id == null && $event->data_source_id == null ){
            throw new Exception("data_source_id must be provided");
        }

        if($event->old_primary_key == null && $event->new_primary_key == null ){
            throw new Exception("Either old or new primary_key must be provided");
        }

        debug("PrimaryKeyUpdatedEvent ds_id:".$event->data_source_id ." old_pk:". $event->old_primary_key
            ." new_pk:".$event->new_primary_key . " ar:" . $event->activity_relationship_type ." pr". $event->party_relationship_type
            ." sr:".$event->service_relationship_type." cr:".$event->collection_relationship_type);
        $this->processEvent($event);
    }

    /**
     * Process The PortaIndexUpdatePK Events
     * @param Event\PrimaryKeyUpdatedEvent $event
     */
    public function processEvent(Event\PrimaryKeyUpdatedEvent $event)
    {
        /**
         * determine what needs to be done:
         * if no old_primary_key then there is nothing to delete
         * if no new_primary_key then there is nothing to add
         * check primary record class
         * if record is not collection then its portal index also needs to be updated
         * if '*' action is required (except collections' titles arent recorded in the related objects' indexes
         * primary key class, target: party, activity, collection, service
         * --------------------------------------------------------------|
         * Party     *  |             *   |   *    |    *  |      *      |
         * -------------|------------------------------------------------|
         * Activity  *  |             *   |   *    |    *  |      *      |
         * -------------|------------------------------------------------|
         * Collection * |             -   |   -    |    -  |      -      |
         * -------------|------------------------------------------------|
         * Service  *   |             *   |   *    |    *  |      *      |
         * ______________________________________________________________|
         * check relationship
         *
         */
        if($event->old_primary_key !== null){
            $this->processRemoveOldPrimaryKey($event);
        }
        if($event->new_primary_key !== null){
            $this->processAddNewPrimaryKey($event);
        }
    }

    private function processAddNewPrimaryKey(Event\PrimaryKeyUpdatedEvent $event){
        $primary_record = RegistryObjectsRepository::getPublishedByKey($event->new_primary_key);
        $data_source_id = $event->data_source_id;
        $pr_title = $primary_record->title;
        $pr_class = $primary_record->class;
        $pr_type = $primary_record->type;
        $indexed_field = "related_".$pr_class;
        if($pr_class === 'party' && strtolower($pr_type) === 'group'){
            $indexed_field .= '_multi_title';
        }elseif($pr_class === 'party'){
            $indexed_field .= '_one_title';
        }else{
            $indexed_field .= '_title';
        }

        if($pr_class !== 'collection'){
            // TODO: find all activities and add the primary key's title to their index in portal
            if($event->activity_relationship_type !== null && $event->activity_relationship_type !== ""){
                $this->addPrimaryRecordToPortalIndex($data_source_id, 'activity', $indexed_field, $pr_title);
            }
            if($event->party_relationship_type !== null && $event->party_relationship_type !== ""){
                $this->addPrimaryRecordToPortalIndex($data_source_id, 'party', $indexed_field, $pr_title);
            }
            if($event->collection_relationship_type !== null && $event->collection_relationship_type !== ""){
                $this->addPrimaryRecordToPortalIndex($data_source_id, 'collection', $indexed_field, $pr_title);
            }
            if($event->service_relationship_type !== null && $event->service_relationship_type !== ""){
                $this->addPrimaryRecordToPortalIndex($data_source_id, 'service', $indexed_field, $pr_title);
            }
        }
        RIFCSIndexProvider::indexRecord($primary_record);
    }

    private function addPrimaryRecordToPortalIndex($data_source_id, $target_class, $indexed_field, $value){
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");
        $query = array();
        $batchSize = 400;
        $query["fl"] = "id";
        $query["rows"] = $batchSize;
        $query["start"] = 0;
        $query["q"] = "+data_source_id:".$data_source_id." +class:".$target_class;

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
                $this->updatePortalIndexes($targetRecordIds, $indexed_field, "", $value);
            }
            $query["start"] += $batchSize;
        } while (sizeof($result["response"]["docs"]) > 0 && sizeof($targetRecordIds) > 0);


    }


    private function processRemoveOldPrimaryKey(Event\PrimaryKeyUpdatedEvent $event){
        $primary_record = RegistryObjectsRepository::getPublishedByKey($event->new_primary_key);
        $data_source_id = $event->data_source_id;
        $pr_title = $primary_record->title;
        $pr_class = $primary_record->class;
        $pr_type = $primary_record->type;
        $indexed_field = "related_".$pr_class;
        if($pr_class === 'party' && strtolower($pr_type) === 'group'){
            $indexed_field .= '_multi_title';
        }elseif($pr_class === 'party'){
            $indexed_field .= '_one_title';
        }else{
            $indexed_field .= '_title';
        }

        if($pr_class !== 'collection'){
            // TODO: find all activities and add the primary key's title to their index in portal
            if($event->activity_relationship_type !== null && $event->activity_relationship_type !== ""){
                $this->RemoveAllMatchingPortalIndex($data_source_id, 'activity', $indexed_field, $pr_title);
            }
            if($event->party_relationship_type !== null && $event->party_relationship_type !== ""){
                $this->RemoveAllMatchingPortalIndex($data_source_id, 'party', $indexed_field, $pr_title);
            }
            if($event->collection_relationship_type !== null && $event->collection_relationship_type !== ""){
                $this->RemoveAllMatchingPortalIndex($data_source_id, 'collection', $indexed_field, $pr_title);
            }
            if($event->service_relationship_type !== null && $event->service_relationship_type !== ""){
                $this->RemoveAllMatchingPortalIndex($data_source_id, 'service', $indexed_field, $pr_title);
            }
        }
        // reindex the primary record
        RIFCSIndexProvider::indexRecord($primary_record);
    }

    /**
     * the Event handler if the event contains no registry_object_id
     * it finds ALL records in the portal Index that has the given search value in the given index field
     * and replaces them with the new_value
     * @param $event
     */
    public function RemoveAllMatchingPortalIndex($data_source_id, $target_class, $indexed_field, $value){
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");
        $query = array();
        $batchSize = 400;
        $query["fl"] = "id";
        $query["rows"] = $batchSize;
        $query["start"] = 0;
        $query["fq"] = "+data_source_id:".$data_source_id." +class:".$target_class;
        $query["q"] = $indexed_field.':"'.$value.'"';
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
                $this->updatePortalIndexes($targetRecordIds, $indexed_field, $value, null);
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
    public function updatePortalIndexes($idList, $indexed_field, $old_value, $new_value ){

        $jsonPackets = array();

        foreach($idList as $id){
            $json = array();
            $actions = array();
            $json["id"] = $id;
            if($old_value != null){
                $actions["remove"] = $old_value;
            }
            if($new_value != null){
                $actions["add-distinct"] = $new_value;
            }
            $json[$indexed_field] = $actions;
            $jsonPackets[] = $json;
        }
        $this->updateSolr(json_encode($jsonPackets));
    }

    private function updateSolr($jsonBody){
        debug("updatePortalIndexe(s) q:".$jsonBody);
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");
        $solrClient->request("POST", "portal/update/json", ['commit' => 'true'], $jsonBody, "body");
    }

}