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
     * Populate the full_name field
     */
    public function populateFullName()
    {
        $this->full_name = $this->getFullName();
        $this->save();
    }

    /**
     * Business Logic to get the full name of a given ORCIDRecord
     *
     * @return null|string
     */
    public function getFullName()
    {
        $bio = $this->bio;

        // get credit-name if possible
        if ($name = $bio['person']['name']['credit-name']['value']) {
            return $name;
        }

        // get given-name then family-name
        if ($givenName = $bio['person']['name']['given-names']['value']) {
            $familyName = $bio['person']['name']['family-name']['value'];
            return $givenName. " ".$familyName;
        }

        // TODO: aka name
        return null;
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
     * $this->url
     * Returns the https version regardless
     *
     * @return mixed
     */
    public function getUrlAttribute()
    {
        if ($this->bio['orcid-identifier']['uri']) {
            return str_replace("http://", "https://", $this->bio['orcid-identifier']['uri']);
        }
    }

    /**
     * Eloquent Relationship for ORCIDExport
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function exports() {
        return $this->hasMany(ORCIDExport::class, 'orcid_id');
    }

}