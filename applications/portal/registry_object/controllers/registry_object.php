<?php
class Registry_object extends MX_Controller {

	private $components = array();

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

	function search() {
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

	function s($class = 'collection') {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');

		$data = json_decode(file_get_contents("php://input"), true);
		$filters = $data['filters'];

		// sleep(2);

		$this->load->library('solr');
		$this->solr->setFilters($filters);
		foreach($this->components['facet'] as $facet){
			if ($facet!='temporal' && $facet!='spatial') $this->solr->setFacetOpt('field', $facet);
		}
		$this->solr->setOpt('fl', 'id,title,description,group,slug,spatial_coverage_centres,spatial_coverage_polygons');
		$this->solr->setOpt('hl', 'true');
		$this->solr->setOpt('hl.fl', '*');
		$this->solr->setOpt('hl.simple.pre', '&lt;b&gt;');
		$this->solr->setOpt('hl.simple.post', '&lt;/b&gt;');
		// $this->solr->setOpt('hl.alternateField', 'description');
		// $this->solr->setOpt('hl.alternateFieldLength', '100');
		// $this->solr->setOpt('hl.fragsize', '300');
		// $this->solr->setOpt('hl.snippets', '100');
		// 
		// 
		
		//restrict to default class
		$this->solr->setOpt('fq', '+class:'.$class);

		$this->solr->setFacetOpt('mincount','1');
		$this->solr->setFacetOpt('limit','100');
		$this->solr->setFacetOpt('sort','count');
		$result = $this->solr->executeSearch();

		echo json_encode($result);
	}

	function get() {
		$this->load->model('registry_objects', 'ro');
		if($this->input->get('id')){
			$ro = $this->ro->getByID($this->input->get('id'));
		}
		echo json_encode($ro);
	}

	function __construct() {
		parent::__construct();
		$this->load->model('registry_objects', 'ro');
		$this->components = array(
            'view_headers' =>array('logo', 'title','related-parties'),
			'view' => array('descriptions','reuse-list','quality-list','dates-list', 'connectiontree','publications-list','related-objects-list',  'subjects-list', 'identifiers-list'),
			'aside' => array('access', 'citation-info','rights-info','contact-info'),
			'facet' => array('spatial','group', 'license_class', 'type', 'temporal')
		);
	}
}