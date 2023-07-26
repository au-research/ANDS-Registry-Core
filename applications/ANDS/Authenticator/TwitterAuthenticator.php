<?php


namespace ANDS\Authenticator;

// Updated for Twitter API v2.
// Needs a modified TwitterOAuth to keep working with PHP 5;
// see applications/abraham/README-ARDC.md.

use Abrahamc\TwitterOAuth\TwitterOAuth;

class TwitterAuthenticator
{
    /**
     * @return string
     * @throws \Abrahamc\TwitterOAuth\TwitterOAuthException
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

        /*
        On success, $access now has a value such as:
          array ( 'oauth_token' => '1655...',
            'oauth_token_secret' => '4xAL...',
            'user_id' => '1655...',
            'screen_name' => 'myloginname...')
        */
        $connection->setOauthToken($access['oauth_token'], $access['oauth_token_secret']);

        /*
          To get profile_image_url, you must ask for it.  See
          description of user.fields at
          https://developer.twitter.com/en/docs/twitter-api/users/lookup/api-reference/get-users-me
          for the list of supported fields, and
          https://developer.twitter.com/en/docs/twitter-api/data-dictionary/object-model/user
          for more details about those fields.
        */
        $profile = $connection->get('users/me', ['user.fields' => ['profile_image_url']]);

        /**
         * "Historic" note, since this is only supported for API v1; there's
         * no access via API v2:
         * The app will need to explicitly ask for the user email
         * in order to receive user email information
         * GET account/verify_credentials
         * @url https://developer.twitter.com/en/docs/accounts-and-users/manage-account-settings/api-reference/get-account-verify_credentials.html
         */

        $profile = [
            'identifier' => $access['user_id'],
            'photoURL' => $profile->data->profile_image_url,
            // Construct a display name of the form "My Real Name (@myHandle)".
            'displayName' => $profile->data->name . ' (@' . $access['screen_name'] . ')',
            'firstName' => $profile->data->name,
            'handle' => $access['screen_name'],
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
        // We now use API v2, which must be explicitly requested.
        //$connection->setApiVersion('2');
        return $connection;
    }

    public static function getConfig()
    {
        $config = \ANDS\Util\Config::get('oauth');
        return $config['providers']['Twitter'];
    }
}