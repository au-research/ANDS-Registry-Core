<?php


namespace ANDS\Registry;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ImplicitRelationshipView
 * @package ANDS\Registry
 */
class ImplicitRelationshipView extends Model
{
    protected $table = "implicit_relationships";
    protected $primaryKey = null;
    public $timestamps = false;
}