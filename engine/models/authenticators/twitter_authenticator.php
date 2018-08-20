<?php

/**
 * Authenticator for Social Accounts / Facebook
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

use Abraham\TwitterOAuth\TwitterOAuth;

require_once('engine/models/authenticator.php');
class Twitter_authenticator extends Authenticator {

    /**
     * @return bool|void
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     * @throws Exception
     */
    public function authenticate() {

	    $config = \ANDS\Util\Config::get('oauth.providers.Twitter');
        $key = $config['keys']['key'];
        $secret = $config['keys']['secret'];
        $connection = new TwitterOAuth($key, $secret);

        $requestToken = $connection->oauth("oauth/request_token", ['oauth_callback' => 'http://minhrda.ands.org.au/registry/auth/twitter']);
        $oauthToken = $requestToken['oauth_token'];
        $url = $connection->url('oauth/authorize', ['oauth_token' => $oauthToken]);
        redirect($url);

		$provider = 'Twitter';
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
				$user = $this->cosi_db->get_where('roles', array('role_id'=>$user_profile->identifier, 'authentication_service_id'=>'AUTHENTICATION_SOCIAL_TWITTER'));

				if($user->num_rows() > 0) {
					//found existing user, maybe updating some logs here
				} else {
					//create a new role
					$data = array(
						'role_id' => $user_profile->identifier,
						'role_type_id' => 'ROLE_USER',
						'authentication_service_id' => 'AUTHENTICATION_SOCIAL_TWITTER',
						'enabled' => DB_TRUE,
						'name' => $user_profile->displayName,
						'oauth_access_token' => $access_token,
						'oauth_data' => json_encode($user_profile),
						'email' => $user_profile->email
					);

					$this->cosi_db->insert('roles',$data);
					$user = $this->cosi_db->get_where('roles', array('role_id'=>$user_profile->identifier, 'authentication_service_id'=>'AUTHENTICATION_SOCIAL_TWITTER'));
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
