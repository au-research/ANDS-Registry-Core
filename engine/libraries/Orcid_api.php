<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * ORCID class for use globally
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
use ANDS\Registry\Providers\ORCID\ORCIDExport as ORCIDExport;
use ANDS\Registry\Providers\ORCID\ORCIDRecord as ORCIDRecord;

/**
 * TODO: Deprecate this file in favor of
 * ORCIDAuthenticator,
 * ORCIDProvider,
 * ORCIDController
 */

if (!function_exists('http_parse_headers')) {
    function http_parse_headers($raw_headers) {
        $headers = array();
        $key = '';

        foreach(explode("\n", $raw_headers) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[$h[0]]))
                    $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                }
                else {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                }

                $key = $h[0];
            }
            else {
                if (substr($h[0], 0, 1) == "\t")
                    $headers[$key] .= "\r\n\t".trim($h[0]);
                elseif (!$key)
                    $headers[0] = trim($h[0]);
            }
        }

        return $headers;
    }
}


class Orcid_api
{

    private $api_uri = null;
    private $service_uri = null;
    private $client_id = null;
    private $client_secret = null;
    private $redirect_uri = null;
    private $access_token = null;
    private $refresh_token = null;
    private $orcid_id = null;
    private $pub_api_url = null;
    private $ORCIDRecord = null;
    private $db;
    private $log_table = 'logs';

    /**
     * Construction of this class
     */
    function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('session');
        $this->db = $this->CI->db;
        $this->init();
    }

    function init()
    {
        $this->service_uri = $this->CI->config->item('gORCID_SERVICE_BASE_URI');
        $this->pub_api_url = $this->CI->config->item('gORCID_PUB_API_URI');
        $this->api_uri = $this->CI->config->item('gORCID_API_URI');
        $this->client_id = $this->CI->config->item('gORCID_CLIENT_ID');
        $this->client_secret = $this->CI->config->item('gORCID_CLIENT_SECRET');
        $this->redirect_uri = registry_url('orcid/auth');
    }

    /**
     * Authenticate with the API service using oauth
     * @param  string $code auth_code
     * @return data
     */
    function oauth($code)
    {
        $post_array = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirect_uri
        );
        $post_string = http_build_query($post_array);
        $url = $this->service_uri . 'oauth/token';
        $data = curl_post($url, $post_string, array('Accept: application/json'));
        return $data;
    }

    function log($orcid_id)
    {
        $this->db->insert($this->log_table,
            array(
                "type_id" => $orcid_id,
                "date_modified" => date('Y-m-d H:i:s', time()),
                "type" => "orcid_auth",
                "msg" => 'orcid authentication for ' . $orcid_id
            )
        );
        return $this->db->insert_id();
    }

    function set_orcid_id($id)
    {
        $this->loadOrcidRecord($id);
        $this->CI->session->set_userdata('orcid_id', $id);
    }

    function get_orcid_id()
    {
        if ($this->orcid_id) {
            return $this->orcid_id;
        } else {
            if ($this->CI->session->userdata('orcid_id')) {
                return $this->CI->session->userdata('orcid_id');
            } else {
                return false;
            }
        }
        return false;
    }

    function set_access_token($token)
    {
        $this->access_token = $token;
        $this->CI->session->set_userdata('access_token', $token);
        $this->ORCIDRecord->saveAccessToken($token);
    }

    function set_refresh_token($token)
    {
        $this->refresh_token = $token;
        $this->CI->session->set_userdata('refresh_token', $token);
        $this->ORCIDRecord->saveRefreshToken($token);
    }

    function get_access_token()
    {
        if ($this->access_token) {
            return $this->access_token;
        } else {
            if ($this->CI->session->userdata('access_token')) {
                return $this->CI->session->userdata('access_token');
            } else {
                return false;
            }
        }
        return false;
    }



    private function loadOrcidRecord($identifier)
    {

        $lookup_endpoint = $this->pub_api_url . $identifier . '/record';
        $this->ORCIDRecord = ORCIDRecord::find($identifier);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $lookup_endpoint);

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        $result = curl_exec($ch);
        curl_close($ch);
        $resultArray = json_decode($result, true);

        if (isset($resultArray['response-code']) and $resultArray['response-code'] == 404) {
            return $resultArray['developer-message'];
        }

        if ($this->ORCIDRecord == null) {
            $this->ORCIDRecord = new ORCIDRecord;
            $this->ORCIDRecord->orcid_id = $identifier;
            $this->ORCIDRecord->full_name = $this->getFullNameFromResult($resultArray);
            $this->ORCIDRecord->record_data = $result;
            $this->ORCIDRecord->save();
        } else {
            $this->ORCIDRecord->saveRecord($this->getFullNameFromResult($resultArray), $result);
        }
        return $this->ORCIDRecord;

    }

    public function getFullNameFromResult($bio)
    {
        $first_name = $bio['person']['name']['given-names']['value'];
        $last_name = $bio['person']['name']['family-name']['value'];
        $credit_name = "";//$bio['person']['name']['credit-name']['value'];

        return $first_name . " " . $last_name;

    }


    public function getORCIDRecord($identifier)
    {
        $this->ORCIDRecord = ORCIDRecord::find($identifier);
        $oneDayAgo = new DateTime('15 days ago');
        if ($this->ORCIDRecord === null || $this->ORCIDRecord->updated_at <= $oneDayAgo) {
            $this->ORCIDRecord = $this->loadOrcidRecord($identifier);
        }
        return $this->ORCIDRecord;
    }
    
}