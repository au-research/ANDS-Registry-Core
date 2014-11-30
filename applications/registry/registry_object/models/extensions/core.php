<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Core_extension extends ExtensionBase
{
	// Map of this registry object's current attributes
	public $attributes = array();		// An array of attributes for this Registry Object
	
	// Core attributes are stored in the registry_object table (whereas the other attributes require a join to _attributes)
	private $core_attrs = array('data_source_id', 'registry_object_id', 'key', 'class', 'title', 'status', 'slug', 'record_owner');

	// Some limits on attributes
	const MAX_NAME_LEN = 32;
	const MAX_VALUE_LEN = 255;

	function init($core_attributes_only = FALSE)
	{
		// Initialise the core attributes (these are the attributes from the 
		// registry_objects table as opposed to others in _attributes)
		$query = $this->db->join("`registry_objects` `ro`", 'ro.registry_object_id = ra.registry_object_id')
							->get_where("`registry_object_attributes` `ra`", array('ra.registry_object_id' => $this->id));
		//echo $this->id;
        //echo "hello world";
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() AS $row)
			{
				// If this is the first pass, initialise the core attributes
				if (!$this->getAttribute('key'))
				{
					foreach($this->core_attrs AS $attribute_name)
					{
						$this->_initAttribute($attribute_name, $row[$attribute_name], TRUE);
					}
				}

				if (!$core_attributes_only)
				{
					// _initAttribute has no side-effects (as opposed to setAttribute 
					// which marks the attribute as dirty)
					$this->_initAttribute($row['attribute'], $row['value']);
				}
			}
			$query->free_result();
		}
		else 
		{
			throw new Exception("Unable to select Registry Object from database ID:".$this->id);
		}
			
		// Store the status of the registry object when it was first retrieved so
		// that we can determine whether it has changed when deciding whether to
		// "upgrade" it from DRAFT to PUBLISHED
		$this->_initAttribute("original_status", $this->attributes['status']->value);

		return $this;

	}
	
	function setAttribute($name, $value = NULL)
	{
		// truncate attributes that are too long
		if(strlen($value) > self::MAX_VALUE_LEN)
			$value = substr($value, 0 ,self::MAX_VALUE_LEN);

		// setAttribute
		if ($value !== NULL)
		{
			if (isset($this->attributes[$name]))
			{
				if ($this->attributes[$name]->value != $value)
				{
					// Attribute already exists, we're just updating it
					$this->attributes[$name]->value = $value;
					$this->attributes[$name]->dirty = TRUE;
				}
			}
			else 
			{
				// This is a new attribute that needs to be created when we save
				$this->attributes[$name] = new _registry_object_attribute($name, $value);
				$this->attributes[$name]->dirty = TRUE;
				$this->attributes[$name]->new = TRUE;
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

	function getAttributes(){
		return $this->attributes;
	}
	
	function create()
	{

		$this->setAttribute("original_status", $this->getAttribute("status"));
		
		$this->db->insert("registry_objects", array("data_source_id" => $this->getAttribute("data_source_id"), 


													"key" => (string) $this->getAttribute("key"), 
													"class" => $this->getAttribute("class"),
													"title" => $this->getAttribute("title"),
													"status" => $this->getAttribute("status"),
													"slug" => $this->getAttribute("slug"),
													"record_owner" => $this->getAttribute("record_owner")								
													));

		$this->ro->id = $this->db->insert_id();
		$this->id = $this->ro->id;
		$this->save();
		return $this;
	}
	
	function save($change_updated = false)
	{
		// When saving, trigger special business logic if the record status changed

		// If going from PUBLISHED to DRAFT, create a new draft clone
		if($this->getAttribute("status") == 'DRAFT' && $this->getAttribute("original_status") == 'PUBLISHED')
		{
			$draftRecord = $this->_CI->ro->cloneToDraft($this->id);
		}
		else
		{
			if ($this->getAttribute("status") != $this->getAttribute("original_status"))
			{
				$this->handleStatusChange($this->getAttribute("status"));
			}

			if ($change_updated)
			{
				// Mark this record as recently updated
				$this->setAttribute("updated", time());
			}
			
			// Perform the actual SQL updates in batches to improve performance impact of multiple queries
			$update_batch = $this->_initUpdateBatchArray();
			foreach($this->attributes AS $attribute)
			{
				if ($attribute->dirty)
				{
					if ($attribute->core)
					{
						$update_batch['core']['update'][$attribute->name] = $attribute->value;
						$attribute->dirty = FALSE;						
					}
					else
					{
						if ($attribute->value !== NULL)
						{
							if ($attribute->new)
							{
								$update_batch['attr']['insert'][] = array("registry_object_id" => $this->id, "attribute" => $attribute->name, "value"=>$attribute->value);
								$attribute->dirty = FALSE;
								$attribute->new = FALSE;
							}
							else
							{
								$update_batch['attr']['update'][] = array( "attribute" => $attribute->name, "value" => $attribute->value);
								$attribute->dirty = FALSE;
							}
						}
						else
						{
							$update_batch['attr']['delete'][] = $attribute->name;
							unset($this->attributes[$attribute->name]);
						}						
					}
				}
			}
			$this->_execBatchUpdateQueries($update_batch);

		}
		return $this;
	}

	// Execute updates as batch DB queries
	function _execBatchUpdateQueries($update_batch)
	{
		if (count($update_batch['core']['update']))
		{
			$this->db->where("registry_object_id", $this->id)->update("registry_objects", $update_batch['core']['update']);
		}
		if (count($update_batch['attr']['update']))
		{
			$this->db->where("registry_object_id", $this->id)->update_batch("registry_object_attributes", $update_batch['attr']['update'], 'attribute');
		}
		if (count($update_batch['attr']['insert']))
		{
			$this->db->insert_batch("registry_object_attributes", $update_batch['attr']['insert']);
		}
		if (count($update_batch['attr']['delete']))
		{
			$this->db->where("registry_object_id", $this->id)->where_in('attribute', $update_batch['attr']['delete'])->delete('registry_object_attributes');
		}
	}

	// Build the initial batch update array
	function _initUpdateBatchArray()
	{
		return array(	'core' =>array('update'=>array()), 
						'attr'=>array('insert'=>array(), 'update'=>array(), 'delete'=>array())
		);
	}


	/* Handles the changing of status soas not to cause inconsistencies */
	function handleStatusChange($target_status)
	{
		$this->_CI->load->library('Solr');

		// Changing between draft statuses, nothing to worry about:
		$this->_CI->load->model('data_source/data_sources', 'ds');
		$data_source = $this->_CI->ds->getByID($this->getAttribute('data_source_id'));

		if (isDraftStatus($this->getAttribute('original_status')) && isDraftStatus($target_status))
		{
			if($this->getAttribute('original_status') == 'ASSESSMENT_IN_PROGRESS' && $target_status == 'APPROVED')
				$this->setAttribute("manually_assessed", 'yes');
			if($target_status == 'DRAFT')
				$this->setAttribute("manually_assessed", 'no');
		}
		// Else, if the draft is being published:
		else if (isDraftStatus($this->getAttribute('original_status')) && isPublishedStatus($target_status))
		{
			$xml = html_entity_decode($this->ro->getRif());
			$existingRegistryObject = $this->_CI->ro->getPublishedByKey($this->ro->key);
			if($existingRegistryObject && $existingRegistryObject->getAttribute('data_source_id') != $this->getAttribute('data_source_id'))
			{
				$otherDs = $this->_CI->ds->getByID($existingRegistryObject->getAttribute('data_source_id'));
				throw new Exception("Registry Object with key ".$this->ro->key." already exists in the ".NL.$otherDs->title." Data Source");
			}
			else if ($existingRegistryObject)
			{
				// Delete this original draft and change this object to point to the PUBLISHED (seamless changeover)
				$manuallyAssessed = $this->getAttribute('manually_assessed');
				$this->ro = $this->_CI->ro->getPublishedByKey($this->getAttribute("key"));

				if($this->getAttribute('original_status') === 'ASSESSMENT_IN_PROGRESS' || $manuallyAssessed === 'yes')
				{
					$this->ro->setAttribute("manually_assessed", 'yes');
				}
				if($this->ro->getAttribute('gold_status_flag') === 't')
				{
					$this->ro->setAttribute("gold_status_flag", 'f');
				}

				$this->ro->harvest_id = $this->getAttribute('harvest_id');
				$this->ro->save();


				$this->_CI->ro->deleteRegistryObject($this->id);
				$this->id = $this->ro->id;

				$this->init();
			}


			// If the importer is already running
			if ($this->_CI->importer->isImporting)
			{
				// other actions will occur in the existing importer run...
			}
			else
			{
				// Add the XML content of this draft to the published record (and follow enrichment process, etc.)
				$this->_CI->importer->_reset();
				$this->_CI->importer->setXML(wrapRegistryObjects($xml));
				$this->_CI->importer->setDatasource($data_source);
				$this->_CI->importer->forcePublish();
				$this->_CI->importer->statusAlreadyChanged = true;
				$this->_CI->importer->commit();
				if($this->getAttribute('original_status') == 'ASSESSMENT_IN_PROGRESS' || $this->getAttribute('manually_assessed') == 'yes')
				{
					$this->ro = $this->_CI->ro->getPublishedByKey($this->getAttribute("key"));
					$this->ro->setAttribute("manually_assessed", 'yes');
				}

				$this->ro->index_solr();

				if ($error_log = $this->_CI->importer->getErrors())
				{
					throw new Exception("Errors occured whilst migrating to PUBLISHED status: " . NL . $error_log);
				}
			}
		}
		else // Else, the PUBLISHED record is being converted to a DRAFT
		{
			$existingRegistryObject = $this->_CI->ro->getDraftByKey($this->ro->key);
			if ($existingRegistryObject)
			{
				// Delete any existing drafts (effectively overwriting them)
				$this->_CI->ro->deleteRegistryObject($existingRegistryObject->id);
			}

			// Reenrich related records (reindexes affected records)
			// XXX: REENRICH RECORDS RELATED TO ME WHEN I CHANGE STATUS
			/*
			$reenrich_queue = $target_ro->getRelatedKeys();
			$this->_CI->importer->_enrichRecords($reenrich_queue);
			$this->_CI->importer->_reindexRecords($reenrich_queue);
			*/
			$this->ro->slug = DRAFT_RECORD_SLUG . $this->ro->id;

			//remove the record from the index
			$this->_CI->solr->deleteByQueryCondition('id:'.$this->ro->id);
		}

		$this->_initAttribute("original_status", $target_status);
	}

	/* Removes all trace of the record from the database (use this wisely...) */
	function eraseFromDatabase()
	{
		$log ='';
		$this->db->delete('registry_object_relationships', array('registry_object_id'=>$this->id));
		$this->db->delete('registry_object_identifier_relationships', array('registry_object_id'=>$this->id));
		$this->db->delete('registry_object_identifiers', array('registry_object_id'=>$this->id));
		//if($error = $this->db->_error_message())
		//$log = NL."registry_object_relationships: " .$error;
		$this->db->delete('registry_object_metadata', array('registry_object_id'=>$this->id));
		//if($error = $this->db->_error_message())
		//$log .= NL."registry_object_metadata: " .$error;
		$this->db->delete('registry_object_attributes', array('registry_object_id'=>$this->id));
		//if($error = $this->db->_error_message())
		//$log .= NL."registry_object_attributes: " .$error;
		$this->db->delete('record_data', array('registry_object_id'=>$this->id));
		//if($error = $this->db->_error_message())
		//$log .= NL."record_data: " .$error;
		$this->db->delete('url_mappings', array('registry_object_id'=>$this->id));
		//if($error = $this->db->_error_message())
		//$log .= NL."url_mappings: " .$error;
        $this->db->delete('registry_object_links', array('registry_object_id'=>$this->id));
		//TODO: do we still need this table??
		//$this->db->delete('spatial_extents', array('registry_object_id'=>$this->id));
		//$log .= NL."spatial_extents: " .$this->db->_error_message();
		$this->db->delete('registry_objects', array('registry_object_id'=>$this->id));
		//if($error = $this->db->_error_message())
		//$log .= NL."registry_objects: " .$error;
		return $log;
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
	
	function unsetAttribute($name)
	{
		setAttribute($name, NULL);
	}
	
		
	function _initAttribute($name, $value, $core=FALSE)
	{
		$this->attributes[$name] = new _registry_object_attribute($name, $value);
		if ($core)
		{
			$this->attributes[$name]->core = TRUE;
		}
	}
	
	function getID()
	{
		return $this->id;
	}
	
	
		
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}

	 public function __destruct() {
    	// Explicitly clean up our extensions...
    	foreach($this->attributes AS $name => $instance)
    	{
			$this->attributes[$name] = null;
    	}
    	unset($this->attributes);
    	unset($this->ro);
		unset($this->_CI);
		unset($this->db);
		unset($this->id);
    }
}


/**
 * Registry Object Attribute
 * 
 * A representation of attributes of a Registry Object, allowing
 * the state of the attribute to be mainted, so that calls
 * to ->save() only write dirty data to the database.
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @version 0.1
 * @package ands/registryobject
 * @subpackage helpers
 * 
 */
class _registry_object_attribute 
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