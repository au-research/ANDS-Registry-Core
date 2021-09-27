<?php


namespace ANDS\Registry;

use Illuminate\Database\Eloquent\Model;

class RelationshipView extends Model
{
    protected $table = "relationships";
    protected $primaryKey = null;
    public $timestamps = false;
}