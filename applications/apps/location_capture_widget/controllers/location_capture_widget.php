<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 *  
 */
class Location_capture_widget extends MX_Controller {

	function index()
	{
		$data['js_lib'] = array('core','prettyprint');
		$data['scripts'] = array();
		$data['title'] = 'Location Capture Widget';
		$this->load->view('documentation', $data);
		
	}
	
	function demo()
	{
		$this->load->view('demo');
	}

	function download($min=''){
		$this->load->library('zip');
		if($min=='minified'){
			$this->zip->read_file('./applications/apps/location_capture_widget/assets/dist/location_capture_widget.min.css');
			$this->zip->read_file('./applications/apps/location_capture_widget/assets/dist/location_capture_widget.min.js');
		}elseif($min=='full'){
			$this->zip->read_dir('./applications/apps/location_capture_widget/assets/css/', false);
			$this->zip->read_dir('./applications/apps/location_capture_widget/assets/js/', false);
			$this->zip->read_dir('./applications/apps/location_capture_widget/assets/dist/', false);
		}else{
			$this->zip->read_file('./applications/apps/location_capture_widget/assets/css/location_capture_widget.css');
			$this->zip->read_file('./applications/apps/location_capture_widget/assets/js/location_capture_widget.js');
		}
		$this->zip->download('location_capture_widget.zip');
	}
}
	