<?php

namespace ANDS\Registry\Providers\ORCID;

use ANDS\RegistryObject;
use ANDS\Util\ORCIDAPI;
use Illuminate\Database\Eloquent\Model;


class ORCIDExport extends Model
{
    protected $table = "orcid_exports";
    protected $primaryKey = "id";
    protected $fillable = ['registry_object_id', 'orcid_id', 'put_code', 'response', 'data', 'created_at', 'updated_at'];
    protected $appends = ['in_orcid'];

    public static function getTableName()
    {
        return static::table;
    }

    public function registryObject()
    {
        return $this->hasOne(RegistryObject::class, 'registry_object_id', 'registry_object_id');
    }

    public function record()
    {
        return $this->hasOne(ORCIDRecord::class, 'orcid_id', 'orcid_id');
    }

    public function getInOrcidAttribute()
    {
        return $this->put_code ? true : false;
    }

    public function saveData($registry_object_id, $orcid_id, $put_code, $data, $response) {
        $this->registry_object_id = $registry_object_id;
        $this->orcid_id = $orcid_id;
        $this->put_code = $put_code;
        $this->data = $data;
        $this->response = $response;
        $this->created_at = time();
        $this->updated_at = time();
        $this->save();
        return $this;
    }

    public function getPutCode(){
        return $this->put_code;
    }

    public function updateData($put_code, $data, $response) {
        $this->put_code = $put_code;
        $this->data = $data;
        $this->response = $response;
        $this->updated_at = time();
        $this->save();
        return $this;
    }

}