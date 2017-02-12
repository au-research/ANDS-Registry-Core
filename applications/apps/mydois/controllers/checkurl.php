<?php 
class Checkurl extends MX_Controller {
	
	public function index(){
		$this->load->model('doitasks');
		$this->doitasks->checkurl();
	}

}
?>