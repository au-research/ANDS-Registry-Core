<?php


namespace ANDS\RegistryObject;


use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    protected $table = "registry_object_relationships";
    protected $primaryKey = null;
    public $timestamps = false;
}