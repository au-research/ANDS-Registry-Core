<?php


namespace ANDS\Registry\Events\Listener;

use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Events\EventServiceProvider;
use ANDS\Registry\Events\Listener\UpdatePortalIndex;
use ANDS\Registry\Events\Event\PortalIndexUpdateEvent;
use ANDS\Registry\Providers\Quality\QualityMetadataProvider;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\RegistryObject;
use ANDS\Util\Config;
use MinhD\SolrClient\SolrClient;
use RegistryTestClass;

class UpdatePortalIndexTest extends \MyceliumTestClass
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

    function testPortalIndexRelatedpartyMultyUpdated(){
        {
            // RDA-492 with lots of new tests to see what caused the issues
            $solrClient = new SolrClient(Config::get('app.solr_url'));
            $solrClient->setCore("portal");
            // given a record with an author (party)
            $record = $this->stub(RegistryObject::class, ['class' => 'collection','type' => 'dataset','key' => 'AUT_QUALITY_COLLECTION']);
            $this->stub(RecordData::class, [
                'registry_object_id' => $record->id,
                'data' => Storage::disk('test')->get('rifcs/collection_for_related_quality_md.xml')
            ]);
            CoreMetadataProvider::process($record);
            $this->myceliumInsert($record);


            // with an author (party)
            $party = $this->stub(RegistryObject::class, ['class' => 'party','title'=> "Related,Party,One", 'type' => 'group','key' => 'AUT_QUALITY_PARTY']);

            $this->stub(RecordData::class, [
                'registry_object_id' => $party->id,
                'data' => Storage::disk('test')->get('rifcs/party_for_related_quality_md.xml')
            ]);
            CoreMetadataProvider::process($party);
            $this->myceliumInsert($party);


            RIFCSIndexProvider::indexRecord($record);

            RIFCSIndexProvider::indexRecord($party);

            $solrClient->commit();
            $doc = $solrClient->get($record->id)->toArray();
            $this->assertNotNull($doc);
            // the party should have been added by the portal indexing
            $this->assertEquals($party->title, $doc['related_party_multi_title'][0]);
            // change its title (what mycelium would trigger)
            EventServiceProvider::dispatch(PortalIndexUpdateEvent::from([
                'registry_object_id' => $record->id,
                'indexed_field' => "related_party_multi_title",
                'search_value' => $party->title,
                'new_value' => $party->title."UPDATED"
            ]));
            $solrClient->commit();

            $doc = $solrClient->get($record->id)->toArray();
            $this->assertNotNull($doc);

            $this->assertEquals($party->title."UPDATED", $doc['related_party_multi_title'][0]);
            $solrClient->commit();
            $doc = $solrClient->get($record->id)->toArray();
            $this->assertNotNull($doc);
            // add a new related_party_multi_title
            EventServiceProvider::dispatch(PortalIndexUpdateEvent::from([
                'registry_object_id' => $record->id,
                'indexed_field' => "related_party_multi_title",
                'new_value' => "FISH PARTY",
                'search_value' => ""
            ]));
            $doc = $solrClient->get($record->id)->toArray();
            $this->assertNotNull($doc);
            // we should have 2 related party_multi_titles
            $this->assertEquals($party->title."UPDATED", $doc['related_party_multi_title'][0]);
            $this->assertEquals("FISH PARTY", $doc['related_party_multi_title'][1]);


            $solrClient->commit();
            // no new_value means remove
            // remove the 1st (updated) related_party_multi_title
            EventServiceProvider::dispatch(PortalIndexUpdateEvent::from([
                'registry_object_id' => $record->id,
                'indexed_field' => "related_party_multi_title",
                'search_value' => $party->title."UPDATED"
            ]));
            $solrClient->commit();
            $doc = $solrClient->get($record->id)->toArray();
            $this->assertNotNull($doc);
            $this->assertEquals(1, sizeof($doc['related_party_multi_title']));
            $this->assertEquals("FISH PARTY", $doc['related_party_multi_title'][0]);


            // remove the newly added party title
            EventServiceProvider::dispatch(PortalIndexUpdateEvent::from([
                'registry_object_id' => $record->id,
                'indexed_field' => "related_party_multi_title",
                'search_value' => "FISH PARTY"
            ]));

            $doc = $solrClient->get($record->id)->toArray();
            $this->assertNotNull($doc);
            // no related party multy should exist
            $this->assertFalse(array_key_exists('related_party_multi_title', $doc));
        }
    }

    function test_it_should_detect_if_record_can_be_updated()
    {
        // given a record
        $record = $this->stub(RegistryObject::class);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        $record->status = "DRAFT";
        CoreMetadataProvider::process($record);
        $updater = new UpdatePortalIndex();
        $hasIndex = $updater->hasPortalIndex($record->id);
        $this->assertFalse($hasIndex);
        $record->status = "PUBLISHED";
        CoreMetadataProvider::process($record);
        $hasIndex = $updater->hasPortalIndex($record->id);
        $this->assertTrue($hasIndex);
        // non-existent record
        $hasIndex = $updater->hasPortalIndex(999999999);
        $this->assertFalse($hasIndex);
    }

}