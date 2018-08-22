<?php


namespace ANDS\Authenticator;


use Google_Client;
use Google_Service_Drive;
use Google_Service_Oauth2;
use Google_Service_People;
use Google_Service_Plus;

class GoogleAuthenticator
{
    /**
     * @throws \Exception
     */
    public static function getOauthLink()
    {
        $connection = self::getConnection();

        return $connection->createAuthUrl();
    }

    /**
     * @param $code
     * @return array
     * @throws \Exception
     */
    public static function getProfile($code)
    {
        $connection = self::getConnection();
        $token = $connection->fetchAccessTokenWithAuthCode($code);

        $oauth = new Google_Service_Oauth2($connection);
        $userInfo = $oauth->userinfo->get();

        $profile = [
            'identifier' => $userInfo->getId(),
            'photoURL' => $userInfo->getPicture(),
            'displayName' => $userInfo->getName(),
            'firstName' => $userInfo->getGivenName(),
            'lastName' => $userInfo->getFamilyName(),
            'email' => $userInfo->getEmail(),
            'accessToken' => $token['access_token'],
            'authentication_service_id' => 'AUTHENTICATION_SOCIAL_GOOGLE'
        ];

        return $profile;
    }

    /**
     * @return Google_Client
     * @throws \Exception
     */
    public static function getConnection()
    {
        $config = self::getConfig();
        $connection = new Google_Client();
        $connection->setAuthConfig([
            'client_id' => $config['keys']['id'],
            'client_secret' => $config['keys']['secret']
        ]);
        $connection->setAccessType("offline");
        $connection->setIncludeGrantedScopes(true);
        $connection->addScope(Google_Service_Plus::PLUS_ME);
        $connection->setScopes([
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile'
        ]);

        $connection->setRedirectUri(baseUrl('registry/auth/google'));

        return $connection;
    }

    public static function getConfig()
    {
        $config = \ANDS\Util\Config::get('oauth');
        return $config['providers']['Google'];
    }


}