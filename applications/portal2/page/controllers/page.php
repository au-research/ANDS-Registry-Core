<?php
class Page extends MX_Controller {
	function index(){
		$this->load->library('blade');
		$this->blade->set('foo', 'bar')
						->set('an_array', array(1, 2, 3, 4))
						->append('an_array', 5)
						->set_data(array('more' => 'data', 'other' => 'data'))
						->render('test', array('message' => 'Hello World2!'));
	}
}