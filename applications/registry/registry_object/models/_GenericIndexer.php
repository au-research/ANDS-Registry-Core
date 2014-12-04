<?php
class _GenericIndexer extends CI_Model {

	public $ro;
	
	public function set_ro($ro) {
		$this->ro = $ro;
	}

	function construct_payload() {}
	function commit() {}
	function optimize() {}

	function __construct() {
		parent::__construct();
		set_exception_handler('json_exception_handler');
		$this->ro = null;
	}
}