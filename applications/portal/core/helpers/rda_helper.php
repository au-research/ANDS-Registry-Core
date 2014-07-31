<?php
function rda_exception_handler( $e ) {

	if ($e instanceof ErrorException)
	{
		notifySiteAdmin("-1", $e->getMessage(), "...", "");
	}

    $_ci =& get_instance(); // CI super object to access load etc.
    
	$data['js_lib'] = array('core');
	$data['scripts'] = array();
	$data['title'] = 'An error occurred!';

    echo $_ci->load->view( 'rda_header' , $data , true); 
    
   	echo $_ci->load->view( 'rda_exception' , array("message" => $e->getMessage()) , true );
   
    echo $_ci->load->view( 'rda_footer' , $data , true);
}
set_exception_handler('rda_exception_handler');

function oauth_loggedin(){
	$CI =& get_instance();
	$CI->load->library('HybridAuthLib');
	if(sizeof($CI->hybridauthlib->getConnectedProviders()) > 0){
		return true;
	}else return false;
}

function oauth_getConnectedService(){
	$CI =& get_instance();
	$CI->load->library('HybridAuthLib');
	$connected = $CI->hybridauthlib->getConnectedProviders();
	if(is_array($connected) && sizeof($connected) > 0){
		return $connected[0];
	}else return false;
}

function oauth_getAccessToken($provider=''){
	$CI =& get_instance();
	$CI->load->library('session');
	if($CI->session->userdata('oauth_access_token')){
		return $CI->session->userdata('oauth_access_token');
	}else{
		$CI->load->library('HybridAuthLib');
		$service = $CI->hybridauthlib->getAdapter($provider);
		$access_token = $service->getAccessToken();
		return $access_token['access_token'];
	}
	
}

function oauth_getUser(){
	$CI =& get_instance();
	$CI->load->library('HybridAuthLib');
	try {
		$connected = oauth_getConnectedService();
		if(!$connected) return false;
		$access_token = oauth_getAccessToken($connected);
		if(!$access_token) return false;

		// $service = $CI->hybridauthlib->getAdapter($connected[0]);
		$db = $CI->load->database('portal', TRUE);
		$query = $db->get_where('users', array('provider'=>$connected, 'access_token'=>$access_token));

		if($query->num_rows()==0) return false;

		$profile = $query->first_row();
		$data = array(
			'service' => $connected,
			'profile' =>json_decode($profile->profile)
		);
		return $data;
	} catch (Exception $e){
		return false;
	}
	
	
}