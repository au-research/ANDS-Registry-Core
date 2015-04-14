<?php

/**
 * Authenticator for Built in Accounts
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
require_once('engine/models/authenticator.php');
class Built_in_authenticator extends Authenticator {

	public function authenticate() {
		// sleep(2);
		$this->auth_domain = gPIDS_IDENTIFIER_SUFFIX;

		$result = $this->cosi_db->get_where('roles', 
			array('role_id' => $this->params['username'])
		);

		if($result->num_rows()==0) throw new Exception('Login failed: Role "'. $this->params['username']. '" is not recognised');

		$result = $this->cosi_db->get_where('authentication_built_in', 
			array(
				'role_id' => $this->params['username'],
				'passphrase_sha1' => sha1($this->params['password'])
			)
		);

		if($result->num_rows()==0) throw new Exception('Login failed: Bad credentials');

		$user = $result->row(1);
		$this->return_roles($user);
	}

	public function redirect_hook($to) {}
}