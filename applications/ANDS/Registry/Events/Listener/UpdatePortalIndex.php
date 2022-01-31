<?php


namespace ANDS\Registry\Events\Listener;
use ANDS\Registry\Events\Event;
use ANDS\Util\Config;
use Exception;
use MinhD\SolrClient\SolrClient;

class UpdatePortalIndex
{

    public function handle(Event\PortalIndexUpdateEvent $event)
    {
        $json = $this->getSolrUpdateDoc($event);
        $solrClient = new SolrClient(Config::get('app.solr_url'));
        $solrClient->setCore("portal");
        $response = $solrClient->request("POST", "portal/update/json", ['commit' => 'true'], $json);

    }

    public function getSolrUpdateDoc(Event\PortalIndexUpdateEvent $event){

        $event->indexed_field;
        $event->search_value;
        $event->new_value;
        $json = array();
        $json["id"] = $event->registry_object_id;
        if($event->search_value == null){
            $json[$event->indexed_field] = ["set" => $event->new_value];
        }
        else{
            $json[$event->indexed_field] = ["remove" => $event->search_value, "add-distinct" => $event->new_value];
        }

        return "[".json_encode($json)."]";

    }


}