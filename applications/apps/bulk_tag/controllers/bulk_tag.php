<?php

class Bulk_tag extends MX_Controller {
	function index(){
		$data['title'] = 'Bulk Tagging Tool';
		$data['scripts'] = array('bulk_tag_app');
		$data['js_lib'] = array('core', 'angular', 'select2', 'location_capture_widget', 'googleapi', 'google_map');

		$this->load->model("registry/data_source/data_sources","ds");
		$this->load->model("registry/registry_object/registry_objects","ro");
	 	$dataSources = $this->ds->getOwnedDataSources();
		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['key'] = $ds->key;
			array_push($items, $item);
		}
		$data['dataSources'] = $items;

		$data['themepages'] = $this->ro->getAllThemePages();

		$this->load->view('bulk_tag_index', $data);
	}

	function __construct(){
		parent::__construct();
		acl_enforce('REGISTRY_USER');
	}
}