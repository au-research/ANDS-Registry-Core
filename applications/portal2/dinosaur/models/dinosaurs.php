<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dinosaurs extends CI_Model {

	public function getByID($id) {
		return new _dino($id);
	}

	public function getBySlug($slug) {

	} 

	function __construct() {
		parent::__construct();
		include_once("_dino.php");
	}
}