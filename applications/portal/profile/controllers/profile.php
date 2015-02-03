<?php
class Profile extends MX_Controller {
	function index(){
		if($this->user->isLoggedIn()) {
			$this->load->model('portal_user');
			$user = $this->portal_user->getCurrentUser();
			$this->blade
				->set('user', $user)
				->render('profile/dashboard');
		} else {
			redirect('profile/login');
		}
	}

	public function add_user_data($type) {
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