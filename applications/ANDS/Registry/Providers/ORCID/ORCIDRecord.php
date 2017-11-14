<?php

namespace ANDS\Registry\Providers\ORCID;

use ANDS\Util\ORCIDAPI;
use Illuminate\Database\Eloquent\Model;
use ANDS\Registry\Providers\ORCID\ORCIDExport as ORCIDExport;

/**
 * Class ORCIDRecord
 * @package ANDS\Registry\Providers\ORCID
 */
class ORCIDRecord extends Model
{
    protected $table = "orcid_records";
    public $incrementing = false;
    protected $primaryKey = "orcid_id";
    protected $fillable = ['orcid_id', 'full_name', 'record_data', 'created_at', 'updated_at', 'access_token', 'refresh_token'];

    /**
     * Obtain the bio from the API
     * TODO: check cachable resource
     */
    public function populateRecordData()
    {
        $bio = ORCIDAPI::getBio($this);
        $this->record_data = json_encode($bio, true);
        $this->save();
    }

    /**
     * Override $this->record_data
     *
     * @return string
     */
    public function getBioAttribute()
    {
        if (is_null($this->record_data)) {
            $this->populateRecordData();
        }
        return json_decode($this->record_data, true);
    }

    /**
     * Eloquent Relationship for ORCIDExport
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function exports() {
        return $this->hasMany(ORCIDExport::class, 'orcid_id');
    }

}