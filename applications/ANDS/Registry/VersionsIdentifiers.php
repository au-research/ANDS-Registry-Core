<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 30/1/19
 * Time: 3:08 PM
 */

namespace ANDS\Registry;
use Illuminate\Database\Eloquent\Model;

class VersionsIdentifiers extends Model
{
    protected $table = "versions_identifiers";
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['version_id', 'identifier', 'identifier_type'];
}