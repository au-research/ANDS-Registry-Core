<?php


namespace ANDS\Authenticator;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;

class FacebookAuthenticator
{
    protected static $scopes = ['email'];

    /**
     * @return string
     * @throws \Exception
     */
    public static function getOauthLink()
    {
        $connection = self::getConnection();
        $helper = $connection->getRedirectLoginHelper();

        $permissions = static::$scopes;
        $loginUrl = $helper->getLoginUrl(baseUrl('registry/auth/facebook'), $permissions);

        return $loginUrl;
    }

    /**
     * @throws FacebookSDKException
     */
    public static function getProfile()
    {
        $connection = self::getConnection();
        $helper = $connection->getRedirectLoginHelper();

        // https://stackoverflow.com/questions/32029116/facebook-sdk-returned-an-error-cross-site-request-forgery-validation-failed-th
        $_SESSION['FBRLH_state'] = $_GET['state'];

        $accessToken = $helper->getAccessToken();

        $response = $connection->get('/me?fields=id,name,email,picture', $accessToken->getValue());
        $user = $response->getGraphUser();
        $profile = [
            'identifier' => $user->getId(),
            'photoURL' => $user->getPicture() ? $user->getPicture()->getUrl() : '',
            'displayName' => $user->getName(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'accessToken' => $response->getApp()->getAccessToken()->getValue(),
            'authentication_service_id' => 'AUTHENTICATION_SOCIAL_FACEBOOK'
        ];

        return $profile;
    }

    /**
     * @throws \Facebook\Exceptions\FacebookSDKException
     * @throws \Exception
     * @returns Facebook
     */
    public static function getConnection()
    {
        $config = self::getConfig();
        $connection = new Facebook([
            'app_id' => $config['keys']['id'],
            'app_secret' => $config['keys']['secret'],
//            'default_graph_version' => 'v2.2',
        ]);
        return $connection;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function getConfig()
    {
        $config = \ANDS\Util\Config::get('oauth');
        return $config['providers']['Facebook'];
    }
}