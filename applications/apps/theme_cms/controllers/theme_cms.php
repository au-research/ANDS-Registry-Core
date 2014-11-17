<?php

class Theme_cms extends MX_Controller {

	function index(){
		$data['title']='Theme CMS';
		$data['scripts'] = array('theme_cms_app');
		$data['js_lib'] = array('core', 'tinymce', 'angular', 'colorbox', 'select2', 'registry_widget', 'location_capture_widget', 'google_map');
		$this->load->view('theme_cms_index', $data);
	}

	function bulk_tag(){
		$data['title'] = 'Bulk Tagging Tool';
		$data['scripts'] = array('bulk_tag_app');
		$data['js_lib'] = array('core', 'angular', 'select2', 'location_capture_widget', 'googleapi', 'google_map');

		$this->load->model("registry/data_source/data_sources","ds");
	 	$dataSources = $this->ds->getOwnedDataSources();
		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['key'] = $ds->key;
			array_push($items, $item);
		}
		$data['dataSources'] = $items;

		$this->load->view('bulk_tag_index', $data);
	}

	public function get($slug){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$result = $this->theme_pages->get($slug);
		echo $result[0]['content'];
	}

	public function new_page(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$data = file_get_contents("php://input");
		echo $this->theme_pages->add($data);
	}

	public function delete_page(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$data = file_get_contents("php://input");
		echo $this->theme_pages->delete($data);
	}

	public function save_page(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$data = file_get_contents("php://input");
		echo $this->theme_pages->save($data);
	}

	public function generateSecretTag($slug){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$secretTag = 'themeSecret_'.$slug.'-'.rand(1,200);
		$result = $this->db->get_where('tags', array('name'=>$secretTag));
		if($result->num_rows > 0){
			echo $this->generateSecretTag($slug);
		}else{
			echo $secretTag;
		}
	}

	public function view($slug=''){
		$data['title'] = 'Theme CMS';
		$data['scripts'] = array('theme_cms');
		$data['js_lib'] = array('core', 'tinymce');
		$this->load->view('theme_cms_view', $data);
	}

	public function list_pages(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$pages = $this->theme_pages->get();
		echo json_encode($pages);
	}


	
	// Initialise
	function __construct(){
		parent::__construct();
		acl_enforce('PORTAL_STAFF');
		$this->load->helper('file');
		$this->load->model('theme_pages');
	}
}