<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Data Sources model
 * 
 * This model allows the reference and initialisation 
 * of Data Sources. All instances of the _data_source 
 * PHP class should be invoked through this model. 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */

class Data_sources extends CI_Model {

	/**
	 * Returns exactly one data source by Key (or NULL)
	 * 
	 * @param the data source key
	 * @return _data_source object or NULL
	 */
	function getByKey($key, $use_cache = true)
	{
		// Return cached _data_source object if the same key
		if($use_cache){
			$recent = DataSourceReferenceCache::getRecent();
			if ($recent && $recent->key == $key)
			{
				return $recent;
			}

			$query = $this->db->select("data_source_id")->get_where('data_sources', array('key'=>$key));
			if ($query->num_rows() == 0)
			{
				return NULL;
			}
			else
			{
				$id = $query->result_array();
				if($use_cache){
					DataSourceReferenceCache::set(new _data_source($id[0]['data_source_id']));
					return DataSourceReferenceCache::getRecent();
				} else {
					return new _data_source($id[0]['data_source_id']);
				}
			}

		}
	} 	
	
	/**
	 * Returns exactly one data source by ID (or NULL)
	 * 
	 * @param the data source ID
	 * @return _data_source object or NULL
	 */
	function getByID($id, $as_object = true)
	{
		// Return cached _data_source object if the same ID (and we requested $as_object)
		$recent = DataSourceReferenceCache::getRecent();
		if ($as_object && $recent && $recent->id == $id)
		{
			return $recent;
		}

		$query = $this->db->select("*")->get_where('data_sources', array('data_source_id'=>$id));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->result_array();
			if ($as_object)
			{
				DataSourceReferenceCache::set(new _data_source($row[0]['data_source_id']));
				return DataSourceReferenceCache::getRecent();
			}
			else
			{
				// Just return the DB result
				return array_pop($row);
			}
		}
	}
	
		/**
	 * Returns exactly one data source by ID (or NULL)
	 * 
	 * @param the data source ID
	 * @return _data_source object or NULL
	 */
	function getByHarvestID($harvestId)
	{
		$query = $this->db->select("data_source_id")->get_where('harvest_requests', array("id"=>$harvestId));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$id = $query->result_array();
			return new _data_source($id[0]['data_source_id']);
		}
	}

	/**
	 * Returns data sources which this user has ownership of by virtue of their
	 * affiliation (organisational roles)
	 * 
	 * @param the data source ID
	 * @return _data_source object or NULL
	 */
	function getOwnedDataSources($just_id = false, $just_core=false) {
		$data_sources = array();
        // $this->user->refreshAffiliations($this->user->localIdentifier());
		$affiliations = $this->user->affiliations();
		if ((is_array($affiliations) && count($affiliations) > 0) || $this->user->hasFunction(AUTH_FUNCTION_SUPERUSER)) {
			if ($this->user->hasFunction(AUTH_FUNCTION_SUPERUSER)) {
				$query = $this->db->query("SELECT * FROM data_sources");	
			} else {
				$query = $this->db->where_in('record_owner', $affiliations)->get('data_sources');
			}

			if ($query->num_rows() == 0) {
				return $data_sources;
			} else {
				foreach($query->result_array() AS $ds)
				{
					if($just_id){
						$data_sources[] = $ds['data_source_id'];
					}elseif($just_core){
						$data_sources[] = $ds;
					}else{
						$data_sources[] =  new _data_source($ds['data_source_id']);
					}
					
				}
			}
		}
		return $data_sources;
	}

	/**
	 * Returns groups which this datasource has objects which are contributed by
	 * 
	 * @param the data source ID
	 * @return array of groups or NULL
	 */
	function getDataSourceGroups($data_source_id)
	{
		$groups = array();

		$query = $this->db->select('group')->where('registry_object_id','data_source_id')->get('data_source_attributes');
			if ($query->num_rows() == 0)
			{
				return $groups;
			}
			else
			{				
				foreach($query->result_array() AS $group)
				{
					$groups[] =  $group['group'];
				}
			}
		return $groups;	
	} 	



	/**
	 * Returns exactly one data source by URL slug (or NULL)
	 * 
	 * @param the data source slug
	 * @return _data_source object or NULL
	 */
	function getBySlug($key)
	{
		$query = $this->db->select("data_source_id")->get_where('data_sources', array('slug'=>$key));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$id = $query->result_array();
			return new _data_source($id[0]['data_source_id']);
		}
	} 	
	
	/**
	 * Get a number of datasources that match the attribute requirement (or an empty array)
	 * 
	 * @param the name of the attribute to match by
	 * @param the value that the attribute must match
	 * @return array(_data_source)
	 */
	function getByAttribute($attribute_name, $value) {
		$matches = array();
		if(in_array($attribute_name, array('key', 'slug', 'title', 'record_owner'))) {
			$query = $this->db->select('data_source_id')->get_where('data_sources', array($attribute_name=>$value));
		} else {
			$query = $this->db->select("data_source_id")->get_where('data_source_attributes', array("attribute"=>$attribute_name, "value"=>$value));
		}
		
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() AS $result) {
				$matches[] = new _data_source($result['data_source_id']);
			}
		}
		return $matches;
	} 
	
	/**
	 * Get all datasources
	 * 
	 * @param limit by value
	 * @param the offset value
	 * @return array(_data_source) or empty array
	 */
	function getAll($limit = 16, $offset =0)
	{
		$matches = array();
		if($limit==0){
			$query = $this->db->select("data_source_id")->get('data_sources');
		}else{
			$query = $this->db->select("data_source_id")->get('data_sources', $limit, $offset);
		}
		
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() AS $result)
			{
				$matches[] = new _data_source($result['data_source_id']);
			}
		}
		
		return $matches;
	} 

	/** get 2 groups by size

	**/

	function getGroupsBySizeLimit($sizeLimit = 99999999)
	{
		$matches = array();
		$query = $this->db->select('data_source_id, count(*) as count')->from('registry_objects')->where('status', 'PUBLISHED')->group_by('data_source_id')->order_by('count asc')->get();
		$matches['small'] = array();
		$matches['large'] = array();
		foreach($query->result_array() as $r){
			if($r['count'] <= $sizeLimit)
				$matches['small'][] = $r;
			else
				$matches['large'][] = $r;
		}
		return $matches;
	}
	

	/**
	 * XXX: 
	 * @return array(_data_source) or NULL
	 */
	function create($key, $slug)
	{
		$ds = new _data_source();
		
		// Compulsory attributes
		$ds->_initAttribute("key",$key, TRUE);
		$ds->_initAttribute("slug",$slug, TRUE);
		
		// Some extras
		$ds->setAttribute("created",time());

		$ds->create();
		return $ds;
	} 	
	
	/**
	 * @ignore
	 */
	function __construct()
	{
		parent::__construct();
		require_once("_data_source.php");
	}	
		
}

/* Avoid hammering the database if the last accessed
   data source object is the same as the current one */
class DataSourceReferenceCache {
	static $recent = NULL;

	static function &getRecent()
	{
		// force PHP to return an object reference
		return self::$recent;
	}
	static function set($data_source_obj)
	{
		self::$recent = $data_source_obj;
	}
}
