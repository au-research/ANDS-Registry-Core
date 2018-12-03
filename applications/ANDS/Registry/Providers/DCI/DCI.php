<?php

namespace ANDS\Registry\Providers\DCI;

use ANDS\DataSource;
use ANDS\RegistryObject;
use Illuminate\Database\Eloquent\Model;

class DCI extends Model
{
    protected $table = "dci";

    public function registryObject()
    {
        return $this->belongsTo(RegistryObject::class);
    }

    public function dataSource()
    {
        return $this->belongsTo(DataSource::class, 'registry_object_data_source_id', 'data_source_id');
    }
}