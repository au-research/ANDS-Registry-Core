<?php 
class Status extends MX_Controller {
	
	public function index(){
		$this->load->model('doitasks');
		$this->doitasks->status();
	}

}
?>