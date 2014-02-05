<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MX_Controller {

	public function index(){
		// $this->load->library('HybridAuthLib');
		// $this->load->library('User');
		// var_dump($this->user->name());
		// var_dump($this->hybridauthlib->getConnectedProviders());
		// echo anchor('auth/login/Twitter','Login With Twitter.').' ';
		// echo anchor('auth/login/Facebook','Login With Facebook.').' ';
		// echo anchor('auth/login/Google','Login With Google.').' ';
		// echo anchor('auth/login/LinkedIn','Login With LinkedIn.').' ';
		$this->getUser();

		// $this->load->library('User');
		// echo $this->user->oauth_loggedin();
	}



	public function getUser(){
		$this->output->set_header('Content-type: application/json');
		$data = array();
		$this->load->library('HybridAuthLib');
		// $this->load->library('User');

		if(sizeof($this->hybridauthlib->getConnectedProviders()) > 0){
			foreach($this->hybridauthlib->getConnectedProviders() as $conn){
				try{
					$service = $this->hybridauthlib->authenticate($conn);
					if($service->isUserConnected()){
						$user_profile = $service->getUserProfile();
						$data['logged_in'] = true;
						$data[$conn] = array(
							'name' => $user_profile->displayName,
							'identifier' => $user_profile->identifier,
							'profileURL' => $user_profile->profileURL,
							'avatar' => $user_profile->photoURL
						);
					}else{
						show_error('Cannot authenticate');
					}
				}catch(Exception $e){

				}
			}
		}
		// else if($this->user->loggedIn()){
		// 	$data['name'] = $this->user->name();
		// 	$data['identifier'] = $this->user->identifier();
		// 	$data['logged_in'] = true;
		// }
		else{
			$data['logged_in'] = false;
		}

		echo json_encode($data);
	}

	public function login($provider){
		$redirect = $this->input->get('redirect');
		if(!$redirect) $redirect = '/';
		try{
			$this->load->library('HybridAuthLib');
			if ($this->hybridauthlib->providerEnabled($provider)){
				$service = $this->hybridauthlib->authenticate($provider);

				if ($service->isUserConnected()){
					$user_profile = $service->getUserProfile();
					$data['user_profile'] = $user_profile;
					redirect($redirect);
				}else{
					show_error('Cannot authenticate user');
				}
			}else{
				show_404($_SERVER['REQUEST_URI']);
			}
		}catch(Exception $e){
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

			show_error('Error authenticating user. '.$e);
		}
	}

	public function logout($provider=''){
		$redirect = $this->input->get('redirect');
		if(!$redirect) $redirect = '/';
		$this->load->library('HybridAuthLib');
		if($provider!=''){
			$service = $this->hybridauthlib->getAdapter($provider);
		}else{
			$this->hybridauthlib->logoutAllProviders();
			redirect($redirect);
		}
	}

	public function oauth(){
		if ($_SERVER['REQUEST_METHOD'] === 'GET'){
			$_GET = $_REQUEST;
		}
		require_once FCPATH.'/assets/lib/hybridauth/index.php';
	}

}