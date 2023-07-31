<?php

namespace ANDS\Registry\Events\Event;

use ANDS\Registry\Events\Event;

class RegenerateMetadataEvent implements Event
{

    public $registryObjectId;
    public $dci = false;
    public $scholix = false;

    public static function from($data)
    {
        $event = new static;
        $event->registryObjectId = array_get($data, 'registryObjectId');
        $event->dci = boolval(array_get($data, 'dci'));
        $event->scholix = boolval(array_get($data, 'scholix'));
        return $event;
    }
}