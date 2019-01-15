<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 15/1/19
 * Time: 2:34 PM
 */

namespace ANDS\RegistryObject;
use ANDS\DataSource;
use ANDS\RegistryObject;
use Illuminate\Database\Eloquent\Model;

class AltSchemaVersion extends Model
{
    protected $table = "alt_schema_versions";

    public function registryObject()
    {
        return $this->belongsTo(RegistryObject::class);
    }

    public function dataSource()
    {
        return $this->belongsTo(DataSource::class, 'registry_object_data_source_id', 'data_source_id');
    }

}


