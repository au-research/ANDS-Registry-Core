<?php
class Registry_object extends MX_Controller {

	function view(){
		$this->load->model('registry_objects', 'ro');
		if($this->input->get('id')){
			$ro = $this->ro->getByID($this->input->get('id'));
		}
		
		echo json_encode($ro);
	}

}