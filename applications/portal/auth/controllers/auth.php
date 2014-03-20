<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MX_Controller {

	public function index(){
		$this->getUser();
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
					// echo json_encode($data['user_profile']);

					$db = $this->load->database('portal', true);
					$access_token = $service->getAccessToken();
					$access_token = $access_token['access_token'];

					$users = $db->get_where('users', array('provider'=>$provider, 'identifier'=>$user_profile->identifier));

					$allow = array('identifier', 'displayName', 'photoURL', 'firstName', 'lastName');
					foreach($user_profile as $u=>$thing){
						if(!in_array($u, $allow)) unset($user_profile->{$u});
					}

					if($users->num_rows() > 0){
						$user = $users->first_row();
						$db->where('id', $user->id);
						$db->update('users', array('status'=>'logged_in', 'access_token'=>$access_token, 'profile'=>json_encode($user_profile)));
					}else{
						$user = array(
							'identifier' => $user_profile->identifier,
							'displayName' => $user_profile->displayName,
							'status' => 'logged_in',
							'provider' => $provider,
							'access_token' => $access_token,
							'profile' => json_encode($user_profile)
						);
						$db->insert('users', $user);
					}

					$this->load->library('session');
					$this->session->set_userdata('oauth_access_token', $access_token);

					redirect($redirect);
				}else{
					redirect($redirect);
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
						 redirect($redirect);
				         //show_error('User has cancelled the authentication or the provider refused the connection.');
				         break;
				case 6 : $error = 'User profile request failed. Most likely the user is not connected to the provider authentication needs to be done again.';
				         break;
				case 7 : $error = 'User not connected to the provider.';
				         break;
			}

			if (isset($service)){
				$service->logout();
			}
			$data['error'] = $error;
			// redirect($redirect);
			show_error('Error authenticating user. '.$e);
		}
	}

	public function logout(){
		$redirect = $this->input->get('redirect');
		if(!$redirect) $redirect = '/';
		$this->load->library('HybridAuthLib');
		$provider = oauth_getConnectedService();
		$service = $this->hybridauthlib->getAdapter($provider);

		$this->load->library('session');
		$access_token = $this->session->userdata('oauth_access_token');
		
		$db = $this->load->database('portal', true);
		$db->where('access_token', $access_token);
		$db->where('provider', $provider);
		$db->update('users', array('status'=>'logged_out'));

		$service->logOut();
		redirect($redirect);
		
	}

	public function oauth(){
		if ($_SERVER['REQUEST_METHOD'] === 'GET'){
			$_GET = $_REQUEST;
		}
		require_once FCPATH.'/assets/lib/hybridauth/index.php';
	}

}