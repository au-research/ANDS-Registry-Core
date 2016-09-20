<?php


namespace ANDS\RegistryObject;


use Illuminate\Database\Eloquent\Model;

class Metadata extends Model
{
    protected $table = "registry_object_metadata";
    protected $primaryKey = "id";
    public $timestamps = false;
}