<?php
namespace ANDS\Registry\Providers\ORCID;

use ANDS\Util\ORCIDAPI;

/**
 * Class ORCIDRecordsRepository
 * @package ANDS\Registry\Providers\ORCID
 */
class ORCIDRecordsRepository
{
    /**
     * Return an existing orcid by orcid_id
     * or create a new one firstOrCreate style
     * also, update the oauth tokens if needed
     *
     * @param $orcidID
     * @param array $data
     * @return mixed
     */
    public static function firstOrCreate($orcidID, $data = [])
    {
        $orcid = ORCIDRecord::find($orcidID);

        // create one with the provided data if none exist
        // data[name] is passed along the authentication, can be refreshed using
        // our business logic with populateFullName() if needed
        if (!$orcid) {
            $orcid = ORCIDRecord::create([
                'orcid_id' => $data['orcid'],
                'full_name' => $data['name'],
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token']
            ]);
        }

        // update the ORCID access token and refresh token
        $orcid->access_token = $data['access_token'];
        $orcid->refresh_token = $data['refresh_token'];

        $orcid->save();
        $orcid->populateRecordData();

        return $orcid;
    }

    /**
     * obtain an ORCIDRecord 
     * creates if not exists
     * null if unobtainable
     * 
     * @return ORCIDRecord | null
     */
    public static function obtain($orcidID)
    {
        // check if it exists
        if ($orcid = ORCIDRecord::find($orcidID)) {
            return $orcid;
        }

        // obtain them
        if ($data = ORCIDAPI::getRecord($orcidID)) {
            $orcid = ORCIDRecord::create([
                'orcid_id' => $orcidID
            ]);
            $orcid->populateRecordData();
            $orcid->populateFullName();
            return $orcid;
        };

        return null;
    }

}