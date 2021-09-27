<?php


namespace ANDS\RegistryObject;


use Illuminate\Database\Eloquent\Model;

/**
 * Class ImplicitRelationship
 * @package ANDS\RegistryObject
 */
class ImplicitRelationship extends Model
{
    protected $table = "registry_object_implicit_relationships";
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'from_id',
        'to_id',
        'relation_type',
        'relation_origin'
    ];
}