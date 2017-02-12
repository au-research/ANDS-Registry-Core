<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 *  
 */
class Tr_dci_preview extends MX_Controller {

	function index()
	{
		acl_enforce('REGISTRY_USER');
		$this->load->model("registry/data_source/data_sources","ds");
	 	$data['data_sources'] = $this->ds->getOwnedDataSources();

		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'TR DCI Preview Tool';
		$this->load->view('query_interface', $data);
	}

		
}
	