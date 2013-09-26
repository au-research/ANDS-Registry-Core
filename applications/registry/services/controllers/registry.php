<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Registry Objects Services controller
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ands/services/registry
 * 
 */
class Registry extends MX_Controller {

	//formatResponse is a helper function in engine/helper/presentation_function


	/*
	 * get_registry_object
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param 	registry_object_id identifier
	 *			format: xml/json/raw/raw-xml
	 * prints out the requested rifcs of the object
	 */
	public function get_registry_object($id=null, $format='xml'){
		if($id){
			try{
				$this->load->model('registry_object/registry_objects', 'ro');
				$ro = $this->ro->getByID($id);
				$response = array();
				$response['status']='OK';
				$response['message']=$ro->getXML();
				formatResponse($response, $format);
			}catch (Exception $e){
				$response['status']='ERROR';
				$response['message']=$e->getMessage;
				formatResponse($response, $format);
			}
		}else{
			$response['status']='WARNING';
			$response['message']='Missing ID identifier for Registry Object';
			formatResponse($response, $format);
		}
	}

	/*
	 * get_vocab
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param 	vocab identifier
	 * prints out the requested json fragment for the autocomplete
	 */
	public function get_vocab($vocabIdentifier){
		$this->load->database();
		$this->db->select('vocabpath, name, identifier, description, id');
		$this->db->from('tbl_terms');
		$this->db->where(array('vocabulary_identifier'=>$vocabIdentifier));
		$query = $this->db->get();

		$vocab_results = array();
		foreach($query->result() as $row){
			$description = $row->vocabpath;
			/*if($row->description){
				$description = $row->description;
			} else{
				$description = $row->name;
			}*/
			$item = array('value'=>$row->identifier, 'subtext'=>$description);
			array_push($vocab_results, $item);
		}

		$vocab_results = json_encode($vocab_results);
		echo $vocab_results;
	}

	public function search(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$this->load->database();
		$like = $this->input->get('query');
		$this->db->select('registry_object_id, title');
		$this->db->from('registry_objects');
		$this->db->like('title', $like);
		$this->db->or_like('key', $like);
		$this->db->or_like('slug', $like);
		$this->db->limit(10);
		$query = $this->db->get();

		$vocab_results = array();
		foreach($query->result() as $row){
			$item = array('value'=>$row->title, 'id'=>$row->registry_object_id);
			array_push($vocab_results, $item);
		}

		$vocab_results = json_encode($vocab_results);
		echo $vocab_results;
	}

	public function solr_search(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->library('solr');
		$start=0;
		$start = ($this->input->get('start')?$this->input->get('start'):'0');
		$query = ($this->input->get('query')?$this->input->get('query'):'*:*');
		$fq = ($this->input->get('fq')?$this->input->get('fq'):'');
		
		$query = $this->input->get('query');
		$this->solr->setOpt('defType', 'edismax');
		$this->solr->setOpt('start', $start);
		$this->solr->setOpt('q', 'fulltext:('.$query.')');
		if($fq!='') $this->solr->setOpt('fq', $fq);
		$this->solr->executeSearch();
		$data['result'] = $this->solr->getResult();
		$data['numFound'] = $this->solr->getNumFound();
		$data['solr_header'] = $this->solr->getHeader();
		$data['fieldstrings'] = $this->solr->constructFieldString();
		$data['timeTaken'] = $data['solr_header']->{'QTime'} / 1000;
		echo json_encode($data);
	}

	public function post_solr_search(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->library('solr');
		$this->load->helper('presentation_helper');
		$filters = $this->input->post('filters');

		//check php input for filters (angularjs ajax)
		if(!$filters){
			$data = file_get_contents("php://input");
			$array = json_decode($data, true);
			$filters = $array['filters'];
		}
		if(!$filters) $filters = array();

		if($filters['include_facet']){
			$facets = array(
				'class' => 'Class',
				'group' => 'Contributed By',
				'license_class' => 'Licence',
				'type' => 'Type',
			);
			foreach($facets as $facet=>$display){
				$this->solr->setFacetOpt('field', $facet);
			}
			$this->solr->setFacetOpt('mincount','1');
			$this->solr->setFacetOpt('limit','100');
			$this->solr->setFacetOpt('sort','count');
		}

		$data = array();
		$this->solr->setFilters($filters);
		$this->solr->executeSearch();
		$data['result'] = $this->solr->getResult();
		if($filters['include_facet']) $data['facet'] = $this->solr->getFacet();
		$data['numFound'] = $this->solr->getNumFound();
		$data['solr_header'] = $this->solr->getHeader();
		$data['fieldstrings'] = $this->solr->constructFieldString();
		$data['timeTaken'] = $data['solr_header']->{'QTime'} / 1000;
		echo json_encode($data);
	}

	/*
	 * get_random_key
	 * 
	 * @author 	Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param 	length of the key
	 * prints out a random key that is unique
	 */
	public function get_random_key($length=52){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";	
		$str='';
		$size = strlen( $chars );
		for( $i = 0; $i < $length; $i++ ) {
			$str .= $chars[ rand( 0, $size - 1 ) ];
		}
		$jsonData['key'] = $str;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	/**
	 * check a key to see uniqueness
	 * @param  string $type data_source_key || registry_object_key
	 * @param  string $key  
	 * @return [total]
	 */
	public function check_unique($type){
		$this->load->database();
		$key = $this->input->post('key');
		if($type=='data_source_key'){
			$this->db->select('key');
			$this->db->from('data_sources');
			$this->db->where('key', $key);
			$total =  $this->db->count_all_results();
		}else if($type=='registry_object_key'){
			$this->db->select('key');
			$this->db->from('registry_objects');
			$this->db->where('key', $key);
			$total =  $this->db->count_all_results();
		}
		echo $total;
	}



	/**
	 * check an ro_key to see uniqueness
	 * @param  string ro_key
	 * @return [results]
	 */
	public function check_unique_ro_key(){
		$results= array();
		$this->load->database();
		$key = $this->input->get('ro_key');
		$results['ro_key'] = $key;
		// $query = $this->db->select('*')->get_where('registry_objects', array("binary key"=>$key), true);
		//case sensitive search for key
		$query = $this->db->where('binary `key` =', '"'.$key.'"', false)->get('registry_objects');
		$results['ro_list'] = $query->result_array();
		if(is_array($results['ro_list']) && count($results['ro_list'] > 0))
		{
			foreach ($results['ro_list'] AS &$item)
			{
				$item['status'] = readable($item['status'], true);
			}
		}
		$results = json_encode($results);
		echo $results;
	}




	/*
	 * get_datasources_list
	 * 
	 * @author 	Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param 	
	 * prints out the list of datasources the user has access to @TODO: needs ACL
	 */
	public function get_datasources_list(){
		//$this->output->enable_profiler(TRUE);
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';
		$this->load->model("data_source/data_sources","ds");
		$dataSources = $this->ds->getAll(0, 0);//get All

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;
			array_push($items, $item);
		}
		
		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
}	