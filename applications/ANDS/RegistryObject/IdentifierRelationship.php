<?php


namespace ANDS\RegistryObject;


use Illuminate\Database\Eloquent\Model;

class IdentifierRelationship extends Model
{
    protected $table = "identifier_relationships";
    protected $primaryKey = false;
    public $timestamps = false;
}