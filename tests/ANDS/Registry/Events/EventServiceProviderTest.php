<?php

namespace ANDS\Registry\Events;

use ANDS\DataSource;
use ANDS\Registry\Events\Event\DataSourceUpdatedEvent;
use ANDS\Registry\Events\Event\PortalIndexUpdateEvent;
use RegistryTestClass;

class EventServiceProviderTest extends RegistryTestClass
{
    public function testDispatchDataSourceUpdatedEvent()
    {
        EventServiceProvider::dispatch(DataSourceUpdatedEvent::from([
            'data_source_id' => $this->dataSource->id,
            'log_message' => 'stuff'
        ]));

        // it creates a new data source log
        $logMessage = DataSource\DataSourceLog::where('data_source_id', $this->dataSource->id)->get()->first();
        $this->assertEquals("stuff", $logMessage->log);
    }

    public function testGetShortNames()
    {
        $shortNames = EventServiceProvider::getShortNames();
        $this->assertContains("DataSourceUpdatedEvent", $shortNames);
        $this->assertContains("PortalIndexUpdateEvent", $shortNames);
    }

    public function testDispatchPortalIndexUpdateEvent()
    {
        EventServiceProvider::dispatch(PortalIndexUpdateEvent::from([
            'registry_object_id' => "1",
            'indexed_field' => "related_party_title",
            'search_value' => "Old Fish",
            'new_value' => "New Fish"
        ]));
        // TODO: add a solr doc in setup and test for value change
    }

}
