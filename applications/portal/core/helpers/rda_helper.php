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
	try {
		$connected = $CI->hybridauthlib->getConnectedProviders();
		$service = $CI->hybridauthlib->authenticate($connected[0]);
		$data = array(
			'service' => $connected[0],
			'profile' =>$service->getUserProfile()
		);
		return $data;
	} catch (Exception $e){
		$error = 'Unexpected error';
		switch($e->getCode()){

			case 0 : $error = 'Unspecified error.'; break;
			case 1 : $error = 'Hybriauth configuration error.'; break;
			case 2 : $error = 'Provider not properly configured.'; break;
			case 3 : $error = 'Unknown or disabled provider.'; break;
			case 4 : $error = 'Missing provider application credentials.'; break;
			case 5 : log_message('debug', 'controllers.HAuth.login: Authentification failed. The user has canceled the authentication or the provider refused the connection.');
			         //redirect();
			         if (isset($service)){
			         	$service->logout();
			         }
			         show_error('User has cancelled the authentication or the provider refused the connection.');
			         break;
			case 6 : $error = 'User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again.';
			         break;
			case 7 : $error = 'User not connected to the provider.';
			         break;
		}

		if (isset($service)){
			$service->logout();
		}

		return $error;
	}
	
	
}