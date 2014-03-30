<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * ORCID class for use globally
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Orcid_api {

	private $api_uri = null;
    private $service_uri = null;
    private $client_id = null;
    private $client_secret = null;
    private $redirect_uri = null;
    private $access_token = null;
    private $orcid_id = null;

    private $db;
    private $log_table = 'logs';

	/**
	 * Construction of this class
	 */
	function __construct(){
        $this->CI =& get_instance();
		$this->CI->load->library('session');
        $this->db = $this->CI->db;
		$this->init();
    }

    function init(){
        $this->service_uri = $this->CI->config->item('gORCID_SERVICE_BASE_URI');
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
    function oauth($code){
        $post_array = array(
            'client_id'=>$this->client_id,
            'client_secret'=>$this->client_secret,
            'grant_type'=>'authorization_code',
            'code'=>$code,
            'redirect_uri'=>$this->redirect_uri
        );
        $post_string = http_build_query($post_array);
        $url = $this->api_uri.'oauth/token';
        $data = curl_post($url, $post_string, array('Accept: application/json'));
        return $data;
    }

    function log($orcid_id){
        $this->db->insert($this->log_table, 
            array(
                "type_id" => $orcid_id,
                "date_modified" => date('Y-m-d H:i:s',time()), 
                "type" => "orcid_auth", 
                "msg" => 'orcid authentication for '. $orcid_id
            )
        );
        return $this->db->insert_id();
    }

    function set_orcid_id($id){
        $this->orcid_id = $id;
        $this->CI->session->set_userdata('orcid_id', $id);
    }

    function get_orcid_id(){
        if($this->orcid_id){
            return $this->orcid_id;
        }else{
            if($this->CI->session->userdata('orcid_id')){
                return $this->CI->session->userdata('orcid_id');
            }else{
                return false;
            }
        }
        return false;
    }

    function set_access_token($token){
        $this->access_token = $token;
        $this->CI->session->set_userdata('access_token', $token);
    }

    function get_access_token(){
        if($this->access_token){
            return $this->access_token;
        }else{
            if($this->CI->session->userdata('access_token')){
                return $this->CI->session->userdata('access_token');
            }else{
                return false;
            }
        }
        return false;
    }

    /**
     * Get orcid XML of orcid id, if access_token is not set, it will return public information
     * @return object_xml         
     */
    function get_full(){
        $opts = array(
            'method'=>'GET',
            'header'=>'Accept: application/orcid+json'
        );
        $header = array("Accept:application/orcid+json; charset=utf-8");
        if(!$this->get_orcid_id() && !$this->get_access_token()){
            return false;
        }else{
            $url = $this->api_uri.'v1.1/'.$this->get_orcid_id().'/orcid-profile/';
            // $context = stream_context_create($opts);
            if($this->get_access_token()) $url.='?access_token='.$this->get_access_token();
            // $result = @file_get_contents($url.'s', true, $context);
            // $result = curl_post($url,'', array('header'=>'Accept: application/orcid+json'));
            $result = curl_file_get_contents($url, $header);
            $re = json_decode($result, true);
            if(isset($re['error'])){
                return false;
            }else{
                return $result;
            }
        }
    }

    /**
     * POST xml to orcid works
     * @param  [type] $xml [description]
     * @return [type]      [description]
     */
    function append_works($xml){
        if(!$this->get_orcid_id() && !$this->get_access_token()){
            return false;
        }
        $url = $this->api_uri.'v1.1/'.$this->get_orcid_id().'/orcid-works/';
        $url.='?access_token='.$this->get_access_token();
        $data = curl_post($url, $xml);
        if(trim($data)==''){
            return 1;
        }else return $data;
    }

    
}