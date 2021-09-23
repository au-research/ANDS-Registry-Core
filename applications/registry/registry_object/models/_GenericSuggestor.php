<?php
class _GenericSuggestor extends CI_Model {

	public $ro;
	
	public function set_ro($ro) {
		$this->ro = $ro;
	}

	public function suggest() {}

	function __construct() {
		parent::__construct();
		set_exception_handler('json_exception_handler');
		$this->ro = null;
	}
}