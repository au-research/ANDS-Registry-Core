<?php
class Dinosaur extends MX_Controller {

	function view(){
		$this->load->model('dinosaurs', 'dino');
		if($this->input->get('id')){
			$ro = $this->dino->getByID($this->input->get('id'));
		}
		
		echo json_encode($ro);
	}

}