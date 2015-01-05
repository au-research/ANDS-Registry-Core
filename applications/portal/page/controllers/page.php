<?php
class Page extends MX_Controller {
	function index(){
		$this->blade->render('home');
	}

	function test(){
		$this->blade->set('foo', 'bar')
						->set('an_array', array(1, 2, 3, 4))
						->append('an_array', 5)
						->set_data(array('more' => 'data', 'other' => 'data'))
						->render('test', array('message' => 'Hello World2!'));
	}

	public function __construct() {
		parent::__construct();
		$this->load->library('blade');
		$this->blade->set_template('omega');
	}
}