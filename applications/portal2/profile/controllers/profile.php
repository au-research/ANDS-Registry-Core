<?php
class Profile extends MX_Controller {
	function index(){
		$this->blade->render('home');
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
			->set('lib', array('angular13'))
			->render('profile/login');
	}

	public function __construct() {
		parent::__construct();
		$this->load->library('blade');
		$this->blade->set_template('omega');
	}
}