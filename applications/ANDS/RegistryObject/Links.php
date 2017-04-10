<?php

namespace ANDS\RegistryObject;

use Illuminate\Database\Eloquent\Model;

class Links extends Model
{
    protected $table = "registry_object_links";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $fillable = ['registry_object_id', 'data_source_id', 'link_type', 'link', 'status', 'last_checked'];
}