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
				$response['message']=$e->getMessage();
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
		$this->db->or_like('registry_object_id', $like);
		$this->db->or_like('key', $like);
		$this->db->or_like('slug', $like);
		$this->db->limit(10);
		$query = $this->db->get();

		$registry_object_results = array();
		foreach($query->result() as $row){
			$item = array('value'=>$row->title, 'link'=>base_url('registry_object/view/'.$row->registry_object_id));
			array_push($registry_object_results, $item);
		}

		$query = $this->db->select('title, data_source_id')->from('data_sources')->like('title', $like)->or_like('key', $like)->or_like('data_source_id', $like)->limit(10)->get();
		$data_source_results = array();
		foreach($query->result() as $row){
			$item = array('value'=>$row->title, 'link'=>base_url('data_source/manage#!/view/'.$row->data_source_id));
			array_push($data_source_results, $item);
		}

		$roles_db = $this->load->database('roles', TRUE);
		$query = $roles_db->select('name, role_id')->from('roles')->like('role_id', $like)->or_like('name', $like)->limit(10)->get();
		$roles_results = array();
		foreach($query->result() as $row){
			$item = array('value'=>$row->name, 'link'=>roles_url('#/view/'.rawurlencode($row->role_id)));
			array_push($roles_results, $item);
		}

		$result['ro'] = $registry_object_results;
		if ($this->user->hasFunction('REGISTRY_SUPERUSER')) $result['ds'] = $data_source_results;
		if ($this->user->hasFunction('REGISTRY_SUPERUSER')) $result['roles'] = $roles_results;

		if(sizeof($result['ro']) > 0 || sizeof($result['ds']) > 0 || sizeof($result['roles']) > 0) {
			$result['has_result'] = true;
		}

		$result = json_encode($result);
		echo $result;
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
		set_exception_handler('json_exception_handler');
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

		$filters['include_facet'] = (isset($filters['include_facet']) ? $filters['include_facet'] : false);
		$filters['include_facet_tags'] = (isset($filters['include_facet_tags']) ? $filters['include_facet_tags'] : false);

		if($filters['include_facet']){
			$facets = array(
				'class' => 'Class',
				'group' => 'Contributed By',
				'license_class' => 'Licence',
				'type' => 'Type',
				'tag' => 'Tag'
			);
			foreach($facets as $facet=>$display){
				$this->solr->setFacetOpt('field', $facet);
			}
			$this->solr->setFacetOpt('mincount','1');
			$this->solr->setFacetOpt('limit','100');
			$this->solr->setFacetOpt('sort','count');
		}

		if($filters['include_facet_tags']){
			$this->solr->setOpt('sort', 'update_timestamp desc');
			$this->solr->setFacetOpt('query', '{!ex=dt key="hasTag"}tag:*');
			$this->solr->setFacetOpt('mincount','1');
			$this->solr->setFacetOpt('limit','100');
			$this->solr->setFacetOpt('sort','count');
		}
		if(isset($filters['facet.sort'])) $this->solr->setFacetOpt('sort',$filters['facet.sort']);
		$data = array();
		$this->solr->setFilters($filters);
		$this->solr->addBoostCondition('(tag:*)^1000');
		$this->solr->executeSearch();
		$data['result'] = $this->solr->getResult();
		if($filters['include_facet'] || $filters['include_facet_tags']) $data['facet'] = $this->solr->getFacet();
		$data['numFound'] = $this->solr->getNumFound();
		$data['solr_header'] = $this->solr->getHeader();
		$data['fieldstrings'] = rawurldecode($this->solr->constructFieldString());
		$data['timeTaken'] = $data['solr_header']->{'QTime'} / 1000;
		echo json_encode($data);
	}

	public function tags($source, $action=''){
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->library('solr');
		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->model('registry_object/registry_object_tags', 'tags');

		$keys = array();
		if($source=='solr'){
			$filters = $this->input->post('filters');
			$tag = $this->input->post('tag');
			$tag_type = $this->input->post('tag_type');
			if(!$filters){
				$data = file_get_contents("php://input");
				$array = json_decode($data, true);
				$filters = $array['filters'];
				if(isset($array['tag'])) $tag = $array['tag'];
				if(isset($array['tag_type'])) $tag_type = $array['tag_type'];
				if($action=='remove') $filters['tag'] = $tag;
			}
			$this->solr->setFilters($filters);
			$this->solr->addBoostCondition('(tag:*)^1000');
			$this->solr->setOpt('rows', '2000');
			$this->solr->executeSearch();
			$result = $this->solr->getResult();
			foreach($result->{'docs'} as $d) array_push($keys, $d->{'key'});
		}elseif($source == 'keys'){
			$keys = $this->input->post('keys');
			$tag = $this->input->post('tag');
			$tag_type = $this->input->post('tag_type');
			if(!$keys){
				$data = file_get_contents("php://input");
				$array = json_decode($data, true);
				$keys = $array['keys'];
				if(isset($array['tag'])) $tag = $array['tag'];
				if(isset($array['tag_type'])) $tag_type = $array['tag_type'];
			}
		}

		if($action=='get'){
			$tags = $this->tags->getTagsByKeys($keys);
			echo json_encode($tags);
		}else{
			foreach($keys as $key){
				$ro = $this->ro->getPublishedByKey($key);
				if(!$ro) $ro = $this->ro->getDraftByKey($key);
				if(!$ro) throw new Exception("Can't find record with the key: ". $key);

				if($action=='add' && $tag){
					if($ro->preCheckTag($tag, $tag_type)){
						$ro->addTag($tag, $tag_type);
					}else{
						throw new Exception($tag. ' already exists as a '.$ro->getTagType($tag).' tag. Please try a different tag.');
					}
				}

				if($action=='remove' && $tag) {
					$ro->removeTag($tag);
				}
				unset($ro);
			}
			if($action=='add') $this->tags->batchIndexAddTag($keys, $tag, $tag_type);
			if($action=='remove') $this->ro->batchIndexKeys($keys);
			echo json_encode($keys);
		}
	}

	public function tags_status(){
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$tags = $this->input->post('tags');
		$data = file_get_contents("php://input");
		$array = json_decode($data, true);
		if(isset($array['tags'])) $tags = $array['tags'];
		$result = array();
		
		foreach($tags['data'] as $tag){
			$row = $this->db->get_where('tags', array('name'=>$tag['name']));
			$row = $row->first_row();
			if($row){
				array_push($result, array(
					'name' => $tag['name'],
					'type' => $row->type
				));
			}
		}
		
		echo json_encode(array('status'=>'OK', 'content'=>$result));
	}

	public function suggest($what='', $q=''){
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->library('solr');
		$items = array();
		$accepted_facet = array('class', 'type', 'group', 'originating_source', 'subject_value_resolved');
		if(in_array($what, $accepted_facet)){
			$this->solr->setFacetOpt('field', $what);
			$this->solr->setFacetOpt('sort','index');
			$this->solr->executeSearch();
			$tags = $this->solr->getFacet($what);
			$tags = $tags->{'facet_fields'}->{$what};
			for($i=0;$i<sizeof($tags);$i+=2){
				if($q!=''){
					if(stristr($tags[$i], $q)) array_push($items, array('value'=>$tags[$i], 'label'=>$tags[$i]));
				}else{
					array_push($items, array('value'=>$tags[$i], 'label'=>$tags[$i]));
				}
			}
		}else if($what=='data_source_key'){
			$this->load->model("data_source/data_sources","ds");
			$dataSources = $this->ds->getOwnedDataSources();
			foreach($dataSources as $ds){
				if($q!=''){
					if(stristr($ds->title, $q)) array_push($items, array('value'=>$ds->key, 'label'=>$ds->title));
				}else{
					array_push($items, array('value'=>$ds->key, 'label'=>$ds->title));
				}
			}
		}else if($what=='tag'){
			$this->load->library('vocab');
			$matches = $this->vocab->anyContains($q, 'anzsrc-for');
			foreach($matches as $match){
				array_push($items, array('value'=>$match, 'label'=>$match));
			}
			$matches = $this->vocab->anyContains($q, 'anzsrc-seo');
			foreach($matches as $match){
				array_push($items, array('value'=>$match, 'label'=>$match));
			}

			$this->db->select('name')->like('name', $q);
			$matches = $this->db->get_where('tags', array('type'=>'public'));
			foreach($matches->result() as $match){
				array_push($items, array('value'=>$match, 'label'=>$match));
			}

		}
		echo json_encode($items);
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
			foreach ($results['ro_list'] AS &$item){
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
		// header('Cache-Control: no-cache, must-revalidate');
		// header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';
		$this->load->model("data_source/data_sources","ds");
		$dataSources = $this->ds->getAll(0, 0);//get All

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;
			$item['key'] = $ds->key;
			array_push($items, $item);
		}
		
		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
}	