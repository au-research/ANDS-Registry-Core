<?php


namespace ANDS\Authenticator;


use ANDS\Util\URLUtil;

class AAFRapidConnectAuthenticator
{
    /**
     * Returns the url for use to login
     *
     * @return mixed
     */
    public static function getAuthURL()
    {
        $config = self::getConfig();
        return $config['keys']['url'];
    }

    /**
     * Returns a profile in accordance with all authenticators
     *
     * @param $jwt
     * @return array
     */
    public static function getProfile($jwt)
    {
        $config = self::getConfig();
        $secret = $config['keys']['secret'];

        $decoded = self::decodeJWT($jwt, $secret);

        $email = $decoded->{'https://aaf.edu.au/attributes'}->mail;
        $displayName = $decoded->{'https://aaf.edu.au/attributes'}->displayname;
        $persistent_id = $decoded->{'https://aaf.edu.au/attributes'}->edupersontargetedid;

        $username = sha1($persistent_id);
        $profile = [
            'identifier' => $username,
            'photoURL' => '',
            'displayName' => $displayName,
            'firstName' => '',
            'lastName' => '',
            'email' => $email,
            'accessToken' => '',
            'authentication_service_id' => 'AUTHENTICATION_SHIBBOLETH'
        ];

        return $profile;
    }


    /**
     * Decode the JWT payload
     *
     * @param $jwt
     * @param null $key
     * @param bool $verify
     * @return mixed
     */
    public static function decodeJWT($jwt, $key = null, $verify = true)
    {
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            throw new \UnexpectedValueException('Wrong number of segments');
        }

        list($headb64, $payloadb64, $cryptob64) = $tks;

        $header = json_decode(URLUtil::urlsafeB64Decode($headb64));
        if ($header === null) {
            throw new \UnexpectedValueException('Invalid segment encoding');
        }

        $payload = json_decode(URLUtil::urlsafeB64Decode($payloadb64));
        if ($payload === null) {
            throw new \UnexpectedValueException('Invalid segment encoding');
        }

        if ($verify) {
            $sig = URLUtil::urlsafeB64Decode($cryptob64);
            if (empty($header->alg)) {
                throw new \DomainException('Empty algorithm');
            }
            if ($sig != static::sign("$headb64.$payloadb64", $key, $header->alg)) {
                throw new \UnexpectedValueException('Signature verification failed');
            }
        }

        return $payload;
    }

    /**
     * Sign the message
     *
     * @param $msg
     * @param $key
     * @param string $method
     * @return string
     */
    public static function sign($msg, $key, $method = 'HS256')
    {
        $methods = [
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        ];
        if (empty($methods[$method])) {
            throw new \DomainException('Algorithm not supported');
        }
        return hash_hmac($methods[$method], $msg, $key, true);
    }

    /**
     * Authenticator configuration
     *
     * @return mixed
     */
    public static function getConfig()
    {
        $config = \ANDS\Util\Config::get('oauth');
        return $config['providers']['AAF_RapidConnect'];
    }
}