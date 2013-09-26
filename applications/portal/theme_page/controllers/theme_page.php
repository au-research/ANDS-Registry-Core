<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Theme_page extends MX_Controller {

	function index($slug){
		$this->view('slug');
	}

	function view($slug){
		$data['page'] = json_decode($this->fetch_theme_page_by_slug($slug), true);
		$data['scripts'] = array('portal_theme');
		$data['js_lib'] = array('angular', 'colorbox');
		$this->load->view('theme_page_index', $data);
	}

	public function fetch_theme_page_by_slug($slug){
		$url = $this->config->item('registry_endpoint') . "getThemePage/" . $slug;
		$contents = @file_get_contents($url);
		return $contents;
	}
}