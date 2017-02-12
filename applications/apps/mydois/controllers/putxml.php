<?php 
class Putxml extends MX_Controller {
	
	public function index(){
		//$doi = $this->input->get('doi');
		$this->load->model('doitasks');
		$data['putxml']=$this->doitasks->putxml();
		print($data['xml']);	
	}

}
?>