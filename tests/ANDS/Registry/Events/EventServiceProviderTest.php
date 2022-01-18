<?php

namespace ANDS\Registry\Events;

use ANDS\DataSource;
use ANDS\Registry\Events\Event\DataSourceUpdatedEvent;
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
    }
}
