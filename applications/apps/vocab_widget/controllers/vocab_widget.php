<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Vocab_widget extends MX_Controller {

	function index(){
		$data['js_lib'] = array('core','prettyprint');
		$data['scripts'] = array();
		$data['title'] = 'Vocabulary Widget - ANDS';
		$this->load->view('documentation', $data);
	}

	function proxy(){
		$solr_base = get_config_item('solr_url');
		$sissvoc_base = get_config_item('sissvoc_url');
		$this->load->view("proxy", array('solr_base' => $solr_base,
						 'sissvoc_base' => $sissvoc_base));
	}

	function demo(){
		$data['title'] = "ANDS Vocab widget";
		$data['scripts'] = array('vocab_widget_loader');
		$data['js_lib'] = array('core', 'vocab_widget');
		$this->load->view('demo', $data);
	}

	function demo2(){
		$data['title'] = "ANDS Vocab widget";
		$data['scripts'] = array('vocab_widget_loader');
		$data['js_lib'] = array('core', 'vocab_widget');
		$this->load->view('demo2', $data);
	}


	function download($min=''){
		$this->load->library('zip');
		if($min=='minified'){
			$this->zip->read_file('./applications/apps/vocab_widget/assets/dist/vocab_widget.min.css');
			$this->zip->read_file('./applications/apps/vocab_widget/assets/dist/vocab_widget.min.js');
		}elseif($min=='full'){
			$this->zip->read_dir('./applications/apps/vocab_widget/assets/css/', false);
			$this->zip->read_dir('./applications/apps/vocab_widget/assets/js/', false);
			$this->zip->read_dir('./applications/apps/vocab_widget/assets/dist/', false);
		}else{
			$this->zip->read_file('./applications/apps/vocab_widget/assets/css/vocab_widget.css');
			$this->zip->read_file('./applications/apps/vocab_widget/assets/js/vocab_widget.js');
		}
		$this->zip->download('vocab_widget.zip');

	}

}
