<?php


namespace ANDS\RegistryObject;


use Illuminate\Database\Eloquent\Model;

/**
 * Class RegistryObjectVersion
 * @package ANDS\RegistryObject
 */
class RegistryObjectVersion extends Model
{
    protected $table = "registry_object_versions";
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['version_id', 'registry_object_id'];
}