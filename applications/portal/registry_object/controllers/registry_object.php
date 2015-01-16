<?php
class Registry_object extends MX_Controller {

	private $components = array();

	/**
	 * Viewing a single registry object
	 * @param $_GET['id'] parsed through the dispatcher
	 * @todo  $_GET['slug'] or $_GET['any']
	 * @return HTML generated by view
	 */
	function view(){

		if($this->input->get('id')){
			$ro = $this->ro->getByID($this->input->get('id'));
		}

		$this->load->library('blade');

		$this->blade
			->set('scripts', array('view'))
			->set('lib', array('jquery-ui', 'dynatree'))
			->set('ro', $ro)
			->set('contents', $this->components['view'])
			->set('aside', $this->components['aside'])
            ->set('view_headers', $this->components['view_headers'])
			->set('url', $ro->construct_api_url())
			->render('registry_object/view');
	}

	/**
	 * Search View
	 * Displaying the search view for the current component
	 * @return HTML 
	 */
	function search() {
		//redirect to the correct URL if q is used in the search query
		if($this->input->get('q')) {
			redirect('search/#!/q='.$this->input->get('q'));
		}
		$this->load->library('blade');
		$this->blade
			->set('lib', array('ui-events', 'angular-ui-map', 'google-map'))
			->set('scripts', array('search_app'))
			->set('facets', $this->components['facet'])
			->set('search', true) //to disable the global search
			->render('registry_object/search');
	}

	/**
	 * Main search function
	 * SOLR search
	 * @param  string $class class restriction
	 * @return json
	 */
	function filter($class = 'collection') {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$data = json_decode(file_get_contents("php://input"), true);
		$filters = $data['filters'];

		// experiment with delayed response time
		// sleep(2);

		$this->load->library('solr');
		$this->solr->setFilters($filters);

		//returns this set of Facets
		foreach($this->components['facet'] as $facet){
			if ($facet!='temporal' && $facet!='spatial') $this->solr->setFacetOpt('field', $facet);
		}

		//flags, these are the only fields that will be returned in the search
		$this->solr->setOpt('fl', 'id,title,description,group,slug,spatial_coverage_centres,spatial_coverage_polygons');

		//highlighting
		$this->solr->setOpt('hl', 'true');
		$this->solr->setOpt('hl.fl', '*');
		$this->solr->setOpt('hl.simple.pre', '&lt;b&gt;');
		$this->solr->setOpt('hl.simple.post', '&lt;/b&gt;');

		//experiment hl attrs
		// $this->solr->setOpt('hl.alternateField', 'description');
		// $this->solr->setOpt('hl.alternateFieldLength', '100');
		// $this->solr->setOpt('hl.fragsize', '300');
		// $this->solr->setOpt('hl.snippets', '100');
		
		//restrict to default class
		$this->solr->setOpt('fq', '+class:'.$class);

		$this->solr->setFacetOpt('mincount','1');
		$this->solr->setFacetOpt('limit','100');
		$this->solr->setFacetOpt('sort','count');
		$result = $this->solr->executeSearch();

		echo json_encode($result);
	}

	/**
	 * List all attribute of a registry object
	 * for development only!
	 * @return json 
	 */
	function get() {
		$this->load->model('registry_objects', 'ro');
		if($this->input->get('id')){
			$ro = $this->ro->getByID($this->input->get('id'));
		}
		echo json_encode($ro);
	}

	/**
	 * Construction
	 * Defines the components that will be displayed and search for within the application
	 */
	function __construct() {
		parent::__construct();
		$this->load->model('registry_objects', 'ro');
		$this->components = array(
			'view' => array('descriptions','reuse-list','quality-list','dates-list', 'connectiontree','publications-list','related-objects-list',  'subjects-list', 'identifiers-list'),
			'aside' => array('access', 'citation-info','rights-info','contact-info'),
			'facet' => array('spatial','group', 'license_class', 'type', 'temporal'),
            'view_headers' => array('title','related-parties')
		);
	}
}