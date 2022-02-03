<?php

namespace ANDS\RegistryObject;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = "registry_object_tags";
    protected $primaryKey = "id";
    public $timestamps = false;
    public $fillable = [
        'key', 'tag', 'type', 'user', 'user_from'
    ];
}