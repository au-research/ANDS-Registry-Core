<?php


namespace ANDS;


use Illuminate\Database\Eloquent\Model;

class RegistryObject extends Model
{
    protected $table = "registry_objects";
    protected $primaryKey = "registry_object_id";
    public $timestamps = false;

    public function data()
    {
        return $this->hasMany(RecordData::class, 'registry_object_id', 'registry_object_id');
    }

}