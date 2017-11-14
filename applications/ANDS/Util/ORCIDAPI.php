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
    public static function getBio(ORCIDRecord $orcid = null)
    {
        // TODO: if orcid is null, get one from current session
        $data = self::getPublicClient()->get($orcid->orcid_id .'/record');
        // TODO: error handling
        $content = json_decode($data->getBody()->getContents(), true);
        return $content;
    }

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

    public static function getPublicClient()
    {
        $client = new Client([
            'base_uri' => Config::get('orcid.public_api_url'),
            'time_out' => 10,
            'headers' => ['Accept' => 'application/json'],
        ]);
        return $client;
    }

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
}