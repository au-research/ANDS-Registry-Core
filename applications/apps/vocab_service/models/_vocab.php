<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Data Sources PHP object
 * 
 * This class defines the PHP object representation of 
 * data sources. Objects can be initialised, modified 
 * and saved, abstracting away the underlying attribute
 * structure. 
 * 
 * "Core" attributes must be initialised before a registry
 * object can be created. 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/datasource
 * @subpackage helpers
 */
class _vocab {
	
	private $id; 	// the unique ID for this data source
	private $_CI; 	// an internal reference to the CodeIgniter Engine 
	private $db; 	// another internal reference to save typing!
	
	public $attributes = array();		// An array of attributes for this Data Source
	const MAX_NAME_LEN = 32;
	const MAX_VALUE_LEN = 255;
	
	function __construct($id = NULL, $core_attributes_only = FALSE)
	{
		if (!is_numeric($id) && !is_null($id)) 
		{
			throw new Exception("Vocab Wrapper must be initialised with a numeric Identifier");
		}
		
		$this->id = $id;				// Set this object's ID
		$this->_CI =& get_instance();	// Get a pointer to the framework's instance
		$this->db = $this->_CI->load->database('vocabs',TRUE);	// Shorthand pointer to database
		
		if (!is_null($id))
		{
			$this->init();
		}
	}
	
	
	function getID()
	{
		return $this->id;
	}
	
	function init()
	{
		/* Initialise the  attributes */
		$query = $this->db->get_where("vocab_metadata", array('id' => $this->id));
		
		if ($query->num_rows() == 1)
		{
			$core_attributes = $query->row();	
			foreach($core_attributes AS $name => $value)
			{
				$this->_initAttribute($name, $value, TRUE);
			}
		}
		else 
		{
			throw new Exception("Unable to select Vocab from database");
		}
			
		return $this;

	}
	
	function create()
	{
		$this->db->insert("vocab_metadata", array("id" => NULL));
		$this->id = $this->db->insert_id();
		//$this->save();
		return $this;
	}
	
	function save()
	{
		foreach($this->attributes AS $attribute)
		{
			$updateQuery = "UPDATE vocab_metadata SET `".$attribute->name."` = '".mysql_real_escape_string($attribute->value)."' WHERE `id` = '".$this->id."'";
			$this->db->query($updateQuery);
		}
		return $this;
	}
	
	
	function getAttribute($name, $graceful = TRUE)
	{
		if (isset($this->attributes[$name]) && $this->attributes[$name] != NULL) 
		{
			return $this->attributes[$name]->value;			
		}
		else if (!$graceful)
		{
			throw new Exception("Unknown/NULL attribute requested by getAttribute($name) method");
		}
		else
		{
			return NULL;
		}
	}
	
	function setAttribute($name, $value = NULL)
	{
		if (strlen($name) > self::MAX_NAME_LEN )
		{
			throw new Exception("Attribute name exceeds " . self::MAX_NAME_LEN . " chars or value exceeds " . self::MAX_VALUE_LEN . ". Attribute not set"); 
		}
	
		// setAttribute
		if ($value !== NULL)
		{
			if (isset($this->attributes[$name]))
			{
				if ($this->attributes[$name]->value != $value)
				{
					$this->attributes[$name]->value = $value;
					$this->attributes[$name]->dirty = TRUE;
				}
			}

		}
		else
		{
			if (isset($this->attributes[$name]))
			{
				$this->attributes[$name]->value = NULL;
				$this->attributes[$name]->dirty = TRUE;
			}			
		}
		
		return $this;
	}
	
	function unsetAttribute($name)
	{
		setAttribute($name, NULL);
	}
	
	
	function attributes()
	{
		$attributes = array();
		foreach ($this->attributes AS $attribute)
		{
			$attributes[$attribute->name] = $attribute->value;
		}
		return $attributes;
	}
		
	function _initAttribute($name, $value, $core=FALSE)
	{
		$this->attributes[$name] = new _vocab_attribute($name, $value);
		if ($core)
		{
			$this->attributes[$name]->core = TRUE;
		}
	}
	function _initVersions($status,$version,$format)
	{
		$this->versions[$status] = new _vocab_version($status,$version,$format);

	}	
	
	/*
	 * LOGS
	 */
	function append_log($log_message, $log_type = "message")
	{
		$this->db->insert("data_source_logs", array("data_source_id" => $this->id, "date_modified" => time(), "type" => $log_type, "log" => $log_message));
		return true;
	}
	
	function get_logs($count = 10, $offset = 0)
	{
		$logs = array();
		$query = $this->db->get_where("data_source_logs", array("data_source_id"=>$this->id), $count, $offset);
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() AS $row)
			{
				$logs[] = $row;		
			}
		}
		return $logs;
	}
	
	function clear_logs()
	{
		$this->db->where(array("data_source_id" => $this->id));
		$this->db->delete("data_source_logs");
		return;
	}
	
	
	/*
	 * 	STATS
	 */
	
	function updateStats()
	{
		$this->_CI->load->model("registry_object/registry_objects", "ro");
		foreach ($this->_CI->ro->valid_classes AS $class)
		{
			$this->db->where(array('data_source_id'=>$this->id, 'class'=>$class));
			$this->setAttribute("count_$class", $this->db->count_all_results('registry_objects'));
		}
		
		foreach ($this->_CI->ro->valid_status AS $status)
		{
			$this->db->where(array('data_source_id'=>$this->id, 'status'=>$status));
			$this->setAttribute("count_$status", $this->db->count_all_results('registry_objects'));
		}
		foreach ($this->_CI->ro->valid_levels AS $attribute_name => $level)
		{
			// SO MUCH repetitiveness ;-(
			$this->db->join('registry_object_attributes', 'registry_object_attributes.registry_object_id = registry_objects.registry_object_id');
			$this->db->where(array('data_source_id'=>$this->id, 'attribute'=>'quality_level', 'value'=>$level));
			$this->setAttribute("count_$attribute_name", $this->db->count_all_results('registry_objects'));
		}
		$this->save();
		return $this;
	}
	
	/*
	 * magic methods
	 */
	function __toString()
	{
		$return = sprintf("%s (%s) [%d]", $this->getAttribute("key", TRUE), $this->getAttribute("slug", TRUE), $this->id) . BR;
		foreach ($this->attributes AS $attribute)
		{
			$return .= sprintf("%s", $attribute) . BR;
		}
		return $return;	
	}
	
	/**
	 * This is where the magic mappings happen (i.e. $data_source->record_owner) 
	 *
	 * @ignore
	 */
	function __get($property)
	{
		if($property == "id")
		{
			return $this->id;
		}
		else
		{
			return call_user_func_array(array($this, "getAttribute"), array($property));
		}
	}
	
	/**
	 * This is where the magic mappings happen (i.e. $data_source->record_owner) 
	 *
	 * @ignore
	 */
	function __set($property, $value)
	{
		if($property == "id")
		{
			$this->id = $value;
		}
		else
		{
			return call_user_func_array(array($this, "setAttribute"), array($property, $value));
		}
	}
}


/**
 * Data Source Attribute
 * 
 * A representation of attributes of a data source, allowing
 * the state of the attribute to be mainted, so that calls
 * to ->save() only write dirty data to the database.
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @version 0.1
 * @package ands/datasource
 * @subpackage helpers
 * 
 */
class _vocab_attribute 
{
	public $name;
	public $value;
	public $core = FALSE; 	// Is this attribute part of the core table or the attributes annex
	public $dirty = FALSE;	// Have we changed it since it was read from the DB
	public $new = FALSE;	// Is this new since we read from the DB
	
	function __construct($name, $value)
	{
		$this->name = $name;
		$this->value = $value;
	}
	
	/**
	 * @ignore
	 */
	function __toString()
	{
		return sprintf("%s: %s", $this->name, $this->value) . ($this->dirty ? " (Dirty)" : "") . ($this->core ? " (Core)" : "") . ($this->new ? " (New)" : "");	
	}
}
