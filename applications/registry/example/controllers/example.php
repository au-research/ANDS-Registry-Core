<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//mod_enforce('mydois');

/**
 *  
 */
class Example extends MX_Controller {

	function index()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array('examplejs');
		$data['styles'] = array('examplestyle');
		$data['title'] = 'Example Module';
		$this->load->view('sample_view', $data);
		
	}
}
	