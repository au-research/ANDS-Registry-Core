<?php


namespace ANDS\Authenticator;


use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterAuthenticator
{
    /**
     * @return string
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     * @throws \Exception
     */
    public static function getOauthLink()
    {
        $connection = self::getConnection();
        $requestToken = $connection->oauth("oauth/request_token", [
            'oauth_callback' => baseUrl('registry/auth/twitter')
        ]);
        $oauthToken = $requestToken['oauth_token'];
        return $connection->url('oauth/authorize', ['oauth_token' => $oauthToken]);
    }

    /**
     * @param $oauthToken
     * @param $oauthVerifier
     * @return array
     * @throws \Exception
     */
    public static function getProfile($oauthToken, $oauthVerifier)
    {
        $connection = self::getConnection();
        $access = $connection->oauth("oauth/access_token", ["oauth_verifier" => $oauthVerifier, 'oauth_token' => $oauthToken]);

        $profile = $connection->get('users/show', [
            'user_id' => $access['user_id']
        ]);

        /**
         * The app will need to explicitly ask for the user email
         * in order to receive user email information
         * GET account/verify_credentials
         * @url https://developer.twitter.com/en/docs/accounts-and-users/manage-account-settings/api-reference/get-account-verify_credentials.html
         */

        $profile = [
            'identifier' => $access['user_id'],
            'photoURL' => $profile->profile_image_url_https,
            'displayName' => $access['screen_name'],
            'firstName' => 'Minh Duc Nguyen',
            'lastName' => '',
            'email' => '',
            'accessToken' => $access['oauth_token'],
            'authentication_service_id' => 'AUTHENTICATION_SOCIAL_TWITTER'
        ];

        return $profile;
    }

    /**
     * @return TwitterOAuth
     * @throws \Exception
     */
    public static function getConnection()
    {
        $config = self::getConfig();
        $key = $config['keys']['key'];
        $secret = $config['keys']['secret'];
        $connection = new TwitterOAuth($key, $secret);
        return $connection;
    }

    public static function getConfig()
    {
        $config = \ANDS\Util\Config::get('oauth');
        return $config['providers']['Twitter'];
    }
}