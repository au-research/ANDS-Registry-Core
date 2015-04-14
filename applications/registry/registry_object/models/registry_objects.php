<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Data Sources model
 *
 * XXX:
 *
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/registryobject
 *
 */

class Registry_objects extends CI_Model {

	public $valid_classes = array("collection","activity","party","service");
	public $valid_status  = array("MORE_WORK_REQUIRED"=>"MORE_WORK_REQUIRED", "DRAFT"=>"DRAFT", "SUBMITTED_FOR_ASSESSMENT"=>"SUBMITTED_FOR_ASSESSMENT", "ASSESSMENT_IN_PROGRESS"=>"ASSESSMENT_IN_PROGRESS", "APPROVED"=>"APPROVED", "PUBLISHED"=>"PUBLISHED");
	public $valid_levels  = array("level_1"=>"1", "level_2"=>"2", "level_3"=>"3", "level_4"=>"4" );
	
	static $status_colors = array(
		"MORE_WORK_REQUIRED"=>"#6A4A3C", 
		"DRAFT"=>"#c60", 
		"SUBMITTED_FOR_ASSESSMENT"=>"#688EDE", 
		"ASSESSMENT_IN_PROGRESS"=>"#0B2E59", 
		"APPROVED"=>"#EDD155", 
		"PUBLISHED"=>"#32CD32"
	);

	static $classes = array("collection"=>"Collection", "party"=>"Party", "service"=>"Service", "activity"=>"Activity");
	static $statuses  = array(
		"MORE_WORK_REQUIRED"=>"More Work Required", 
		"DRAFT"=>"Draft", 
		"SUBMITTED_FOR_ASSESSMENT"=>"Submitted for Assessment", 
		"ASSESSMENT_IN_PROGRESS"=>"Assessment in Progress", 
		"APPROVED"=>"Approved", 
		"PUBLISHED"=>"Published"
	);
	static $quality_levels = array(
		"1" => "Quality Level 1",
		"2" => "Quality Level 2",
		"3" => "Quality Level 3",
		"4" => "Gold Standard Record"
	);
	


	/**
	 * Generic registry_objects get handler.
	 *
	 * This moderately nifty piece of code lets you get some `_registry_object`
	 * goodness by any means necessary. Just add wat^H^H^H the following:
	 *   - a list of callback functions to apply to this (the `Registry_objects`) model
	 *   - whether you want `_registry_object`s or plain old `registry_object_id`s (i.e. ints)
	 *   - number of records to limit the reponse to (optional)
	 *   - offset from which to retrieve record set (optional)
	 *
	 * The callback pipeline expects an array of arrays. Crazy, I know. I blame PHP for not
	 * having a tuple/list/set datatype, or even a plain hash. That's right: I'm blaming my tools.
	 * Anyway, `$pipeline` should look something like:
	 *
	 *
	 * array(                                 //a list of callbacks to apply
	 *   array(                               //the first callback
	 *     'args' => ...,                     //arguments to pass to callback. this will be the second parameter. stuff with array as required
	 *     'fn' => function($db,$args){...}   //callback. takes a CodeIgniter db object, which should be returned by the function.
	 *  )
	 * )
	 *
	 *
	 * @param an array of processing callbacks (type `callable`). See above description
	 * for specific details.
	 * @param should we create `_registry_object` objects for each result? (default: true)
	 * @param query limit (int) (default: false; i.e. no limit)
	 * @param query offset (int) (default: false; i.e. no offset)
	 * @return an array of results, or null if no results.
	 */
	public function _get($pipeline, $make_ro=true, $limit=false, $offset=false)
	{
		if (!is_array($pipeline))
		{
			throw new Exception("pipeline must be an array");
		}

		foreach ($pipeline as $p)
		{
			if (!is_callable($p['fn']))
			{
				throw new Exception("pipeline members must be callable");
			}
		}

		$CI =& get_instance();
		$db =& $CI->db;

		foreach ($pipeline as $p)
		{
		    //do we need to catch the return value, seeing as we're passing db by reference? don't think so....
		    call_user_func_array($p['fn'], array(&$db, &$p['args']));
		    //$db = call_user_func($p['fn'], $db, $p['args']);
		}
		$results = null;
		$query = false;
		if ($limit && $offset)
		{
			$query = $db->get(null, $limit, $offset);
		}
		elseif ($limit)
		{
			$query = $db->get(null, $limit);
		}
		elseif ($offset)
		{
			$query = $db->get(null, null, $offset);
		}
		else
		{
			$query = $db->get();
		}
		if ($query && $query->num_rows() > 0)
		{
			$results = array();
			foreach ($query->result_array() as $rec)
			{
				if ($make_ro)
				{
					$cached_object_reference = RegistryObjectReferenceCache::getById($rec["registry_object_id"]);
					if ($cached_object_reference)
					{
						$results[] = $cached_object_reference;
					}
					else
					{
						$results[] = RegistryObjectReferenceCache::fetchCacheReference(new _registry_object($rec["registry_object_id"]));
					}
				}
				else
				{
					$results[] = $rec;
				}
			}
		}
		if ($query)
		{
			$query->free_result();
		}
		return $results;
	}

	/**
	 * Returns exactly one PUBLISHED registry object by Key (or NULL)
	 *
	 * @param the registry object key
	 * @return _registry_object object or NULL
	 */
	function getPublishedByKey($key)
	{
		$results =  $this->_get(array(array('args' => $key,
						    'fn' => function($db,$key) {
							    $db->select("registry_object_id")
								    ->from("registry_objects")
								    ->where('`key` =', '"'.$key.'"', false)
								    ->where("status", PUBLISHED);
							    return $db;
						    })),
							true,
							1);
		return is_array($results) ? $results[0] : null;
	}

	/**
	 * Returns exactly one DRAFT (or draft-equivalent) registry object by Key (or NULL)
	 *
	 * @param the registry object key
	 * @return _registry_object object or NULL
	 */
	function getDraftByKey($key)
	{
		$results =  $this->_get(array(array('args' => $key,
						    'fn' => function($db,$key) {
							    $db->select("registry_object_id")
								    ->from("registry_objects")
								    ->where('`key` =', '"'.$key.'"', false)
								    ->where_in("status", getDraftStatusGroup());
							    return $db;
						    })),
							true,
							1);
		return is_array($results) ? $results[0] : null;
	}

	/**
	 * Returns all registry objects with a given key (or NULL)
	 *
	 * @param the registry object key
	 * @return array(_registry_object) or NULL
	 */
	function getAllByKey($key)
	{
		$results =  $this->_get(array(array('args' => $key,
						    'fn' => function($db,$key) {
							    $db->select("registry_object_id")
								    ->from("registry_objects")
								    ->where('`key` =', '"'.$key.'"', false);
							    return $db;
						    })),
							true
							);
		return is_array($results) ? $results : null;
	}


	/**
	 * Returns exactly one registry object by Key (or NULL)
	 *
	 * @param the registry object key
	 * @return _registry_object object or NULL
	 */
	function getByID($id)
	{
		// Reduce number of DB calls by avoiding the pipeline
		// trying to determine the ID (when it was explicitly specified)
		try 
		{
			$cached_object_reference = RegistryObjectReferenceCache::getById($id);
			if ($cached_object_reference)
			{
				return $cached_object_reference;
			}
			else
			{
				return RegistryObjectReferenceCache::fetchCacheReference(new _registry_object($id));
			}
		}
		catch (Exception $e)
		{
			return null;
		}

		/*
		$results = $this->_get(array(array('args' => $id,
						   'fn' => function($db,$id) {
							   $db->select("registry_object_id")
								   ->from("registry_objects")
								   ->where("registry_object_id", $id);
							   return $db;
						   })),
				       true,
				       1);
		return is_array($results) ? $results[0] : null;
		*/
	}


	/**
	 * Returns exactly one registry object by URL slug (or NULL)
	 *
	 * @param the registry object slug
	 * @param the status of the registry object we want 
	 * @return _registry_object object or NULL
	 */
	function getBySlug($slug, $status = "PUBLISHED")
	{
		$results = $this->_get(array(array('args' => $slug,
						   'fn' => function($db,$slug) {
							   $db->select("registry_object_id")
								   ->from("registry_objects")
								   ->where("status", "PUBLISHED")
								   ->where("slug", $slug);
							   return $db;
						   })),
				       true,
				       1);
		return is_array($results) ? $results[0] : null;
	}

	/**
	 * Returns exactly one registry object by URL slug (or NULL)
	 *
	 * @param the registry object identifier
	 * @param the type of the registry object's identifier
	 * @return _registry_object id
	 */
	function getByIdentifier($identifier, $type)
	{
		$query = $this->db->get_where('registry_object_identifiers', array("identifier"=>$identifier, "identifier_type"=>$type));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->result_array();
		}
	}

	function getByRelatedInfoIdentifier($identifier){
		$query = $this->db->get_where('registry_object_identifier_relationships', array('related_object_identifier'=>$identifier));
		if ($query->num_rows()) {
			return NULL;
		} else {
			return $query->result_array();
		}
	}

	/**
	 * Get a number of registry_objects that match the attribute requirement (or an empty array)
	 *
	 * @param the name of the attribute to match by
	 * @param the value that the attribute must match
	 * @return array(_registry_object)
	 */
	function getByAttribute($attribute_name, $value, $core=false, $make_ro=true)
	{
		$args = array('name' => $attribute_name, 'val' => $value);
		return $core == true ?
			$this->_get(array(array('args' => $args,
						'fn' => function($db,$args) {
							$db->select("registry_object_id")
								->from("registry_objects")
								->where($args['name'], $args['val']);
							return $db;
						})), $make_ro)
			:
			$this->_get(array(array('args' => $args,
						'fn' => function($db,$args) {
							$db->select("registry_object_id")
								->from("registry_object_attributes")
								->where("attribute", $args['name'])
								->where("value", $args['val']);
							return $db;
						})), $make_ro)
			;
	}

	function getByAttributeDatasource($data_source_id, $attribute_name, $value, $core=false, $make_ro=true)
	{
		$args = array('name' => $attribute_name, 'val' => $value, 'data_source_id'=>$data_source_id);
		return $core == true ?
			$this->_get(array(array('args' => $args,
						'fn' => function($db,$args) {
							$db->select("registry_object_id")
								->from("registry_objects")
								->where('data_source_id', $args['data_source_id'])
								->where($args['name'], $args['val']);

							return $db;
						})), $make_ro)
			:
			$this->_get(array(array('args' => $args,
						'fn' => function($db,$args) {
							$db->select("registry_objects.registry_object_id")
								->from("registry_object_attributes")
								->join('registry_objects', 'registry_objects.registry_object_id = registry_object_attributes.registry_object_id', 'right')
								->where('data_source_id', $args['data_source_id']);

							if($args['name']=='tag'){
								$db->where("attribute", "tag")->where("value !=", "");
							}else{
								$db->where("attribute", $args['name'])->where("value", $args['val']);
							}
							return $db;
						})), $make_ro)
			;
	}


	/**
	 * Get a number of registry_objects that match the attribute requirement (or an empty array).
	 * Note that by default, this method returns registry_object_id's only. If you want
	 * `_registry_object`s, pass an additional boolean `true` for the second parameter
	 *
	 * @param the data source ID to match by
	 * @param boolean flag indicating whether to return an array of IDs (int), or an
	 * array of `_registry_object`s.
	 * @return array of results, or null if no matching records
	 */
	function getIDsByDataSourceID($data_source_id, $make_ro=false, $status='All', $offset=0, $limit=99999)
	{
		$results =  $this->_get(array(array('args' => array('ds_id'=>$data_source_id, 'status'=>$status, 'offset'=>$offset, 'limit'=>$limit),
						    'fn' => function($db, $args) {
							    $db->select("registry_object_id")
								    ->from("registry_objects")
								    ->where("data_source_id", $args['ds_id']);
								if($args['status']!='All') $db->where('status', $args['status']);
								$db->limit($args['limit'], $args['offset']);
							    return $db;
						    })),
					$make_ro);
		if(is_array($results))
			return $make_ro ? $results : array_map(function($r){return $r['registry_object_id'];}, $results);
		else
			return null;
	}

	function getKeysByDataSourceID($data_source_id, $make_ro=false, $status='All', $offset=0, $limit=99999)
	{
		$results =  $this->_get(array(array('args' => array('ds_id'=>$data_source_id, 'status'=>$status, 'offset'=>$offset, 'limit'=>$limit),
						    'fn' => function($db, $args) {
							    $db->select("key")
								    ->from("registry_objects")
								    ->where("data_source_id", $args['ds_id']);
								if($args['status']!='All') $db->where('status', $args['status']);
								$db->limit($args['limit'], $args['offset']);
							    return $db;
						    })),
					$make_ro);
		if(is_array($results))
			return $make_ro ? $results : array_map(function($r){return $r['key'];}, $results);
		else
			return null;
	}

	function getOldHarvestedRecordIDsByDataSourceID($data_source_id, $harvest_id, $make_ro=false)
	{
		$results =  $this->_get(array(array('args' => array('data_source_id'=>$data_source_id, 'harvest_id'=>$harvest_id),
						    'fn' => function($db, $args) {
							$db->select("registry_objects.registry_object_id")
								->from("registry_object_attributes")
								->join('registry_objects', 'registry_objects.registry_object_id = registry_object_attributes.registry_object_id', 'right')
								->where('data_source_id', $args['data_source_id'])
								->where("attribute", "harvest_id")
								->where("value !=", "")
								->where("value !=", $args['harvest_id'])
								->not_like('value', 'MANUAL-');
							return $db;
						    })),
					$make_ro);
		if(is_array($results))
			return $make_ro ? $results : array_map(function($r){return $r['registry_object_id'];}, $results);
		else
			return null;
	}




	/**
	 * Get a number of registry_objects that match the attribute requirement (or an empty array)
	 *
	 * @param the data source ID to match by
	 * @deprecated USE getIDsByDataSourceID() instead
	 * @return array(_registry_object)
	 */
	function getByDataSourceKey($data_source_key)
	{
		return $this->_get(array(array('args' => $data_source_key,
					       'fn' => function($db, $dsk) {
						       $db->select("registry_object_id")
							       ->from("registry_objects")
							       ->join("data_sources",
								      "data_sources.data_source_id = registry_objects.data_source_id")
							       ->where("data_sources.key", $dsk);
						       return $db;
					       })));
	}

	function getAll($limit=10, $offset=0, $args=null)
	{
		return $this->_get(array(array('args' => array(
									'search'=>$args['search'] ? $args['search'] : false,
									'sort'=>$args['sort'],
									'filter'=>$args['filter']
								),
					       'fn' => function($db, $args) {
						       $db->select("registry_object_id")
							       ->from("registry_objects");
							   	if($args['search']) {
							   		$db->like('title',$args['search'],'both');
							   		$db->or_like('key', $args['search'],'both');
							   	}
						   		if($args['sort']){
						   			foreach($args['sort'] as $sort){
						   				foreach($sort as $key=>$value){
						   					$db->order_by($key, $value);
						   				}
						   			}
						   		}
						   		if($args['filter']){
						   			foreach($args['filter'] as $key=>$value){
						   				$db->where($key,$value);
						   			}
						   		}
						       return $db;
					       })),true, $limit, $offset);
	}

	function getByAttributeSQL($key, $value, $data_source_id = ""){
		$CI =& get_instance();
		if($value=='!='){
			$result = $CI->db->select('ra.registry_object_id')->from('registry_object_attributes ra')->where('attribute', $key)->where('value !=', $value);
		}else $result = $CI->db->select('ra.registry_object_id')->from('registry_object_attributes ra')->where('attribute', $key)->where('value', $value);

		if ($data_source_id){
			$result->join('registry_objects r','ra.registry_object_id = r.registry_object_id')->where('data_source_id', $data_source_id);
		}

		$result = $result->get();

		
		$res = array();
		foreach($result->result() as $r){
			array_push($res, array('registry_object_id'=>$r->registry_object_id));
		}
		return $res;
	}

	function getBySQL($key, $value, $limit=10) {
		$CI =& get_instance();
		$result = $CI->db->select('registry_object_id')->from('registry_objects')->where($key, $value)->limit(1000);
		$result = $result->get();
		$res = array();
		foreach($result->result() as $r){
			array_push($res, array('registry_object_id'=>$r->registry_object_id));
		}
		return $res;
	}

	function filter_by($args, $limit=10, $offset=0, $make_ro=true){
		$white_list = array('title', 'class', 'key', 'status', 'slug', 'record_owner');
		$filtered = array();
		$filtering = false;
		$ff = false;
		if($args['filter']){
			foreach($args['filter'] as $key=>$value){
				if(in_array($key, $white_list) && array_key_exists('data_source_id', $args)){
					$ff = $this->getByAttributeDatasource($args['data_source_id'], $key, $value, false, false);
				} elseif (in_array($key, $white_list)) {
					$ff = $this->getBySQL($key, $value, $limit);
					$filtering = true;
				} else {
					$data_source_id = (isset($args['data_source_id']) ? $args['data_source_id']: false);
					$ff = $this->getByAttributeSQL($key, $value, $data_source_id);
					$filtering = true;
				}

				if($ff && is_array($ff)){
					foreach($ff as $f){
						if(!in_array($f['registry_object_id'], $filtered)){
							array_push($filtered, $f['registry_object_id']);
						}
					}
				}
			}
		}
		$where_in = $filtered;

		if($filtering && sizeof($where_in)==0) return array();
		// $where_in = array();
		// if($filtered){
		// 	foreach($filtered as $f){
		// 		array_push($where_in, $f['registry_object_id']);
		// 	}
		// }
		return $this->_get(array(array('args' => array(
									'data_source_id'=>isset($args['data_source_id']) ? $args['data_source_id'] : false,
									'search'=>isset($args['search']) ? $args['search'] : false,
									'sort'=>isset($args['sort']) ? $args['sort'] : false,
									'filter'=>isset($args['filter']) ? $args['filter'] : false,
									'where_in'=>isset($where_in) ? $where_in : false
								),
					       'fn' => function($db, $args) {
						       	$db->select("registry_objects.registry_object_id")->from("registry_objects");
						       	if($args['data_source_id']){
						       		$db->where('data_source_id', $args['data_source_id']);
						       	}

							   	if($args['search']) {
							   		$args['search'] = $db->escape_like_str($args['search']);
							   		$db->where('(`title` LIKE \'%'.$args['search'].'%\' || `key` LIKE \'%'.$args['search'].'%\' || `registry_objects`.`registry_object_id` LIKE \'%'.$args['search'].'%\')');
							   		//$db->like('title', $args['search']);
							   		//$db->or_like('key', $args['search']);
							   	}

							   	$white_list = array('title', 'class', 'key', 'status', 'slug', 'record_owner');
						   		if($args['sort']){
						   			foreach($args['sort'] as $key=>$value){
						   				$db->join('registry_object_attributes', 'registry_objects.registry_object_id = registry_object_attributes.registry_object_id', 'left');
						   				$db->select('registry_object_attributes.value as v');
						   				$db->where('registry_object_attributes.attribute', $key);
						   				if(in_array($key, $white_list)){
						   					$db->order_by($key, $value);
						   				}else{
						   					$db->order_by('v', $value);
						   				}
						   			}
						   		}

						   		if($args['filter']){
						   			foreach($args['filter'] as $key=>$value){
						   				if(in_array($key, $white_list)){
						   					$db->where($key,$value);
						   				}
						   			}
						   		}
						   		if($args['where_in']){
						   			if(sizeof($args['where_in'])>0){
						   				$db->where_in('registry_objects.registry_object_id',$args['where_in']);
						   			}else return false;
						   		}
						       return $db;
					       })),$make_ro, $limit, $offset);
	}

	function getUnEnriched(){
		$CI =& get_instance();

		$result = $CI->db->select('r.registry_object_id')->from('registry_objects r')
		->join('record_data d', "d.registry_object_id = r.registry_object_id AND scheme='extrif'", 'left')->where('data IS NULL');
		return $result->get();
		/* use LEFT JOIN to palm off this overhead to the DB instead of two queries and a massive WHERE_NOT_IN call!!

		$enrichedResult = $CI->db->select('registry_object_id')->from('record_data')->where('scheme', 'extrif')->get();
		$enriched = array();
		foreach($enrichedResult->result() as $e){
			array_push($enriched, $e->registry_object_id);
		}
		$result = $CI->db->select('registry_object_id')->from('record_data')->where_not_in('registry_object_id', $enriched);
		return $result->get();
		*/
	}

	function getGroupSuggestor($data_source_ids){
		$CI =& get_instance();
		$result = $CI->db->select('distinct(value)')->from('registry_object_attributes ra')->where('attribute', 'group');
		$result->join('registry_objects r','ra.registry_object_id = r.registry_object_id')->where_in('data_source_id', $data_source_ids);
		return $result->get();
	}

	/**
	 * Get a number of registry_objects that match the class requirement (or an empty array)
	 *
	 * @param the value that the class must match
	 * @return array(_registry_object)
	 */
	function getByClass($class)
	{
		return $this->_get(array(array('args' => $class,
					       'fn' => function($db, $class) {
						       $db->select("registry_object_id")
							       ->from("registry_objects")
							       ->where("class", $class);
						       return $db;
					       })));
	}


	/**
	 * XXX:
	 * @return array(_data_source) or NULL
	 */
	function create(_data_source $data_source, $registry_object_key, $class, $title, $status, $slug, $record_owner, $harvestID)
	{
		$ro = new _registry_object();
		$ro->_initAttribute("data_source_id", $data_source->getAttribute('data_source_id'), TRUE);


		$ro->_initAttribute("key",$registry_object_key, TRUE);
		$ro->_initAttribute("class",$class, TRUE);
		$ro->_initAttribute("title",$title, TRUE);
		$ro->_initAttribute("status",$status, TRUE);
		$ro->_initAttribute("slug",$slug, TRUE);
		$ro->_initAttribute("record_owner",$record_owner, TRUE);
		$ro->create();

		// Some extras
		$ro->setAttribute("created",time());
		$ro->setAttribute("harvest_id", $harvestID);
		$ro->save();

		return $ro;
	}

	/**
	 * XXX:
	 * @return array(_data_source) or NULL
	 */
	function update($registry_object_key, $class, $title, $status, $slug, $record_owner)
	{
		$ro = $this->getByKey($registry_object_key);
		if (!is_null($ro))
		{

			$ro->setAttribute("class",$class);
			$ro->setAttribute("title",$title);
			$ro->setAttribute("status",$status);
			$ro->setAttribute("slug",$slug);
			$ro->setAttribute("record_owner",$record_owner);

			$ro->save();
			return $ro;
		}
		else
		{
			throw new Exception ("Unable to update registry object (this registry object key does not exist in the registry)");
		}
	}

	/**
	 * XXX:
	 */	
	function emailAssessor($data_source){		
		$to = $data_source->getAttribute('assessment_notify_email_addr');	
		if($to)
		{
			$subject = "Records from ".$data_source->title." are ready for your assessment";
			$message = $data_source->title." has submitted records for your assessment. You can access the records on the Manage Records page within the registry";
			$headers = 'From: "ANDS Services - Automated Email" <services@ands.org.au>' . "\r\n" .
	    	'Reply-To: "ANDS Services" <services@ands.org.au>' . "\r\n" .
	    	'X-Mailer: PHP/' . phpversion();
			mail ($to ,$subject ,$message, $headers);
		}				
	}	

	/**
	  * XXX: 
	  */ 
	function cloneToDraft($registry_object)
	{
		if (!($registry_object instanceof _registry_object))
		{
			// Then this is a registry object ID
			$registry_object = $this->getByID($registry_object);
		}
		if (!$registry_object) { throw new Exception ("Could not load registry object to create draft."); }

		// Add the XML content of this draft to the published record (and follow enrichment process, etc.)
		
		$this->load->model('data_source/data_sources', 'ds');
		$this->importer->_reset();
		$this->importer->setXML(wrapRegistryObjects(html_entity_decode($registry_object->getRif())));
		//echo $registry_object->getRif();
		$this->importer->setDatasource($this->ds->getByID($registry_object->data_source_id));
		$this->importer->forceDraft();
		$this->importer->commit();

		if ($error_log = $this->importer->getErrors())
		{
			throw new Exception("Errors occured whilst cloning the record to DRAFT status: " . NL . $error_log);
		}

		return $this->getDraftByKey($registry_object->key);
	}


    function erase($id)
    {
        $log ='';
        $CI =& get_instance();
        $CI->db->delete('registry_object_relationships', array('registry_object_id'=>$id));
        $CI->db->delete('registry_object_identifier_relationships', array('registry_object_id'=>$id));
        $CI->db->delete('registry_object_identifiers', array('registry_object_id'=>$id));
        //if($error = $this->db->_error_message())
        //$log = NL."registry_object_relationships: " .$error;
        $CI->db->delete('registry_object_metadata', array('registry_object_id'=>$id));
        //if($error = $this->db->_error_message())
        //$log .= NL."registry_object_metadata: " .$error;
        $CI->db->delete('registry_object_attributes', array('registry_object_id'=>$id));
        //if($error = $this->db->_error_message())
        //$log .= NL."registry_object_attributes: " .$error;
        $CI->db->delete('record_data', array('registry_object_id'=>$id));
        //if($error = $this->db->_error_message())
        //$log .= NL."record_data: " .$error;
        $CI->db->delete('url_mappings', array('registry_object_id'=>$id));
        //if($error = $this->db->_error_message())
        //$log .= NL."url_mappings: " .$error;
        $CI->db->delete('registry_object_links', array('registry_object_id'=>$id));
        //$this->db->delete('spatial_extents', array('registry_object_id'=>$this->id));
        //$log .= NL."spatial_extents: " .$this->db->_error_message();
        $CI->db->delete('registry_objects', array('registry_object_id'=>$id));
        //if($error = $this->db->_error_message())
        //$log .= NL."registry_objects: " .$error;
        return $log;
    }

	public function deleteRegistryObjects($target_ro_ids, $finalise = true)
	{
		
		$this->load->library('Solr');
		$this->solr->deleteByIDsCondition($target_ro_ids);
		$deleted_record_keys = array();
		$affected_record_keys = array();

		foreach($target_ro_ids AS $target_ro_id)
		{
			try{
				$deletedRegObject = $this->getByID($target_ro_id);
				$deleted_record_keys[] = $deletedRegObject->key;
				$affected_record_keys = array_unique(array_merge($affected_record_keys, $this->deleteRegistryObject($target_ro_id, false)));
			}
			catch(Exception $e)
			{
				throw new Exception("ERROR REMOVING RECORD: " .$target_ro_id.NL.$e);
			}
		}
		if($finalise)
		{
			// And then their related records get reindexed...
			$this->importer->_enrichRecords($affected_record_keys);
			$this->importer->_reindexRecords($affected_record_keys);
		}


		return array('deleted_record_keys'=>$deleted_record_keys, 'affected_record_keys'=>$affected_record_keys);
	}

	public function getAllThemePages() {
		$themes = array();
		$CI =& get_instance();
		$query = $CI->db->get('theme_pages');
		if($query && $query->num_rows() > 0) {
			return $query->result_array();
		}else return array();
	}

	/**
	 * batch enrich and index a set of keys, required in multiple places, put in for tag deletion
	 * @param  [array] $keys [list of registry object keys to enrich and index]
	 * @return [void]       
	 */
	public function batchIndexKeys($keys){
		$_CI =& get_instance();
		$solr_docs = array();
		$chunkSize = 400;
		$arraySize = sizeof($keys);
		for($i=0;$i<$arraySize; $i++){
			$key = $keys[$i];
			$ro = $this->getPublishedByKey($key);
			if($ro){
				$ro->enrich();
				$solr_docs[] = $ro->indexable_json();
				if(($i % $chunkSize == 0 && $i != 0) || $i == ($arraySize -1)){
					$_CI->solr->add_json(json_encode($solr_docs));
					$_CI->solr->commit();
					$solr_docs = array();
				}
			}
			unset($ro);
		}
	}

	/**
	 * Deletes a RegistryObject 
	 *
	 * @param the registry object key
	 * @return TRUE if delete was successful
	 */
	public function deleteRegistryObject($target_ro, $finalise = true)
	{
		$reenrich_queue = array();

		// Check target_ro
		if (!$target_ro instanceof _registry_object)
		{
			$target_ro = $this->getByID($target_ro);
			if (!$target_ro)
			{
				throw new Exception("Registry Object targeted for delete does not exist?");
			}
		}
		if($finalise)
		{
			//delete index
			$this->load->library('Solr');
			$this->solr->deleteByQueryCondition('id:'.$target_ro->id);
		}

		if (isPublishedStatus($target_ro->status))
		{
			
			$this->load->model('data_source/data_sources', 'ds');
			$data_source = $this->ds->getByID($target_ro->data_source_id);
			
			// Handle URL backup
			



			$this->db->where('registry_object_id', $target_ro->id);
			$this->db->update('url_mappings', array(	"registry_object_id"=>NULL, 
														"search_title"=>$target_ro->title, 
														"updated"=>time()
													));

			//remore previous records from the deleted_registry_objects table
			
			$this->db->delete('deleted_registry_objects', array('key'=>$target_ro->key));
			
			// Add to deleted_records table


			$this->db->set(array(
								'data_source_id'=>$target_ro->data_source_id,
								'key'=>$target_ro->key,
								'deleted'=>time(),
								'title'=>$target_ro->title,
								'class'=>$target_ro->class,
								'group'=> str_replace(" ", "0x20", $target_ro->group),
								'datasource'=> str_replace(" ", "0x20", $data_source->slug),
								'record_data'=>$target_ro->getRif(),
							));
			$this->db->insert('deleted_registry_objects');

			// Re-enrich and reindex related
			$reenrich_queue = $target_ro->getRelatedKeys();
			if($finalise)
			{
			// Delete from the index
				$result = json_decode($this->solr->deleteByQueryCondition("id:(\"".$target_ro->id."\")"));

				if($result->responseHeader->status != 0)
				{			
					$data_source->append_log("Failed to erase from SOLR: id:" .$target_ro->id , 'error', 'registry_object');
				}
				else{
					$this->solr->commit();
				}
			}
		}

		// Also treat identifier matches as affected records which need to be enriched
		// (to increment their extRif:matching_identifier_count)
		$related_ids_by_identifier_matches = $target_ro->findMatchingRecords(); // from ro/extensions/identifiers.php
		$related_keys = array();
		foreach($related_ids_by_identifier_matches AS $matching_record_id)
		{
			$matched_ro = $this->ro->getByID($matching_record_id);
			$reenrich_queue[] = $matched_ro->key;
		}

		// Delete the actual registry object
		$this->load->model('data_source/data_sources', 'ds');
		$data_source = $this->ds->getByID($target_ro->data_source_id);
		$log = $target_ro->eraseFromDatabase($target_ro->id);
		//if($log)
		//$data_source->append_log("eraseFromDatabase " . $log, 'info', 'registry_object');

		if($finalise)
		{
			// And then their related records get reindexed...
			$this->importer->_enrichRecords($reenrich_queue);
			$this->importer->_reindexRecords($reenrich_queue);
			//log_message('debug', "Reindexed " . count($reenrich_queue) . " related record(s) when " . $target_ro->key . " was deleted.");
		}

		return $reenrich_queue;
	}

	public function getDeletedRegistryObjects($search_criteria)
	{
		$query = $this->db->get_where('deleted_registry_objects', $search_criteria);
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->result_array();
		}
	
	}


	public function getDeletedRegistryObject($id)
	{
		$query = $this->db->get_where('deleted_registry_objects', array("id" => $id));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->result_array();
		}
	
	}

	public function removeDeletedRegistryObject($id)
	{
		$this->db->where("id", $id)->delete('deleted_registry_objects');
		return;
	}


	public function clearAllFromDatasourceUnsafe($data_source_id)
	{
		$reenrich_queue = array();

		$registryObjects = $this->getIDsByDataSourceID($data_source_id);

		foreach($registryObjects AS $target_ro_id)
		{
			$target_ro = $this->ro->getByID($target_ro_id);
			$target_ro->eraseFromDatabase();
		}

		// Delete from the index
		$result = json_decode($this->solr->deleteByQueryCondition("data_source_id:(\"".$data_source_id."\")"));

		if($result->responseHeader->status != 0)
		{			
			$this->load->model('data_source/data_sources', 'ds');
			$data_source = $this->ds->getByID($data_source_id);
			$data_source->append_log("Failed to erase from SOLR: ds_id:" .$data_source_id , 'error', 'data_source');
		}



		$this->solr->commit();

	}

	public function getRecordsInDataSourceFromOldHarvest($data_source_id, $harvest_id)
	{

		$oldRegistryObjectIDs = $this->getOldHarvestedRecordIDsByDataSourceID($data_source_id, $harvest_id);
		return $oldRegistryObjectIDs;
	}


	/**
	 * @ignore
	 */
	function __construct()
	{
		parent::__construct();
		include_once("_registry_object.php");
	}

}

/* Avoid hammering the database if the registry object
   has been recently accessed from the database */
class RegistryObjectReferenceCache {

	static $cache_size = 150;
	static $recent_ids = null;
	static $registry_object_cache = array();
	static $false = false;

	static function &getByID($id)
	{
		if (isset(self::$registry_object_cache[$id]))
		{
			return self::$registry_object_cache[$id];
		}
		else
		{
			return self::$false;
		}
	}

	static function fetchCacheReference(_registry_object $ro)
	{
		// First check the queue is initialised
		if (!self::$recent_ids) { self::$recent_ids = new SplQueue(); }
		// Add this RO to the queue
		self::$recent_ids->enqueue($ro->id);

		// If the cache is overflowing, purge the oldest
		if (self::$recent_ids->count() > self::$cache_size)
		{
			$stale_id = self::$recent_ids->dequeue();
			unset(self::$registry_object_cache[$stale_id]);
		}

		// Get the cache reference for this registry object
		self::$registry_object_cache[$ro->id] = $ro;
		return self::$registry_object_cache[$ro->id];
	}
}
