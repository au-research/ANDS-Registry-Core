<?php

/**
 * Authenticator for Social Accounts / Facebook
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
require_once('engine/models/authenticator.php');
class Facebook_authenticator extends Authenticator {

	public function authenticate() {
		$provider = 'Facebook';
		$this->load->library('HybridAuthLib');
		try{
			$service = $this->hybridauthlib->authenticate($provider);
			if($service->isUserConnected()) {
				$user_profile = $service->getUserProfile();
				$access_token = $service->getAccessToken();
				$access_token = $access_token['access_token'];

				$allow = array('identifier', 'displayName', 'photoURL', 'firstName', 'lastName', 'email');
				foreach($user_profile as $u=>$thing){
					if(!in_array($u, $allow)) unset($user_profile->{$u});
				}
				
				//check if there's an existing profile
				$user = $this->cosi_db->get_where('roles', array('role_id'=>$user_profile->identifier, 'authentication_service_id'=>'AUTHENTICATION_SOCIAL_FACEBOOK'));

				if($user->num_rows() > 0) {
					//found existing user, maybe updating some logs here
				} else {
					//create a new role
					$data = array(
						'role_id' => $user_profile->identifier,
						'role_type_id' => 'ROLE_USER',
						'authentication_service_id' => 'AUTHENTICATION_SOCIAL_FACEBOOK',
						'enabled' => DB_TRUE,
						'name' => $user_profile->displayName,
						'oauth_access_token' => $access_token,
						'oauth_data' => json_encode($user_profile),
						'email' => $user_profile->email
					);

					$this->cosi_db->insert('roles',$data);
					$user = $this->cosi_db->get_where('roles', array('role_id'=>$user_profile->identifier, 'authentication_service_id'=>'AUTHENTICATION_SOCIAL_FACEBOOK'));
				}

				$user = $user->row(1);

				$this->return_roles($user);
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function load_params($params) {}
    private function check_req() {}
}
