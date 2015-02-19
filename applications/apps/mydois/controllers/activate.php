<?php 
class Activate extends MX_Controller {
	
	public function index(){
		$this->load->model('doitasks');
		$this->doitasks->activate();
	}

}
?>