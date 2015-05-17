<?php
/**
 * Vocabs controller
 * This is the primary controller for the vocabulary module
 * This module is meant as a standalone with all assets, views and models self contained
 * within the applications/vocabs directory
 * @version 1.0
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Vocabs extends MX_Controller {

	/**
	 * Index / Home page
	 * Displaying the Home Page
	 * @return view/html
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	function index(){
		// header('Content-Type: text/html; charset=utf-8');
		$this->blade->render('index');
	}

	/**
	 * Viewing a vocabulary by slug
	 * @return view/html
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
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
	 * Search
	 * Displaying the search page
	 * @ignore Not used for now. Home page is a search hybrid
	 * @version 1.0
	 * @return view/html
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	public function search() {
		$this->blade->render('search');
	}

	/**
	 * Adding a vocabulary
	 * Displaying a view for adding a vocabulary
	 * Using the same CMS as edit
	 * @todo  ACL
	 * @return view
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */	
	public function add() {
		$this->blade
			->set('scripts', array('vocabs_cms'))
			->render('cms');
	}

	/**
	 * Edit a vocabulary
	 * Displaying a view for editing a vocabulary
	 * Using the same CMS as add but directed towards a vocabulary
	 * @todo ACL
	 * @param  string $slug slug of the vocabulary, unique for a vocabulary
	 * @return view
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	public function edit($slug=false) {
		if (!$slug) throw new Exception('Require a Vocabulary Slug to edit');
		// $vocab = $this->vocab->getBySlug($slug);
		$this->blade
			->set('scripts', array('vocabs_cms'))
			->render('cms');
	}

	/**
	 * Page Controller
	 * For displaying static pages that belongs to the vocabs module
	 * @param  $slug supported: [help|about|contribute]
	 * @return view
	 */
	public function page($slug) {
		$this->blade->render($slug);
	}

	/**
	 * Services Controller
	 * For allowing RESTful API against the Vocabs Portal Database / SOLR
	 * @param  string $class  [vocabs] context
	 * @param  string $id     [id] of the context
	 * @param  string $method [method] description of the query
	 * @return API response / JSON
	 * @example services/vocabs/ , services/vocabs/anzsrc-for , services/vocabs/rifcs/versions
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
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
	 * Automated test tools
	 * @version 1.0
	 * @internal Used as internal testing before rolling out automated test cases
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
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