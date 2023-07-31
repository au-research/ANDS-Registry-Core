<?php
class Styles extends CI_Controller {

	public function index(){
		$data['content'] = 'Hello World! and Ben!';
		$data['title'] = 'ARMS Style Test';
		$this->load->view('index', $data);
	}
}
?>