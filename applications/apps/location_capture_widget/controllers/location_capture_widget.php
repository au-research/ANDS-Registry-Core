<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 *  
 */
class Location_capture_widget extends MX_Controller {

	function index(){
		$data['js_lib'] = array('core','prettyprint','location_capture_widget');
		$data['scripts'] = array();
		$data['title'] = 'Location Capture Widget';
		$this->load->view('documentation', $data);
		
	}
	
	function demo()	{
        $data['title'] = 'Location Capture Widget - ANDS';
        $data['js_lib'] = array('core', 'location_capture_widget', 'prettyprint','google_map');
        $data['scripts'] = array('location_capture_widget_loader');

		$this->load->view('demo',$data);
	}

	function download($min=''){
		$this->load->library('zip');
		if($min=='minified'){
			$this->zip->read_file('./applications/apps/location_capture_widget/assets/dist/location_capture_widget_v2.min.css');
			$this->zip->read_file('./applications/apps/location_capture_widget/assets/dist/location_capture_widget_v2.min.js');
		}elseif($min=='full'){
			$this->zip->read_dir('./applications/apps/location_capture_widget/assets/css/', false);
			$this->zip->read_dir('./applications/apps/location_capture_widget/assets/js/', false);
			$this->zip->read_dir('./applications/apps/location_capture_widget/assets/dist/', false);
		}else{
			$this->zip->read_file('./applications/apps/location_capture_widget/assets/css/location_capture_widget_v2.css');
			$this->zip->read_file('./applications/apps/location_capture_widget/assets/js/location_capture_widget_v2.js');
		}
		$this->zip->download('location_capture_widget_v2.zip');
	}
}
	