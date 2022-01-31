<?php

namespace ANDS\Registry\Events\Event;
use ANDS\Registry\Events\Event;


class PortalIndexUpdateEvent implements Event
{
    public $registry_object_id;
    public $indexed_field;
    public $search_value;
    public $new_value;

    public static function from($data)
    {
        $event = new static;
        $event->registry_object_id = array_get($data, 'registry_object_id');
        $event->indexed_field = array_get($data, 'indexed_field');
        // if no search_value we just set the field to the new_value
        $event->search_value = array_get($data, 'search_value', null);
        $event->new_value= array_get($data, 'new_value');
        return $event;
    }
}