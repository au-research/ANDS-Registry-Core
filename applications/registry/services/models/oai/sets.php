<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * OAI Provider: Sets model
 *
 * OAI Sets are sourced from:
 *  - `data_sources`
 *  - `registry_objects`#class
 *  - `registry_object_attributes`#value(where attribute=group)
 *
 * Sets have 2 pieces of information:
 *  - setSpec: something that uniquely identifies the set
 *  - setName: something human readable
 *
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 * @package ands/services/oai
 *
 */
class Sets extends CI_Model
{
	/**
	 * Get sets, optionally for a specific record
	 * @param a record id (optional)
	 * @return an array of `_set`s
	 */
	public function get($id=false)
	{
	    $ds = array();
	    $roa = array();
	    $roc = array();
	    $this->load->model('data_source/Data_sources', 'ds');
	    $this->load->model('registry_object/Registry_objects', 'ro');

	    if ($id)
	    {
		try
		{
		    $reg_obj = $this->ro->getByID($id);
		}
		catch (Exception $e)
		{
		    #invalid id: ignore it
		    return array();
		}
		if ($reg_obj)
		{
		    $ds = array($this->ds->getByID($reg_obj->data_source_id));
		    $roa = array(str_replace(" ", "0x20", $reg_obj->getAttribute("group")));
		    $roc = array($reg_obj->class);
		}
		else
		{
		    #invalid id: ignore it
		    return array();
		}
	    }
	    else
	    {
		$ds = $this->ds->getAll(0);

		$query = $this->db->distinct()->select("class")->get("registry_objects");
		$roc = array_map(create_function('$r', 'return $r["class"];'),
				 $query->result_array());

		$query = $this->db->distinct()->select("value")
		    ->get_where("registry_object_attributes",
				array("attribute" => "group"));
		$roa = array_map(create_function('$r', 'return str_replace(" ", "0x20",$r["value"]);'),
				 $query->result_array());
	    }

	    foreach ($ds as $set)
	    {
		$sets[] = $this->_from_ds($set);
	    }

	    foreach ($roc as $set)
	    {
	    	$sets[] = $this->_from_class($set);
	    }

	    foreach ($roa as $set)
	    {
		$sets[] = $this->_from_group($set);
	    }
	    return $sets;
	}

	private function _from_group($set)
	{
	    	return new _set("group", $set, $set);
	}

	private function _from_ds($set)
	{
	    if ($set instanceof _data_source)
	    {
		return new _set("datasource",
				$set->getAttribute("slug"),
				str_replace("&"," ",$set->getAttribute("title")));
	    }
	    else
	    {
		return null;
	    }
	}

	private function _from_class($set)
	{
	    	return new _set("class", $set, $set);
	}

	/**
	 * Retrieve a _set object matching the supplied spec
	 *
	 * @param the setSpec to identify the spec
	 * @return a _set object, or null if the supplied spec didn't identify anything
	 */
	public function getBySpec($spec)
	{
	    $spec = urldecode($spec);
	    $split_spec = explode(':', $spec, 2);
	    if (count($split_spec) < 2)
	    {
		throw new Oai_BadArgument_Exceptions("malformed set spec '$spec'");
	    }
	    $prefix = $split_spec[0];
	    $spec = (string)$split_spec[1];

	    switch($prefix)
	    {
	    case "datasource":
		$this->load->model('data_source/Data_sources', 'ds');

		$ds = $this->ds->getBySlug($spec);
		// Needed to fix returning all records when the datasource doesn't exist - if the datsource is null then must set up a dummy useless set
		if($ds){
			$set = $this->_from_ds($ds);
		}else{
			$set='No set';
		}
		break;
	    case "class":
		$query = $this->db->distinct()->select("class")
		    ->get_where("registry_objects",
				array("class" => $spec));
		if ($query->num_rows < 0)
		{
		    $set = null;
		}
		else
		{
		    $set = $this->_from_class($spec);
		}
		$query->free_result();
		break;
	    case "group":
		$query = $this->db->distinct()->select("value")
		    ->get_where("registry_object_attributes",
				array("attribute" => "group",
				      "value" => str_replace(" ", "0x20",$spec)));
		if ($query->num_rows < 0)
		{
		    $set = null;
		}
		else
		{
		    $set = $this->_from_group($spec);
		}
		$query->free_result();
		break;
	    default:
		throw new Oai_BadArgument_Exceptions("unknown setSpec '$spec'");
	    }
	    return $set;
	}

	/**
	 * Retrieve an array of registry_object_ids for a given set
	 *
	 * @param a `_set` object
	 * @return an array of ints that are `registry_objects.registry_object_ids`.
	 * note that if `$set` is not an instance of `_set`, an empty array will
	 * be returned.
	 */
	public function getIDsforSet($set)
	{
	    if ($set instanceof _set)
	    {
		$ids = array();
		$query = false;
		$sname = $set->name;
		$split_spec = explode(':', $set->spec, 2);
		if (count($split_spec) < 2)
		{
		    throw new Oai_BadArgument_Exceptions("malformed set spec '$spec'");
		}
		$prefix = $split_spec[0];
		$spec = $split_spec[1];
		switch($set->source)
		{
		case 'datasource':
			$query = $this->db->distinct()->select("registry_objects.registry_object_id")
				->join("data_sources",
				       "data_sources.data_source_id = registry_objects.data_source_id",
				       "inner")
				->get_where("registry_objects",
					    array("data_sources.slug" => $spec));
			break;
		case 'group':
			$query = $this->db->distinct()->select("registry_objects.registry_object_id")
				->join("registry_object_attributes",
				       "registry_object_attributes.registry_object_id = registry_objects.registry_object_id")
				->get_where("registry_objects",
					    array("registry_object_attributes.attribute" => "group",
						  "registry_object_attributes.value" => str_replace("0x20"," ",$sname)));
			break;
		case 'class':
			$query = $this->db->distinct()->select("registry_objects.registry_object_id")
				->get_where("registry_objects",
					    array("class" => $sname));
		    break;
		default:
		    //this should never really happen, but just in case
		    return array();
		    break;
		}
		$ids = array_map(create_function('$e', 'return $e["registry_object_id"];'),
						 $query->result_array());
		$query->free_result();
		return $ids;
	    }
	    else
	    {
		return array();
	    }
	}

	public function __construct()
	{
		parent::__construct();
		include_once("_set.php");
	}

}
?>
