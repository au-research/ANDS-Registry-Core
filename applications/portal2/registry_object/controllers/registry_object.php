<?php
class Registry_object extends MX_Controller {

	function view(){
		if($this->input->get('id')){
			$ro = $this->ro->getByID($this->input->get('id'));
		}

		$this->load->library('blade');

		$contents = array(
			'descriptions',
			'identifiers-list',
			'related-objects-list',
			'subjects-list'
		);

		$aside = array(
			'metadata-info',
			'suggested-datasets-list'
		);

		$this->blade
			->set('ro', $ro)
			->set('contents', $contents)
			->set('aside', $aside)
			->render('registry_object/view');
	}

	function search() {
		$this->load->library('blade');
		$this->blade->render('registry_object/search');
	}

	function s() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$filters = array('q'=>'geoscience');

		$this->load->library('solr');
		$this->solr->setFilters($filters);
		$facets['class'] = 'Class';
		$facets['group'] = 'Contributor';
		$facets['license_class'] = 'Licence';
		$facets['type'] = 'Type';

		foreach($facets as $facet=>$display){
			// $this->solr->setFacetOpt('field', $facet);
		}
		$this->solr->setOpt('fl', 'id,title');
		$this->solr->setOpt('hl', 'true');
		$this->solr->setOpt('hl.fl', '*');

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
	}
}