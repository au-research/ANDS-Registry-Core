<?php


namespace ANDS\RegistryObject;


use Illuminate\Database\Eloquent\Model;

class RelatedInfoRelationship extends Model
{
    protected $table = "relationships";
    protected $primaryKey = 'id';
    public $timestamps = false;
}