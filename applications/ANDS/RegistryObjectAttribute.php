<?php


namespace ANDS;


use Illuminate\Database\Eloquent\Model;

class RegistryObjectAttribute extends Model
{
    protected $table = "registry_object_attributes";
    public $timestamps = false;
    protected $fillable = ['registry_object_id', 'attribute', 'value'];
}