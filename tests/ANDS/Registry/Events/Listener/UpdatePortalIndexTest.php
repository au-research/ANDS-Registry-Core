<?php


namespace ANDS\Registry\Events\Listener;

use ANDS\Registry\Events\EventServiceProvider;
use ANDS\Registry\Events\Listener\UpdatePortalIndex;
use ANDS\Registry\Events\Event\PortalIndexUpdateEvent;
use RegistryTestClass;

class UpdatePortalIndexTest extends RegistryTestClass
{


    public function testSolrUpdate(){
        $event = new PortalIndexUpdateEvent;
        $event->registry_object_id = "2";
        $event->indexed_field = "description_type";
        $event->search_value = "brief";
        $event->new_value= "case";

        $listeners = EventServiceProvider::getListeners($event);
        foreach ($listeners as $listener) {
            try {
                $listenerObj = new $listener;
                $listenerObj->handle($event);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
        // TODO: add a solr doc in setup and test for value change
    }

    public function testSolrUpdateReverse(){
        $event = new PortalIndexUpdateEvent;
        $event->registry_object_id = "1";
        $event->indexed_field = "related_party_multi_title";
        $event->search_value = "";
        $event->new_value= "P1 Example related CCC Barty party";

        $listeners = EventServiceProvider::getListeners($event);
        foreach ($listeners as $listener) {
            try {
                $listenerObj = new $listener;
                $listenerObj->handle($event);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
        // TODO: add a solr doc in setup and test for value change
    }


    function testGrantsNetworkUpdate(){
        $event = new PortalIndexUpdateEvent;
        $event->registry_object_id = 9;
        $event->indexed_field = "related_party_one_title";
        $event->search_value = "Leo was here";
        $event->new_value = "Leo is gone";
        $event->relationship_types = "Principal Investigator";
        $listeners = EventServiceProvider::getListeners($event);
        foreach ($listeners as $listener) {
            try {
                $listenerObj = new $listener;
                $listenerObj->handle($event);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    function testGetRecordIdsToUpdate(){
        $event = new PortalIndexUpdateEvent;
        $event->registry_object_id = 1;
        $event->indexed_field = "related_party_multi_title";
        $event->search_value = null;
        $event->new_value = null;

        $listeners = EventServiceProvider::getListeners($event);
        foreach ($listeners as $listener) {
            try {
                $listenerObj = new $listener;
                $listenerObj->handle($event);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

}