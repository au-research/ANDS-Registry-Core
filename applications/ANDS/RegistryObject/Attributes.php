<?php


namespace ANDS\RegistryObject;

use Illuminate\Database\Eloquent\Model;

class Attributes extends Model
{
    protected $table = "registry_object_attributes";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $fillable = ['registry_object_id', 'attribute', 'value'];
}