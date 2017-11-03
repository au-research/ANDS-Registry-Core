<?php

namespace ANDS\ORCID;

use ANDS\RegistryObject;
use Illuminate\Database\Eloquent\Model;


class ORCIDExport extends Model
{
    protected $table = "orcid_exports";
    protected $primaryKey = "id";
    protected $fillable = ['registry_object_id', 'orcid_id', 'put_code', 'data', 'created_at', 'updated_at'];
    public $timestamps = false;

    public static function getTableName()
    {
        return static::table;
    }

    public function registryObject()
    {
        return $this->hasOne(RegistryObject::class, 'registry_object_id', 'registry_object_id');
    }

    public function saveData($registry_object_id, $orcid_id, $put_code, $data) {
        $this->registry_object_id = $registry_object_id;
        $this->orcid_id = $orcid_id;
        $this->put_code = $put_code;
        $this->data = $data;
        $this->created_at = now();
        $this->updated_at = now();
        return $this;
    }

    public function updateData($data) {
        $this->data = $data;
        $this->updated_at = now();
        return $this;
    }

}