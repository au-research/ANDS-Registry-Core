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
	 * Viewing a vocabulary by slug
	 * @return view
	 */
	public function view() {
		//use test records for now
		$slug = $this->input->get('any');
		if ($slug) {
			$test_records = $this->vocab->test_vocabs();
			$record = $test_records[$slug];
			if ($record) {
				$this->blade
					->set('vocab', $record)
					->render('vocab');
			}
		}
	}


	/**
	 * About Page
	 * @return view/html
	 */
	function about() {
		$this->blade->render('about');
	}


	/**
	 * Automated test functionality
	 */
	function test() {
		$test_records = $this->vocab->test_vocabs();
		echo json_encode($test_records);
	}


	/**
	 * Constructor Method
	 * Autload blade by default
	 */
	public function __construct() {
		parent::__construct();
		$this->load->model('vocabularies', 'vocab');
		$this->load->library('blade');
	}
}