<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Grant_widget extends MX_Controller {

	function index(){
		$data['title'] = 'Grant Widget - ANDS';
		$data['scripts'] = array('grant_widget_loader');
		$data['js_lib'] = array('core', 'grant_widget', 'prettyprint');
		$this->load->view('documentation', $data);
	}


	function proxy(){
		
	}

	function download($min=''){
		$this->load->library('zip');
		if($min=='minified'){
			$this->zip->read_file('./applications/apps/grant_widget/assets/dist/grant_widget.min.css');
			$this->zip->read_file('./applications/apps/grant_widget/assets/dist/grant_widget.min.js');
		}elseif($min=='full'){
			$this->zip->read_dir('./applications/apps/Grant_widget/assets/css/', false);
			$this->zip->read_dir('./applications/apps/Grant_widget/assets/js/', false);
			$this->zip->read_dir('./applications/apps/Grant_widget/assets/dist/', false);
		}else{
			$this->zip->read_file('./applications/apps/grant_widget/assets/css/grant_widget.css');
			$this->zip->read_file('./applications/apps/grant_widget/assets/js/grant_widget.js');
		}
		$this->zip->download('Grant_widget.zip');
	}

    private function JSONP($callback, $r){
        echo ($callback) . '(' . json_encode($r) . ')';
    }

}
