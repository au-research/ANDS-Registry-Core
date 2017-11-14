<?php
namespace ANDS\Util;


use ANDS\Authenticator\ORCIDAuthenticator;
use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

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

    public static function getPublicClient()
    {
        $client = new Client([
            'base_uri' => Config::get('orcid.public_api_url'),
            'time_out' => 30,
            'headers' => ['Accept' => 'application/json'],
        ]);
        return $client;
    }
}