<?php

namespace ANDS\Registry\Events\Event;
use ANDS\Registry\Events\Event;

class PrimaryKeyUpdatedEvent implements Event
{
    public $data_source_id;
    public $old_primary_key;
    public $new_primary_key;
    public $service_relationship_type;
    public $activity_relationship_type;
    public $collection_relationship_type;
    public $party_relationship_type;

    public static function from($data)
    {
        $event = new static;
        $event->data_source_id = array_get($data, 'data_source_id', null);
        // if no old_primary_key specified we just need to add the new primary_key's title to the given classes
        // and vica versa
        $event->old_primary_key = array_get($data, 'old_primary_key', null);
        // if no new_primary_key specified we just need to remove titles from the old primary_key's portal index
        // and vica versa
        $event->new_primary_key = array_get($data, 'new_primary_key', null);
        // the type of relationships the given primary key is related by to the registry objects in the given datasource
        $event->service_relationship_type = array_get($data, 'service_relationship_type', null);
        $event->activity_relationship_type = array_get($data, 'activity_relationship_type', null);
        $event->collection_relationship_type = array_get($data, "collection_relationship_type", null);
        $event->party_relationship_type = array_get($data, "party_relationship_type", null);

        return $event;
    }

}