<?php


namespace ANDS\RegistryObject;


use Illuminate\Database\Eloquent\Model;

class RelatedInfoRelationship extends Model
{
    protected $table = "registry_object_identifier_relationships";
    protected $primaryKey = 'id';
    public $timestamps = false;
}