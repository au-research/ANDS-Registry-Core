<?php

/**
 * Authenticator for AAF Rapid Connect
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
require_once('engine/models/authenticator.php');
class Aaf_rapid_authenticator extends Authenticator {

	private $jwt_token = false;

	// TODO use AAFRapidAuthenticator class instead
	public function authenticate() {
		try{
			$this->auth_domain = 'aaf.edu.au';

			$this->load->library('JWT');

			// multiple [dot] notation for config get doesn't work
			// $secret = \ANDS\Util\config::get('oauth.AAF_RapidConnect.keys.secret');

            // get secret properly
            $conf = \ANDS\Util\Config::get('oauth');
            $config = $conf['providers']['AAF_RapidConnect'];
            $secret = $config['keys']['secret'];

			$decoded = $this->jwt->decode($this->jwt_token, $secret);

			$email = $decoded->{'https://aaf.edu.au/attributes'}->mail;
			$displayName = $decoded->{'https://aaf.edu.au/attributes'}->displayname;
			$persistent_id = $decoded->{'https://aaf.edu.au/attributes'}->edupersontargetedid;

			//try to match by persistent id
			$result = $this->cosi_db->get_where('roles', array('authentication_service_id'=>gCOSI_AUTH_METHOD_SHIBBOLETH,'persistent_id'=>$persistent_id));
			if ($result->num_rows() > 0) {
				$this->return_roles($result->row(1));
				return;
			}

			//try to match by email
			$result = $this->cosi_db->get_where('roles', array('authentication_service_id'=>gCOSI_AUTH_METHOD_SHIBBOLETH,'email' => $email));
			if ($result->num_rows() > 0) {
				$this->return_roles($result->row(1));
				return;
			}

			//try to match by name
			$result = $this->cosi_db->get_where('roles', array('authentication_service_id'=>gCOSI_AUTH_METHOD_SHIBBOLETH,'name'=>$displayName));
			if ($result->num_rows() > 0){
				$this->return_roles($result->row(1));
				return;
			}

			//ok, found no one, create new role
			$username = sha1($persistent_id);
			$data = array(
	            'role_id' => $username,
	            'role_type_id' => 'ROLE_USER',
	            'authentication_service_id'=>gCOSI_AUTH_METHOD_SHIBBOLETH,
	            'enabled'=>DB_TRUE,
	            'name'=> $displayName,
	            'shared_token' => isset($_SERVER['shib-shared-token']) ? $_SERVER['shib-shared-token'] : '',
	            'persistent_id' => $persistent_id,
	            'email' => $email,
	        );
	        $this->cosi_db->insert('roles', $data);

	        // ulog('insert role :'.$username);
	        // ulog('jwt token: '.$this->jwt_token);

	        //register affilication
	        $data = array(
	        	'parent_role_id'=>$username,
	        	'child_role_id'=>'SHIB_AUTHENTICATED',
	        	'created_who'=>'SYSTEM'
	        );
	        $result = $this->cosi_db->get_where('roles', array('role_id'=>$username));
	        if($result->num_rows()==0){
	        	$this->cosi_db->insert('roles_relation', $data);
	        }

	       	//ok, now we have the role
	       	$result = $this->cosi_db->get_where('roles', array('role_id'=>$username));
	       	if ($result->num_rows() > 0){
				$this->return_roles($result->row(1));
				return;
			}
		} catch (Exception $e) {
			redirect('auth/login/#/?error=aaf_error&message='.$e->getMessage());
		}
	}

	public function load_params($params){
		$ci =& get_instance();
		if ($ci->user->isLoggedIn()) {
			$this->post_authentication_hook();
		} else {
			if (!isset($params['assertion'])) throw new Exception('JWT assertion failure');
			$this->jwt_token = isset($params['assertion']) ? $params['assertion']: '';
		}
	}

}