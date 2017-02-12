<?php


namespace ANDS\RegistryObject;


use Illuminate\Database\Eloquent\Model;

class Metadata extends Model
{
    protected $table = "registry_object_metadata";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $fillable = ['registry_object_id', 'attribute', 'value'];
}