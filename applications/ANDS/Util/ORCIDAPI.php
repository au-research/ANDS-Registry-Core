<?php
namespace ANDS\Util;


use ANDS\Authenticator\ORCIDAuthenticator;
use ANDS\Registry\Providers\ORCID\ORCIDExport;
use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class ORCIDAPI
{
    /**
     * Get the bio of a given ORCIDRecord
     * TODO: check usage along with self::getRecord($orcidID)
     * @param ORCIDRecord|null $orcid
     * @return mixed
     */
    public static function getBio(ORCIDRecord $orcid = null)
    {
        // TODO: if orcid is null, get one from current session
        $data = self::getPublicClient()->get($orcid->orcid_id .'/record');
        // TODO: error handling
        $content = json_decode($data->getBody()->getContents(), true);
        return $content;
    }

    /**
     * Get the record data by ORCIDID
     * @param $orcidID
     * @return array
     */
    public static function getRecord($orcidID)
    {
        try {
            $data = self::getPublicClient()->get($orcidID);
            return json_decode($data->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return null;
        }
    }

    /**
     * Sync an ORCIDExport to ORCID
     * Creates if it doesn't exist, populate own PUTCode
     * Updates it with the PUTCode
     *
     * TODO: check last update and/or check hash value
     * @param ORCIDExport $export
     */
    public static function sync(ORCIDExport $export)
    {
        $orcid = $export->record;
        $xml = $export->data;

        $client = self::getMemberClient($orcid->orcid_id);

        if ($export->in_orcid) {
            //update
            // PUT to /work/:putCode
            try {
                $client->put('work/' . $export->put_code, [
                    'headers' => [ 'Content-type' => 'application/vnd.orcid+xml' ],
                    'body' => $xml
                ]);
                $export->response = null;
                $export->save();
            } catch (RequestException $e) {
                $export->response = $e->getResponse()->getBody()->getContents();
                $export->save();
            }
            return;
        }

        // create new
        // POST to /work
        try {
            $data = $client->post('work/', [
                'headers' => [ 'Content-type' => 'application/vnd.orcid+xml' ],
                'body' => $xml
            ]);
            $location = array_pop($data->getHeader("Location"));
            $putCode = array_pop(explode('/', $location));
            $export->response = null;
            $export->put_code = $putCode;
            $export->save();
        } catch (RequestException $e) {
            $export->response = $e->getResponse()->getBody()->getContents();
            $export->save();
        }
    }

    /**
     * Fully sync an orcid record
     * Delete any export that comes from us that is deleted upstream
     *
     * @param ORCIDRecord $orcid
     */
    public static function syncRecord(ORCIDRecord $orcid)
    {
        $conf = Config::get('orcid');
        $orcid->populateRecordData();
        $data = json_decode($orcid->record_data, true);
        $allWorks = $data['activities-summary']['works']['group'];

        try {
            $codes = collect($allWorks)->filter(function($item) use ($conf){
                return $item['work-summary'][0]['source']['source-client-id']['path'] === $conf['client_id'];
            })->map(function($item) {
                return $item['work-summary'][0]['put-code'];
            })->toArray();

            $shouldBeDeleted = ORCIDExport::where('orcid_id', $orcid->orcid_id)
                ->where('put_code', '!=', "")
                ->whereNotIn('put_code', $codes);

            if ($shouldBeDeleted->count() > 0) {
                $shouldBeDeleted->delete();
            }
        } catch (\Exception $e) {
            // todo: log the error somehow
            // todo: handle exception here
            return;
        }
    }

    /**
     * Get a public client
     * use for getting public metadata
     *
     * @return Client
     */
    public static function getPublicClient()
    {
        $client = new Client([
            'base_uri' => Config::get('orcid.public_api_url'),
            'time_out' => 10,
            'headers' => ['Accept' => 'application/json'],
        ]);
        return $client;
    }

    /**
     * Get a member client with authentication Bearer
     *
     * @param $orcidID
     * @return Client
     */
    public static function getMemberClient($orcidID)
    {
        $accessToken = ORCIDAuthenticator::getOrcidAccessToken();
        $client = new Client([
            'base_uri' => Config::get('orcid.api_url') . $orcidID . '/',
            'time_out' => 10,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$accessToken}"
            ]
        ]);
        return $client;
    }

    public static function delete($orcid, $export)
    {
        $client = self::getMemberClient($orcid->orcid_id);
        try {
            $client->delete("work/{$export->put_code}");
        } catch (RequestException $e) {
            throw new \Exception($e->getResponse()->getBody()->getContents());
        }
    }
}