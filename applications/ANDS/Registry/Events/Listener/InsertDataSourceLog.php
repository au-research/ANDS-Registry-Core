<?php

namespace ANDS\Registry\Events\Listener;

use ANDS\DataSource;
use ANDS\Registry\Events\Event;
use Exception;

class InsertDataSourceLog
{
    /**
     * @throws Exception
     */
    public function handle(Event\DataSourceUpdatedEvent $event)
    {
        if (!$event->logMessage) {
            return;
        }

        $dataSource = DataSource::find($event->dataSourceId);
        if (!$dataSource) {
            throw new Exception("Datasource id:$event->dataSourceId not found");
        }

        $dataSource->appendDataSourceLog($event->logMessage, 'info', 'IMPORTER');
    }
}