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
// set_exception_handler('rda_exception_handler');

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

function oauth_getUser(){
	$CI =& get_instance();
	$CI->load->library('HybridAuthLib');
	$connected = $CI->hybridauthlib->getConnectedProviders();
	$service = $CI->hybridauthlib->authenticate($connected[0]);

	$data = array(
		'service' => $connected[0],
		'profile' =>$service->getUserProfile()
	);

	return $data;
}