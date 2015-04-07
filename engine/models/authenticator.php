<?php

/**
 * Generic Authenticator
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

class Authenticator extends CI_Model {

	public $params = null;
	public $cosi_db = null;
	public $auth_domain = null;
	public $name_overwrite = false;

	function __construct(){
		set_exception_handler('json_exception_handler');
		$this->cosi_db = $this->load->database('roles', TRUE);
	}

	public function load_params($params) {
		$this->params = $params;
		$this->check_req();
	}

	private function check_req() {
		$required_fields = array('username', 'password');
		foreach($required_fields as $req) {
			if(!isset($this->params[$req]) || trim($this->params[$req])==''){
				throw new Exception('Field '.$req.' is required');
			}
		}
	}

	public function return_roles($user){
		
		$role = $this->cosi_db->get_where('roles', array('role_id'=>$user->role_id));
		if($role->result()==0) throw new Exception('Role '.$user->role_id.' not found!');

		$role = $role->row();

		$roles = $this->getChildRoles($role->role_id, true);

		$ret = array('organisational_roles'=>array(), 'functional_roles'=>array());
		foreach($roles as $r) {
			if (trim($r['role_type_id']) == gCOSI_AUTH_ROLE_ORGANISATIONAL) {
				$ret['organisational_roles'][] = $r['role_id'];
			} else if (trim($r['role_type_id']) == gCOSI_AUTH_ROLE_FUNCTIONAL) {
				$ret['functional_roles'][] = $r['role_id'];
			}
		}

		$result = array(
			'role_id' => $role->role_id,
			'authentication_service_id' => $role->authentication_service_id,
			'user_identifier' => $role->role_id,
			'name' => ($this->name_overwrite) ? $this->name_overwrite : $role->name,
			'auth_domain' => $this->auth_domain,
			'last_login' => $role->last_login,
			'organisational_roles' => $ret['organisational_roles'],
			'functional_roles' => $ret['functional_roles'],
			'redirect_to'=>base_url()
		);

		$this->user->authComplete($result);

		//register last login
		$this->cosi_db->where('role_id', $role->role_id)->update('roles', array('last_login'=>date('Y-m-d H:i:s',time())));

		$this->post_authentication_hook();

		echo json_encode(
			array(
				'status' => 'SUCCESS',
				'message' => $result
			)
		);
	}

	public function post_authentication_hook(){
		$redirect = $this->input->get('redirect') ? $this->input->get('redirect') : 'auth/dashboard';
		
		

		$this->load->helper('cookie');
		if(get_cookie('auth_redirect')) {
			$redirect = get_cookie('auth_redirect');
			delete_cookie('auth_redirect');
		}

		if ($redirect =='profile' || $redirect == portal_url('profile')) {
			$redirect = portal_url('profile').'#!/dashboard';
		}


		$this->redirect_hook($redirect);
	}

	public function redirect_hook($redirect){
		redirect($redirect);
	}


	private function getChildRoles($role_id, $recursive = true, $prev = array()) {
		$roles = array();
		
		$related_roles = $this->cosi_db
				->select('role_relations.parent_role_id, roles.role_type_id, roles.name, roles.role_id')
				->from('role_relations')
				->join('roles', 'roles.role_id = role_relations.parent_role_id')
				->where('role_relations.child_role_id', $role_id)
				->where('enabled', DB_TRUE)
				->where('role_relations.parent_role_id !=', $role_id)
				->get();
		
		foreach($related_roles->result() AS $row)
		{
			$roles[] = array("role_id" => $row->parent_role_id, "role_type_id" => $row->role_type_id);
			if($recursive && !in_array($row->parent_role_id, $prev)) {
				array_push($prev, $row->parent_role_id);
				$child = $this->getChildRoles($row->parent_role_id, $recursive, $prev);
				if(sizeof($child) > 0) {
					$roles = array_merge($roles, $this->getChildRoles($row->parent_role_id, $recursive, $prev));
				}
			}
		}
		
		return $roles;
	}

	//OVERWRITE function for authentication
	public function authenticate() { return false; }
}