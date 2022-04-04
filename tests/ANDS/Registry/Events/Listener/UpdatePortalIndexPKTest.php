<?php

namespace ANDS\Registry\Events\Listener;

use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Events\Event\PrimaryKeyUpdatedEvent;
use ANDS\Registry\Events\EventServiceProvider;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\RegistryObject;
use ANDS\Util\Config;
use MinhD\SolrClient\SolrClient;

class UpdatePortalIndexPKTest extends \MyceliumTestClass
{

    function testPortalIndexRelatedpartyMultiUpdated(){
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
            $party = $this->stub(RegistryObject::class, ['class' => 'party','title'=> "Related,Party,One", 'type' => 'group','key' => 'AUT_DCI_PARTY']);

            $this->stub(RecordData::class, [
                'registry_object_id' => $party->id,
                'data' => Storage::disk('test')->get('rifcs/party_DCI.xml')
            ]);
            CoreMetadataProvider::process($party);
            $this->myceliumInsert($party);


            RIFCSIndexProvider::indexRecord($record);

            RIFCSIndexProvider::indexRecord($party);

            $solrClient->commit();
            $doc = $solrClient->get($record->id)->toArray();
            $this->assertNotNull($doc);
            // the party should have been added by the portal indexing
            $this->assertFalse(array_key_exists('related_party_multi_title', $doc));
            // change its title (what mycelium would trigger)

            EventServiceProvider::dispatch(PrimaryKeyUpdatedEvent::from([
                "data_source_id" => $record->data_source_id,
                "new_primary_key" => $party->key,
                "new_collection_relationship_type"=>"hasAssociationWith"
            ]));
            $doc = $solrClient->get($record->id)->toArray();
            $this->assertNotNull($doc);
            $this->assertTrue(array_key_exists('related_party_multi_title', $doc));

        }
    }

    function testLogging(){
        debug("PortalIndexUpdateEvent");
    }

}