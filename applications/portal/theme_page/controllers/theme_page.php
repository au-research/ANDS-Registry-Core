<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Theme_page extends MX_Controller {

	function index($slug=''){
		if($slug!=''){
			$this->view('slug');
		}else{
			$this->listing();
		}
	}

	function view($slug=''){
		$data['page'] = json_decode($this->fetch_theme_page_by_slug($slug), true);
		$data['scripts'] = array('portal_theme');
		$data['js_lib'] = array('angular', 'colorbox');
		$this->load->view('theme_page_index', $data);
	}

	function listing(){
		$data['index'] = json_decode($this->getThemePageIndex(), true);
		// var_dump($data);
		$data['scripts'] = array();
		$data['title'] = 'Theme Pages - Research Data Australia';
		$this->load->view('theme_page_listing', $data);
	}

	public function fetch_theme_page_by_slug($slug){
		$url = $this->config->item('registry_endpoint') . "getThemePage/" . $slug;
		$contents = @file_get_contents($url);
		return $contents;
	}

	public function getThemePageIndex(){
		$url = $this->config->item('registry_endpoint') . "getThemePageIndex/";
		$contents = @file_get_contents($url);
		return $contents;
	}
}