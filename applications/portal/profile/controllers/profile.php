<?php
class Profile extends MX_Controller {
	function index(){
		if($this->user->isLoggedIn()) {
			$this->load->model('portal_user');
			$user = $this->portal_user->getCurrentUser();
			$this->blade
				->set('user', $user)
				->set('scripts', array('profile_app'))
				->render('profile/dashboard2');
		} else {
			redirect('profile/login');
		}
	}

	public function get_user_data() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		if ($this->user->isLoggedIn()) {
			$this->load->model('portal_user');
			$user = $this->portal_user->getCurrentUser();
			$data = $user->portal_user->getUserData($user->identifier);
			echo json_encode($data);
		} else {
			echo json_encode(array(
				'status' => 'error',
				'message' => 'User is not logged in'
			));
		}
	}

	public function test(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
    	$this->load->model('portal_user');

    	$user = $this->portal_user->getCurrentUser();

    	$saved_record = $user->user_data['saved_record'];
    	$saved_record['list'] = array('1','2','3');

    	echo json_encode($saved_record);

    	$list = array(
    		'name' => 'A Single List',
    		'created' => date("Y-m-d H:i:s"),
    		'records' => array()
    	);

	}

	public function add_user_data($type) {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$data = json_decode(file_get_contents("php://input"), true);
		$data = $data['data'];

		$this->load->model('portal_user');

		
		//prepare the data to be saved
		if ($type=='saved_search') {
			$data = array(
				'query_string' => $data
			);
			$saved_data = array(
				'type' => $type,
				'value' => $data
			);
			$this->portal_user->add_user_data($saved_data);
		} if ($type=='saved_record') {
			foreach($data as $d) {
				if (!$this->portal_user->has_saved_search($d['id'])){
					$saved_data = array(
						'type' => $type,
						'value' => array('id'=>$d['id'], 'slug'=>$d['slug'], 'url' => portal_url($d['slug'].'/'.$d['id']), 'title'=>$d['title'])
					);
					$this->portal_user->add_user_data($saved_data);
				}
			}
		}
	}



	public function current_user() {
		$this->load->model('portal_user');
		$user = $this->portal_user->getCurrentUser();

		//fix silly functions encoding
		$functions = array();
		foreach($user->function as $f) array_push($functions, $f);
		$user->function = $functions;
		
		echo json_encode($user);
	}

	function dashboard() {
		$this->index();
	}

	function login() {
		$authenticators = array(
			'built-in' => array(
				'slug' => 'built_in',
				'display' => 'Built In'
			),
			'ldap' => array(
				'slug'		=> 'ldap',
				'display' 	=> 'LDAP',
			),
			'social' => array(
				'slug'		=> 'social',
				'display'	=> 'Social'
			)
		);

		$default_authenticator = false;
		foreach($authenticators as $auth) {
			if(isset($auth['default']) && $auth['default']===true) {
				$default_authenticator = $auth['slug'];
				break;
			}
		}
		if(!$default_authenticator) $default_authenticator = 'built_in';

		$this->blade
			->set('authenticators', $authenticators)
			->set('default_authenticator', $default_authenticator)
			->set('scripts', array('login'))
			->render('profile/login');
	}

	function logout() {
		if($this->user->isLoggedIn()) {
			if(!session_id()) session_start();
			$this->session->sess_destroy();
			redirect('profile/login');
		} else {
			redirect('profile/login');
		}
	}

	public function __construct() {
		parent::__construct();
		$this->load->library('blade');
		$this->blade->set_template('omega');
	}
}