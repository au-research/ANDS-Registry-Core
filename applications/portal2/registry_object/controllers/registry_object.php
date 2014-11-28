<?php
class Registry_object extends MX_Controller {

	function view(){
		$this->load->model('registry_objects', 'ro');
		if($this->input->get('id')){
			$ro = $this->ro->getByID($this->input->get('id'));
		}

		$this->load->library('blade');
		$this->blade->set('ro', $ro)->set('sidebar', array(1,2,3,4))->render('registry_object/view');
	}

	function search() {
		$this->load->library('blade');
		$this->blade->render('registry_object/search');
	}

	function get() {
		$this->load->model('registry_objects', 'ro');
		if($this->input->get('id')){
			$ro = $this->ro->getByID($this->input->get('id'));
		}
		echo json_encode($ro);
	}

	function __construct() {
		parent::__construct();
	}

}