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
    protected $appends = ['in_orcid', 'error_message'];

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
     * Returns the possible error message
     *
     * @return null
     */
    public function getErrorMessageAttribute()
    {
        if (!$this->response) {
            return null;
        }

        $data = json_decode($this->response, true);
        if (array_key_exists('user-message', $data)) {
            return $data['user-message'];
        }

        if (array_key_exists('error_description', $data)) {
            // return $data['error_description'];
            // This normally occur because the access token is invalid
            return "You have been logged out of ORCID, please sign out and sign in again";
        }

        return null;
    }

    /**
     * Returns the possible error message
     *
     * @return null
     */
    public function getDeveloperMessageAttribute()
    {
        if ($this->response) {
            $data = json_decode($this->response, true);
            return $data['developer-message'];
        }
        return null;
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