<?php

namespace ANDS\Registry;

use Illuminate\Database\Eloquent\Model;

class Versions extends Model
{
    protected $table = "versions";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $fillable = ['schema_id', 'data', 'hash', 'origin', 'created_at', 'updated_at'];
}