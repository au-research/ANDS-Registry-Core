<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class _user {
	public $prop;
	private $ci;

	function __construct($id) {
		$this->ci =& get_instance();
		if($this->ci->user->isLoggedIn()){
			$this->init($id);
		}
	}

	function init($id, $populate = array('core')) {
		$this->identifier = $this->ci->user->localIdentifier;
		$this->name = $this->ci->user->name();
	}

	public function __get($property) {
		
	}

	public function __set($property, $value) {
		
	}

}