<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Registry_objects extends CI_Model {

	public function getByID($id) {
		return new _ro($id, array('core', 'descriptions', 'relationships', 'subjects', 'spatial', 'temporal','citations','reuse','quality','dates','connectiontree','publications', 'identifiers','rights', 'contact','directaccess'));
	}

	public function getBySlug($slug) {

	}

	function __construct() {
		parent::__construct();
		include_once("_ro.php");
	}
}