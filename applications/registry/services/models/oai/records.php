<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * OAI Provider: Records model
 *
 * OAI Records are sources from registry_objects
 *
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 * @package ands/services/oai
 *
 */
class Records extends CI_Model
{

	public function get($set,
			    $after=false,
			    $before=false,
			    $start=0)
	{
		$this->load->model('oai/Sets', 'sets');
		$this->load->model('registry_object/Registry_objects', 'ro');
		$args = array();
		$delArgs = array();
		$args['rawclause'] = array('registry_objects.status' => "'PUBLISHED'");
		$args['clause'] = array();
		$args['wherein'] = false;
		$count = '';
		$deleted_records = array();
		if ($after)
		{
			$args["clause"]["registry_object_attributes.value >="] = $after->getTimestamp();
			$delArgs['deleted >='] = $after->getTimestamp();
		}

		if ($before)
		{
			$args["clause"]["registry_object_attributes.value <="] = $before->getTimestamp();
			$delArgs['deleted <='] = $before->getTimestamp();
		}

		if ($after or $before)
		{
			$args["clause"]["registry_object_attributes.attribute"] = "updated";
		}

		if ($set)
		{
			$args["wherein"] = $this->sets->getIDsForSet($set);
			$delArgs[$set->source] = $set->val;
		}

		if(!($set&&!$args["wherein"]))
		{
		$count = $this->ro->_get(array(array('args' => $args,
						     'fn' => function($db, $args) {
							     $db->select("count(distinct(registry_objects.registry_object_id))")
								     ->from("registry_objects")
								     ->join("data_sources",
									    "data_sources.data_source_id = registry_objects.data_source_id",
									    "inner")
								     ->join("registry_object_attributes",
									    "registry_object_attributes.registry_object_id = registry_objects.registry_object_id",
									    "inner")
								     ->where($args['rawclause'], null, false)
								     ->where($args['clause']);
							     if ($args['wherein'])
							     {
								     $db->where_in("registry_objects.registry_object_id",
										   $args['wherein']);
							     }
							     return $db;
						     })),
					 false);

		// get the deleted ones! and added to the count...
		if($after && $before)
		{
			$deleted_records = $this->ro->getDeletedRegistryObjects($delArgs);
		}
		if (is_array($count) && isset($count[0]["count(distinct(registry_objects.registry_object_id))"]))
		{
			$count[0]["count(distinct(registry_objects.registry_object_id))"] = (int)$count[0]["count(distinct(registry_objects.registry_object_id))"] + sizeof($deleted_records);
		}
		else{
			$count[0]["count(distinct(registry_objects.registry_object_id))"] = sizeof($deleted_records);
		}
		}
		if (!is_array($count)||$count[0]["count(distinct(registry_objects.registry_object_id))"]==0)
		{
			throw new Oai_NoRecordsMatch_Exceptions();
		}
		else
		{
			$count = $count[0]["count(distinct(registry_objects.registry_object_id))"];
		}

		$records = $this->ro->_get(array(array('args' => $args,
						       'fn' => function($db, $args) {
							       $db->distinct()
								       ->select("registry_objects.registry_object_id")
								       ->from("registry_objects")
								       ->join("data_sources",
									      "data_sources.data_source_id = registry_objects.data_source_id",
									      "inner")
								       ->join("registry_object_attributes",
									      "registry_object_attributes.registry_object_id = registry_objects.registry_object_id",
									      "inner")
								       ->where($args['rawclause'], null, false)
								       ->where($args['clause']);
							       if ($args['wherein'])
							       {
								       $db->where_in("registry_objects.registry_object_id",
										     $args['wherein']);
							       }
							       $db->order_by("registry_objects.registry_object_id", "asc");
							       return $db;
						       })),
					   true,
					   100,
					   $start);

		if (isset($records) || isset($deleted_records))
		{
			if(isset($records))
			{
				foreach ($records as &$ro)
				{
					$ro = new _record($ro, $this->db);
					$ro->sets = $this->sets->get($ro->id);
				}
			}
			else{
				$records = array();
			}
			if(isset($deleted_records))
			{
				foreach ($deleted_records as $del_ro)
				{
					$del_ro = (object) array('registry_object_id'=>$del_ro['key'], 'status' => 'deleted', 'deleted'=>$del_ro['deleted'], 'sets' => array('class'=>$del_ro['class'],'group'=>$del_ro['group'],'datasource'=>$del_ro['datasource'] )); 
					$records[] = $del_ro;
				}
			}
			return array('records' => $records,
			     'cursor' => $start + count($records),
			     'count' => $count);
		}
		else
		{
			return array('records' => 0,
				'cursor' => 0,
				'count' => 0);
		}
		
	}

	public function getByIdentifier($identifier)
	{
		$this->load->model('registry_object/Registry_objects', 'ro');	
		$ro = $this->ro->getPublishedByKey($identifier);
		$deleted = false;
		$del_ro = null;
		if(!$ro && preg_match('/^oai:.*?::[0-9]+/', $identifier))
		{
			$ident = explode("::", $identifier);
			$identifier = $ident[1];
			$ro = $this->ro->getByID($identifier);
		}
		if(!$ro)
		{
			$deleted_records = $this->ro->getDeletedRegistryObjects(array('key'=>$identifier));
			if(is_array($deleted_records))
			{
				foreach ($deleted_records as $del_ro)
				{
					$ro = (object) array('registry_object_id'=>$del_ro['key'], 'status' => 'deleted', 'deleted'=>$del_ro['deleted'], 'sets' => array('class'=>$del_ro['class'],'group'=>$del_ro['group'],'datasource'=>$del_ro['datasource'] )); 
					$deleted = true;
				}
			}

		}
		if($ro)
		{
			if($deleted)
				return $ro;
			else
				return new _record($ro, $this->db);
		}		
		else
		{
			throw new Oai_NoRecordsMatch_Exceptions("record not found");
		}
	}


	/**
	 * Get the OAI sets associated with this record ID
	 * @param an OAI identifier
	 * @return an array of `_set`s
	 */
	public function sets($ident)
	{
		$record = $this->identify($ident);
		return $record->sets();
	}

	/**
	 * Find the earliest record: used for the `Identify` verb
	 * @return the oldest known `created` timestamp in ISO8601 format.
	 */
	public function earliest()
	{
		try
		{
			$oldest = $this->db->select_min("value")
				->get_where("registry_object_attributes",
					    array("attribute" => "created"))->row()->value;
		}
		catch (Exception $e)
		{
			$oldest = gmdate('Y-m-d\TH:i:s\+\Z', gmmktime());
		}

		return gmdate('Y-m-d\TH:i:s\+\Z', $oldest);

	}

	/**
	 * Retrieve a record specified by an OAI identifier
	 * @param the OAI identifier
	 * @return a `_record`
	 * @throw "bad identifier" Exception if `$ident` doesn't yeild a valid id
	 * @throw "record not found" Exception if `$ident` id doesn't yeild a record
	 */
	public function identify($ident)
	{
		#ident looks like 'oai:[host]::id'
		if (!preg_match('/^oai:.*?::[0-9]+/', $ident))
		{
			throw new Oai_BadArgument_Exceptions("malformed identifier");
		}
		$ident = explode("::", $ident);
		try
		{
			if (count($ident) < 2)
			{
				throw new Exception;
			}
			else
			{
				$id = (int)$ident[1];
				$query = $this->db->get_where("registry_objects",
							      array("registry_object_id" => $id));
				if ($query->num_rows > 0)
				{
					$rec = $query->row();

					return new _record($rec, $this->db);
				}
				else
				{
					throw new Oai_NoRecordsMatch_Exceptions("record not found");
				}
			}
		}
		catch (OAI_Exceptions $e)
		{
			throw $e;
		}
		catch (Exception $ee)
		{
			throw new Oai_BadArgument_Exceptions("bad identifier");
		}
	}

	public function __construct()
	{
		parent::__construct();
		include_once("_record.php");
	}
}
?>
