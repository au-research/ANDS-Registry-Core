<?php

namespace ANDS\Registry\Providers\DCI;

use ANDS\RegistryObject;
use Illuminate\Database\Eloquent\Model;

class DCI extends Model
{
    protected $table = "dci";

    public function registryObject()
    {
        return $this->belongsTo(RegistryObject::class);
    }
}