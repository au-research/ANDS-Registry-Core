<?php
namespace ANDS\Registry\Providers\ORCID;

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
        if (!$orcid) {
            $orcid = ORCIDRecord::create([
                'orcid_id' => $data['orcid'],
                'full_name' => $data['name'],
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token']
            ]);
        }

        $orcid->populateRecordData();
        return $orcid;
    }

}