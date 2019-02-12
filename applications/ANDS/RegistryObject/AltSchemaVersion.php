<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 15/1/19
 * Time: 2:34 PM
 */

namespace ANDS\RegistryObject;
use ANDS\DataSource;
use ANDS\Registry\Versions;
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

    public function version()
    {
        return $this->hasOne(Versions::class, 'id', 'id');
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'origin' => $this->origin,
            'prefix' => $this->prefix,
            'uri' => $this->uri,
            'hash' => $this->version->hash
//            'data' => base64_encode($this->data)
        ];
    }

}


