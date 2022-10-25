<?php

namespace ANDS\Registry\Events\Event;

use ANDS\Registry\Events\Event;

class DataSourceUpdatedEvent implements Event
{
    public $dataSourceId;
    public $logMessage;

    public static function from($data)
    {
        $event = new static;
        $event->dataSourceId = array_get($data, 'data_source_id');
        $event->logMessage = array_get($data, 'log_message');
        return $event;
    }
}