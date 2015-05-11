<?php
/**
 * Vocab controller
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Vocabs extends MX_Controller {

	/**
	 * Index / Home page
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view/html
	 */
	function index(){
		// header('Content-Type: text/html; charset=utf-8');
		$this->blade->render('index');
	}


	/**
	 * About Page
	 * @return view/html
	 */
	function about() {
		$this->blade->render('about');
	}


	/**
	 * Constructor Method
	 * Autload blade by default
	 */
	public function __construct() {
		parent::__construct();
		$this->load->library('blade');
	}
}