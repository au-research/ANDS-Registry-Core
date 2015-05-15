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

	public function services($class='', $id='', $method='') {

		//header
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		set_exception_handler('json_exception_handler');

		if ($class != 'vocabs') throw new Exception('/vocabs required');

		$result = '';
		if ($id=='') {
			//get All vocabs listed
			//use test data for now
			$vocabs = $this->vocab->test_vocabs();
			$result = $vocabs;
		} else if($id!='') {
			$vocabs = $this->vocab->test_vocabs();
			if (isset($vocabs[$id])) {
				$vocab = $vocabs[$id];
				$result = $vocab;
			} else {
				throw new Exception('Vocab ID '. $id. ' not found');
			}
		}

		echo json_encode(
			array(
				'status' => 'OK',
				'message' => $result
			)
		);
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
		//test getting the documents
		$test_records = $this->vocab->test_vocabs();
		// echo json_encode($test_records);

		//test indexing the documents
		$solr_doc = array();
		foreach ($test_records as $record) {
			$solr_doc[] = $record->indexable_json();
		}
		$this->load->library('solr');
		$this->solr->setUrl('http://localhost:8983/solr/vocabs/');
		$solr_doc = json_encode($solr_doc);
		$add_result = $this->solr->add_json($solr_doc);
		$commit_result = $this->solr->commit();

		echo json_encode($add_result);
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