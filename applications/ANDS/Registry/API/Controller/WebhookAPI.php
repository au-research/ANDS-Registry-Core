<?php

namespace ANDS\Registry\API\Controller;

use ANDS\Registry\API\Request;
use ANDS\Registry\Events\EventServiceProvider;
use Exception;

class WebhookAPI extends HTTPController
{
    /**
     * @throws Exception
     */
    public function accept() {
        $payload = Request::all();
        $this->validate(['type', 'data']);

        // resolve type to an Event
        $eventType = $payload['type'];
        $shortNameMapping = EventServiceProvider::getShortNameMapping();
        if (!array_key_exists($eventType, $shortNameMapping)) {
            throw new Exception("Unrecognizable event $eventType");
        }
        $event = new $shortNameMapping[$eventType];

        // dispatch the Event with the data
        EventServiceProvider::dispatch($event->from($payload['data']));
    }
}