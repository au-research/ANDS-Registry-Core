<?php

/**
 * Authenticator for AAF Rapid Connect
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
require_once('engine/models/authenticator.php');
class Aaf_rapid_authenticator extends Authenticator {

	private $jwt_token = false;

	public function authenticate() {
		try{
			$this->auth_domain = 'aaf.edu.au';

			$this->load->library('JWT');
			$secret = get_config_item('aaf_rapidconnect_secret');

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

	        //register affilication
	        $data = array(
	        	'parent_role_id'=>$username,
	        	'child_role_id'=>'SHIB_AUTHENTICATED',
	        	'created_who'=>'SYSTEM'
	        );
	        $result = $this->cosi_db->get_where('roles', $data);
	        if($result->num_rows()==0){
	        	$this->cosi_db->insert('roles_relation', $data);
	        }
	       	
	       	//ok, now we have the role
	       	$result = $this->cosi_db->get_where('roles', array('username'=>$username));
	       	if ($result->num_rows() > 0){
				$this->return_roles($result->row(1));
				return;
			}
		} catch (Exception $e) {
			redirect('auth/login/#/?error=aaf_error&message='.$e->getMessage());
		}
		

	}

	public function load_params($params){
		if (!isset($params['assertion'])) throw new Exception('JWT assertion failure');
		$this->jwt_token = $params['assertion'];
	}

}