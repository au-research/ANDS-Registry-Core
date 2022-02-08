<?php

namespace ANDS\Registry\Events\Event;
use ANDS\Registry\Events\Event;


class PortalIndexUpdateEvent implements Event
{
    public $registry_object_id;
    public $indexed_field;
    public $search_value;
    public $new_value;
    public $relationship_types;

    public static function from($data)
    {
        $event = new static;
        // if no target registry object is specified we need to find and replace ALL occurrences
        $event->registry_object_id = array_get($data, 'registry_object_id', null);
        $event->indexed_field = array_get($data, 'indexed_field');
        // if no search_value we just set the field to the new_value
        $event->search_value = array_get($data, 'search_value', null);
        // if no new_value it is a delete only
        $event->new_value= array_get($data, 'new_value', null);
        // the relationship_types is the given record is related to the "title" holder
        $event->relationship_types = array_get($data, "relationship_type", null);
        return $event;
    }
}