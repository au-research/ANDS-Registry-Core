<?php
namespace ANDS\Authenticator;


use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use ANDS\Registry\Providers\ORCID\ORCIDRecordsRepository;
use ANDS\Util\Config;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ORCIDAuthenticator
{
    protected static $SESSION_ORCID_ID = 'ORCID_ID';
    protected static $SESSION_ACCESS_TOKEN = 'ORCID_ACCESS_TOKEN';
    protected static $SESSION_REFRESH_TOKEN = 'ORCID_REFRESH_TOKEN';
    protected static $scope = "/authenticate /read-limited /activities/update";
    private static $config = null;

    public static function getConfig()
    {
        return Config::get('orcid');
    }

    /**
     * If the user is logged in
     *
     */
    public static function isLoggedIn()
    {
        if (session_status() === PHP_SESSION_NONE){
            session_start();
        }
        // check session for orcid_id
        if (array_key_exists(static::$SESSION_ORCID_ID, $_SESSION)) {
            return true;
        }

        return false;
    }

    public static function setORCIDSession(ORCIDRecord $orcid)
    {
        $_SESSION[self::$SESSION_ORCID_ID] = $orcid->orcid_id;
        $_SESSION[self::$SESSION_ACCESS_TOKEN] = $orcid->access_token;
        $_SESSION[self::$SESSION_REFRESH_TOKEN] = $orcid->refresh_token;
    }

    public static function getOrcidID()
    {
        if (!static::isLoggedIn()) {
            throw new \Exception("User is not Logged In");
        }

        return $_SESSION[static::$SESSION_ORCID_ID];
    }

    public static function getOauthLink($redirect = "")
    {
        $config = self::getConfig();
        return
            $config['service_url']
            . 'oauth/authorize?client_id='
            . $config['client_id']
            . '&response_type=code&scope='
            . self::$scope
            . '&redirect_uri='
            . $redirect;
    }

    public static function oauth($code)
    {
        $config = self::getConfig();

        $client = new Client([
            'base_uri' => $config['service_url'],
            'timeout'  => 30.0
        ]);

        $data = null;
        try {
            $data = $client->post('oauth/token', [
                'form_params' => [
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    //            'redirect_uri' => $this->redirect_uri
                ]
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $content = json_decode($e->getResponse()->getBody()->getContents(), true);
                throw new \Exception($content['error_description']);
            }
        }
        $content = json_decode($data->getBody()->getContents(), true);
        $orcid = ORCIDRecordsRepository::firstOrCreate($content['orcid'], $content);

        // set the session
        self::setORCIDSession($orcid);

        return $orcid;
    }

    public static function getSession()
    {
        $orcidID = $_SESSION[self::$SESSION_ORCID_ID];
        $orcid = ORCIDRecord::find($orcidID);
        if (!$orcid) {
            throw new \Exception("ORCID $orcidID not found");
        }
        return $orcid;
    }
}