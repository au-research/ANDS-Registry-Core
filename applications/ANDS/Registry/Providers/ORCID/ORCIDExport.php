<?php

namespace ANDS\Registry\Providers\ORCID;

use ANDS\RegistryObject;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ORCIDExport
 * @package ANDS\Registry\Providers\ORCID
 */
class ORCIDExport extends Model
{
    protected $table = "orcid_exports";
    protected $primaryKey = "id";
    protected $fillable = ['registry_object_id', 'orcid_id', 'put_code', 'response', 'data', 'created_at', 'updated_at'];
    protected $appends = ['in_orcid'];

    /**
     * $this->registryObject
     * Returns the RegistryObject this belongs to
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function registryObject()
    {
        return $this->hasOne(RegistryObject::class, 'registry_object_id', 'registry_object_id');
    }

    /**
     * $this->record
     * Returns the ORCIDRecord this belongs to
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function record()
    {
        return $this->hasOne(ORCIDRecord::class, 'orcid_id', 'orcid_id');
    }

    /**
     * $this->inORcid
     * $this->in_orcid
     * @return bool
     */
    public function getInOrcidAttribute()
    {
        return $this->put_code ? true : false;
    }

}