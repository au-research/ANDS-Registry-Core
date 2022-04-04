<?php

namespace ANDS\Registry\Events\Event;
use ANDS\Registry\Events\Event;

class PrimaryKeyUpdatedEvent implements Event
{
    public $data_source_id;
    public $old_primary_key;
    public $new_primary_key;
    public $old_service_relationship_type;
    public $old_activity_relationship_type;
    public $old_collection_relationship_type;
    public $old_party_relationship_type;
    public $new_service_relationship_type;
    public $new_activity_relationship_type;
    public $new_collection_relationship_type;
    public $new_party_relationship_type;

    public static function from($data)
    {
        $event = new static;
        $event->data_source_id = array_get($data, 'data_source_id', null);
        // if no old_primary_key specified we just need to add the new primary_key's title to the given classes
        // and vica versa
        $event->old_primary_key = array_get($data, 'old_primary_key', "");
        // if no new_primary_key specified we just need to remove titles from the old primary_key's portal index
        // and vica versa
        $event->new_primary_key = array_get($data, 'new_primary_key', "");
        // the type of relationships the given primary key is related by to the registry objects in the given datasource
        // old and new
        $event->old_service_relationship_type = array_get($data, 'old_service_relationship_type', "");
        $event->old_activity_relationship_type = array_get($data, 'old_activity_relationship_type', "");
        $event->old_collection_relationship_type = array_get($data, "old_collection_relationship_type", "");
        $event->old_party_relationship_type = array_get($data, "old_party_relationship_type", "");
        $event->new_service_relationship_type = array_get($data, 'new_service_relationship_type', "");
        $event->new_activity_relationship_type = array_get($data, 'new_activity_relationship_type', "");
        $event->new_collection_relationship_type = array_get($data, "new_collection_relationship_type", "");
        $event->new_party_relationship_type = array_get($data, "new_party_relationship_type", "");
        return $event;
    }

}