<?php


namespace ANDS\RegistryObject;


use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    protected $table = "registry_object_relationships";
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'registry_object_id',
        'related_object_key',
        'origin',
        'relation_type',
        'relation_description',
        'relation_url'
    ];

}