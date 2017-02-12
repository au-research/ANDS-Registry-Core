<?php 
class Put_xml extends MX_Controller {
	
	public function index(){
		$this->load->model('doitasks');
		$this->doitasks->putxml();
	}

}
?>