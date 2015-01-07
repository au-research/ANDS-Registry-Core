<?php
class Profile extends MX_Controller {
	function index(){
		if($this->user->isLoggedIn()) {
			$this->blade->render('profile/dashboard');
		} else {
			redirect('profile/login');
		}
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