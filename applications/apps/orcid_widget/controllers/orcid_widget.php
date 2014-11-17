<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Orcid_widget extends MX_Controller {

	function index(){
		$data['title'] = 'Orcid Widget - ANDS';
		$data['scripts'] = array('orcid_widget_loader');
		$data['js_lib'] = array('core', 'orcid_widget', 'prettyprint');
		$this->load->view('documentation', $data);
	}


	function proxy(){
		
	}

	function download($min=''){
		$this->load->library('zip');
		if($min=='minified'){
			$this->zip->read_file('./applications/apps/orcid_widget/assets/dist/orcid_widget.min.css');
			$this->zip->read_file('./applications/apps/orcid_widget/assets/dist/orcid_widget.min.js');
		}elseif($min=='full'){
			$this->zip->read_dir('./applications/apps/Orcid_widget/assets/css/', false);
			$this->zip->read_dir('./applications/apps/Orcid_widget/assets/js/', false);
			$this->zip->read_dir('./applications/apps/Orcid_widget/assets/dist/', false);
		}else{
			$this->zip->read_file('./applications/apps/orcid_widget/assets/css/orcid_widget.css');
			$this->zip->read_file('./applications/apps/orcid_widget/assets/js/orcid_widget.js');
		}
		$this->zip->download('Orcid_widget.zip');
	}
}
