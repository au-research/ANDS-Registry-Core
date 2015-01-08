<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Portal_user extends CI_Model {

	public $identifier;
	public $name;
	public $user_data;

	public function getCurrentUser(){
		$this->identifier = $this->user->localIdentifier();
		$this->name = $this->user->name();
		$this->authMethod = $this->user->authMethod();
		$this->function = $this->user->functions();
		$this->user_data = $this->getUserData($this->identifier);
		unset($this->portal_db);
		return $this;
	}

	private function getUserData($role_id) {
		$this->portal_db = $this->load->database('portal', TRUE);
		$result = $this->portal_db->get_where('user_data', array('role_id'=>$role_id));
		if($result->num_rows() > 0){
			$r = $result->first_row();
			return json_decode($r->user_data, true);
		} else {
			//create a new user data row for this user
			$data = array('role_id'=>$role_id, 'user_data'=>'{}');
			$this->portal_db->insert('user_data', $data);
		}
	}

	public function add_user_data($data) {
		$user = $this->getCurrentUser();
		$user_data = $user->user_data;
		$user_data[$data['type']][] = $data['value'];
		$data = array('user_data' => json_encode($user_data));
		$this->portal_db = $this->load->database('portal', TRUE);
		$this->portal_db->where('role_id', $user->identifier)->update('user_data', $data);
	}

	public function get_user_data($type='') {
		if (!$this->user_data) {
			$user = $this->getCurrentUser();
			$user_data = $user->user_data;
		} else {
			$user_data = $this->user_data;
		}
	
		if ($type=='') {
			return $user_data;
		} elseif($user_data[$type]) {
			return $user_data[$type];
		}
		return false;
	}

	public function has_saved_search($id) {
		$user_data = $this->get_user_data('saved_record');
		foreach($user_data as $ud) {
			if($ud['id']==$id) return true;
		}
		return false;
	}

	function __construct() {
		parent::__construct();
	}
}