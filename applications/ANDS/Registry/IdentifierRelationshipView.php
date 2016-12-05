<?php


namespace ANDS\Registry;

use Illuminate\Database\Eloquent\Model;

class IdentifierRelationshipView extends Model
{
    protected $table = "identifier_relationships";
    protected $primaryKey = null;
    public $timestamps = false;
}