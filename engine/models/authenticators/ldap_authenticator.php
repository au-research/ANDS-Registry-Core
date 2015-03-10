<?php

/**
 * Authenticator for LDAP Accounts
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
require_once('engine/models/authenticator.php');
class LDAP_authenticator extends Authenticator {

	public function authenticate() {
		$this->auth_domain = gCOSI_AUTH_LDAP_HOST;

		$this->load->helper('ldap');
		$LDAPAttributes = array();
		$LDAPMessage = "";
		$successful = authenticateWithLDAP($this->params['username'], $this->params['password'], $LDAPAttributes, $LDAPMessage);

		if ($successful) {
			$role = $this->cosi_db->get_where('roles', array('role_id'=>$this->params['username'], 'authentication_service_id'=>gCOSI_AUTH_METHOD_LDAP));
			if ($role->num_rows()==0) throw new Exception ('Role '.$this->params['username'].' not found!');
			$user = $role->row(1);
			$this->return_roles($user);
		} else {
			throw new Exception('Login failed. Bad credentials');
		}
	}

	public function redirect_hook($to) {}
}