<?php


class Connections_Extension extends ExtensionBase
{

	private $party_one_types = array('person','administrativePosition');
	private $party_multi_types = array('group');
	
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	/**
	 * Get a list of connections, returning the details needed to 
	 * display the relationship:
	 *
	 * Business Rules:
	 * 1) If `a` is related to `b`, display the connection
	 *
	 * 2) If INTERNAL LINKS are enabled and `b` is related to `a`
	 *    AND `b` and `a` are in the same data source, display the
	 *    connection. 
	 *
	 * 3) If EXTERNAL LINKS are enabled and `b` is related to `a`
	 *    AND `b` and `a` are in different data sources, display
	 *    the connection. 
	 *
	 * 4) If the group of `a` has a contributor page, infer and 
	 *    display the connection.
	 *
	 * 5) If this record is 'not published', then allow links to other
	 *    'not published' records. 
	 *
	 * 6) If requested (flagged in getConnections()), 
	 *
	 * @return 	array ( 
	 *					array(
	 *						origin (of inference)
	 *						key
	 *						type
	 *						description
	 *						class
	 *						title
	 *					)
	 *			)
	 */
	function getConnections($published_only = true, $specific_type = null, $limit = 100, $offset = 0, $include_dupe_connections = false)
	{


		$allowed_draft = ($published_only==true ? false : true);
		$unordered_connections = $this->getAllRelatedObjects(!$allowed_draft, $include_dupe_connections);

		$ordered_connections = array();

		/* Now sort according to "type" (collection / party_one / party_multi / activity...etc.) */
		foreach($unordered_connections AS $connection)
		{

			// some witchcraft to disambiguate between single researchers 
			// and groups (based on registry object type)
			if ($connection['class'] == "party")
			{
				// Get the type attribute (without loading the whole model)
				$this->db->select('value')
						 ->from('registry_object_attributes')
						 ->where('attribute', 'type')
						 ->where('registry_object_id',$connection['registry_object_id']);

				$query = $this->db->get();
				foreach($query->result_array() AS $row)
				{
					if (isset($row['value']))
					{
						if (in_array($row['value'],$this->party_multi_types))
						{
							$connection['class'] = "party_multi";
						}
						else
						{
							$connection['class'] = "party_one";
						}
					}
				}
			}

			if($connection['class'] == "party" && $connection['registry_object_id'] == null && ($connection['origin'] == 'IDENTIFIER' ||  $connection['origin'] == 'IDENTIFIER REVERSE')){
				$connection['class'] = "party_one";
			}
			// $connection['description'] = $this->_getDescription($connection['registry_object_id']);

			// Continue on for all types:
			/* - Check the constraints */
			if (!is_null($specific_type))
			{
				if ($specific_type == "nested_collection")
				{
					$class_valid = ($connection['class'] == "collection" && 
						($connection['origin'] == "EXPLICIT" && $connection['relation_type'] == "hasPart")
						||
						(in_array($connection['origin'], array("REVERSE_INT","REVERSE_EXT")) && $connection['relation_type'] == "isPartOf")
					);
				}
				else
				{
					$class_valid = ($connection['class'] == $specific_type);
				}
			}
			else
			{
				$class_valid = true;
			}
			$status_valid = (!$published_only || ($connection['status'] == PUBLISHED) || ($connection['registry_object_id'] == null && ($connection['origin'] == 'IDENTIFIER' ||  $connection['origin'] == 'IDENTIFIER REVERSE')));
			if ($class_valid && $status_valid)
			{

				/* - Now classify the counts  */
				if (!isset($ordered_connections[$connection['class']]))
				{
					$ordered_connections[$connection['class']] = array();
					$ordered_connections[$connection['class'] . '_count'] = 0;
				}

				// Stop the same connected object coming from two different sources
				// NB: this prevents connections from being duplicated (uniqueness property)
				if($connection['registry_object_id'] === null && $connection['identifier_relation_id'] !== null)
				{
					if(!isset($ordered_connections[$connection['class']][$connection['identifier_relation_id']]))
					{
						$ordered_connections[$connection['class']][(int)$connection['identifier_relation_id']] = $connection;
						$ordered_connections[$connection['class'] . '_count']++;
					}
				}
				else{
					if(!isset($ordered_connections[$connection['class']][$connection['registry_object_id']]))
					{
						$ordered_connections[$connection['class']][(int)$connection['registry_object_id']] = $connection;
						$ordered_connections[$connection['class'] . '_count']++;
					}
				}				

			}
		}
		/* - Handle the offsetting/limits */
		if ($limit || $offset)
		{
			foreach($ordered_connections AS $name => $list)
			{
				if (is_array($list))
				{
					$ordered_connections[$name] = array_slice($list, $offset, $limit);
					foreach($ordered_connections[$name] as &$connection){
						$connection['description'] = $this->_getDescription($connection['registry_object_id']);
						$connection['logo'] = $this->_getLogo($connection['registry_object_id']);					
					}
				}
			}
		}

		return array($ordered_connections);
	}


	function getAllRelatedObjects($allow_drafts = false, $include_dupe_connections = false, $allow_all_links = false)
	{
		$unordered_connections = array();


		$this->_CI->load->model('data_source/data_sources','ds');
		$ds = $this->_CI->ds->getByID($this->ro->data_source_id);

		$allow_reverse_internal_links = ($ds->allow_reverse_internal_links == "t" || $ds->allow_reverse_internal_links == 1);
		$allow_reverse_external_links = ($ds->allow_reverse_external_links == "t" || $ds->allow_reverse_external_links == 1);
        $create_primary_relationships = ($ds->create_primary_relationships == "t" || $ds->create_primary_relationships == 1);

		/* Step 1 - Straightforward link relationships */
		$unordered_connections = array_merge($unordered_connections, $this->_getExplicitLinks($allow_drafts));
		$unordered_connections= array_merge($unordered_connections, $this->_getIdentifierLinks());
		$unordered_connections= array_merge($unordered_connections, $this->_getReverseIdentifierLinks($allow_reverse_internal_links, $allow_reverse_external_links));
		/* Step 2 - Internal reverse links */
		if ($allow_reverse_internal_links || $allow_all_links)
		{
			$unordered_connections = array_merge($unordered_connections, $this->_getInternalReverseLinks($allow_drafts));			
		}

        if ($create_primary_relationships)
        {
            $unordered_connections = array_merge($unordered_connections, $this->_getPrimaryLinks($allow_drafts));
        }
		/* Step 3 - External reverse links */
		if ($allow_reverse_external_links || $allow_all_links)
		{
			$unordered_connections = array_merge($unordered_connections, $this->_getExternalReverseLinks($allow_drafts));
		}

		/* Step 4 - Contributor */
		$unordered_connections = array_merge($unordered_connections, $this->_getContributorLinks($allow_drafts));


		/* Step 5 - Duplicate Record connections */
		if ( $include_dupe_connections || $allow_all_links)
		{
			$unordered_connections = array_merge($unordered_connections, $this->_getDuplicateConnections());
		}

		if ( $allow_all_links ) {
			$identifierMatches = array();
			foreach($unordered_connections as $cc){
				if($cc['class']=='party'){
					$cc_ro = $this->_CI->ro->getByID($cc['registry_object_id']);
					$identifierMatches = array_merge($identifierMatches, $cc_ro->findMatchingRecords());
				}
			}
			foreach ($identifierMatches as $ii){
				$ii_ro = $this->_CI->ro->getByID($ii);

				$related_registry_object = array(
					'registry_object_id' => $ii_ro->id,
					'key' => $ii_ro->key,
					'class' => $ii_ro->class,
					'title'=>$ii_ro->title,
					'slug' => $ii_ro->slug,
					'origin' => 'IDENTIFIER_MATCH',
					'relation_type' => '(Automatically inferred link from records with matching identifiers)'
				);

				if ($ii_ro->status==PUBLISHED) {
					$unordered_connections[] = $related_registry_object;
				}

			}
		}

		return $unordered_connections;
	}

	function _getDescription($id){
		$this->db->select('value')->from('registry_object_metadata')->where('registry_object_id', $id)->where('attribute', 'the_description')->limit(1);
		$query = $this->db->get();
		foreach($query->result() as $row){
			return $row->value;
		}
	}


	function _getLogo($id){
		$this->db->select('value')->from('registry_object_metadata')->where('registry_object_id', $id)->where('attribute', 'the_logo')->limit(1);
		$query = $this->db->get();
		foreach($query->result() as $row){
			return $row->value;
		}
	}

	function _getExplicitLinks($allow_unmatched_records = false)
	{
		/* Step 1 - Straightforward link relationships */
		$my_connections = array();

		$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type, rr.relation_description,  rr.relation_url, rr.origin')
				 ->from('registry_object_relationships rr')
				 ->join('registry_objects r','rr.related_object_key = r.key', ($allow_unmatched_records ? 'left' : ''))
				 ->where('rr.registry_object_id',$this->id)
				 ->where('rr.origin','EXPLICIT');
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			if (!$row['origin'])
			{
				$row['origin'] = "EXPLICIT";
			}

			$my_connections[] = $row;
		}

		return $my_connections;
	}

	function _getIdentifierLinks()
	{
		/* Step 1 - Straightforward link relationships */
		$my_connections = array();
		$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rir.relation_type, rir.related_info_type, rir.related_title, rir.related_description as relation_description, rir.related_url as relation_url, rir.id as identifier_relation_id, rir.related_object_identifier, rir.related_object_identifier_type')
				 ->from('registry_object_identifier_relationships rir')
				 ->join('registry_object_identifiers ri','rir.related_object_identifier = ri.identifier and rir.related_object_identifier_type = ri.identifier_type','left')
				 ->join('registry_objects r','r.registry_object_id = ri.registry_object_id','left')			 
				 ->where('rir.registry_object_id',$this->id);
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			
			if($row['status'] == null || $row['status'] == 'PUBLISHED')
			{
				$row['origin'] = "IDENTIFIER";
				if($row['class'] == null) 
					$row['class'] = $row['related_info_type'];
				if($row['title'] == null) 
					$row['title'] = $row['related_title'];
				if($row['relation_type'] == null || $row['relation_type'] == '')
					$row['relation_type'] = 'hasAssociationWith';
				if($row['related_title'] != '' or $row['status'] != null)
				{
					$my_connections[] = $row;
				}
			}
		}
		return $my_connections;
	}

	function _getReverseIdentifierLinks($allow_reverse_internal_links, $allow_reverse_external_links)
	{
		/* Step 1 - Straightforward link relationships */
		$my_connections = array();
		if($allow_reverse_internal_links && $allow_reverse_external_links)
		{
			$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rir.relation_type, rir.related_info_type, rir.related_title, rir.related_description as relation_description, rir.related_url as relation_url, rir.id as identifier_relation_id, rir.related_object_identifier, rir.related_object_identifier_type')
				 ->from('registry_object_identifier_relationships rir')
				 ->join('registry_object_identifiers ri','rir.related_object_identifier = ri.identifier and rir.related_object_identifier_type = ri.identifier_type')
				 ->join('registry_objects r','rir.registry_object_id = r.registry_object_id')			 
				 ->where('ri.registry_object_id',$this->id)
				 ->where('r.status','PUBLISHED');
		}
		else if($allow_reverse_internal_links)
		{
			$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rir.relation_type, rir.related_info_type, rir.related_title, rir.related_description as relation_description, rir.related_url as relation_url, rir.id as identifier_relation_id, rir.related_object_identifier, rir.related_object_identifier_type')
				 ->from('registry_object_identifier_relationships rir')
				 ->join('registry_object_identifiers ri','rir.related_object_identifier = ri.identifier and rir.related_object_identifier_type = ri.identifier_type')
				 ->join('registry_objects r','rir.registry_object_id = r.registry_object_id')			 
				 ->where('ri.registry_object_id',$this->id)
				 ->where('r.data_source_id',$this->ro->data_source_id)
				 ->where('r.status','PUBLISHED');
		}
		else if($allow_reverse_external_links)
		{
			$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rir.relation_type, rir.related_info_type, rir.related_title, rir.related_description as relation_description, rir.related_url as relation_url, rir.id as identifier_relation_id, rir.related_object_identifier, rir.related_object_identifier_type')
				 ->from('registry_object_identifier_relationships rir')
				 ->join('registry_object_identifiers ri','rir.related_object_identifier = ri.identifier and rir.related_object_identifier_type = ri.identifier_type')
				 ->join('registry_objects r','rir.registry_object_id = r.registry_object_id')			 
				 ->where('ri.registry_object_id',$this->id)
				 ->where('r.data_source_id !=',$this->ro->data_source_id)
				 ->where('r.status','PUBLISHED');
		}
		else{
			return $my_connections;
		}
		
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			$row['origin'] = "IDENTIFIER REVERSE";
			if($row['relation_type'] == null || $row['relation_type'] == ''){
				$row['relation_type'] = 'hasAssociationWith';
			}
			$my_connections[] = $row;
		}
		return $my_connections;
	}


	function _getInternalReverseLinks($allow_unmatched_records = false)
	{
		/* Step 2 - Internal reverse links */
		$my_connections = array();

		$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type, rr.relation_description')
						 ->from('registry_object_relationships rr')
						 ->join('registry_objects r','rr.registry_object_id = r.registry_object_id', ($allow_unmatched_records ? 'left' : ''))
						 ->where('rr.related_object_key',$this->ro->key)
						 ->where('r.data_source_id',$this->ro->data_source_id)
						 ->where('rr.origin !=','PRIMARY');
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			$row['origin'] = "REVERSE_INT";
			$my_connections[] = $row;
		}

		return $my_connections;
	}

	function _getPrimaryLinks($allow_unmatched_records = false)
	{
		/* Step 2 - Internal reverse links */
		$my_connections = array();

		$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type, rr.relation_description')
						 ->from('registry_object_relationships rr')
						 ->join('registry_objects r','r.key = rr.related_object_key', ($allow_unmatched_records ? 'left' : ''))
						 ->where('rr.registry_object_id',$this->ro->id)
						 ->where('r.data_source_id',$this->ro->data_source_id)
						 ->where('rr.origin =','PRIMARY');
		$query = $this->db->get();
		
		foreach ($query->result_array() AS $row)
		{
			$row['origin'] = "PRIMARY";
			$my_connections[] = $row;
		}

		return $my_connections;
	}

	function _getExternalReverseLinks($allow_unmatched_records = false)
	{
		/* Step 3 - External reverse links */
		$my_connections = array();

		$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type, rr.relation_description')
						 ->from('registry_object_relationships rr')
						 ->join('registry_objects r','rr.registry_object_id = r.registry_object_id', ($allow_unmatched_records ? 'left' : ''))
						 ->where('rr.related_object_key',$this->ro->key)
						 ->where('r.data_source_id !=',$this->ro->data_source_id);
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			$row['origin'] = "REVERSE_EXT";

			$my_connections[] = $row;
		}

		return $my_connections;
	}

    function getRelatedObjectsByClassAndRelationshipType($classArray, $relationshipTypeArray)
    {
        $unordered_connections = array();

        $this->_CI->load->model('data_source/data_sources','ds');
        $ds = $this->_CI->ds->getByID($this->ro->data_source_id);

        $allow_reverse_internal_links = ($ds->allow_reverse_internal_links == "t" || $ds->allow_reverse_internal_links == 1);
        $allow_reverse_external_links = ($ds->allow_reverse_external_links == "t" || $ds->allow_reverse_external_links == 1);

        $unordered_connections = array_merge($unordered_connections, $this->_getExplicitLinks());

        if ($allow_reverse_internal_links)
        {
            $unordered_connections = array_merge($unordered_connections, $this->_getInternalReverseLinks());
        }
        if ($allow_reverse_external_links)
        {
            $unordered_connections = array_merge($unordered_connections, $this->_getExternalReverseLinks());
        }

        $connections = array();

        foreach($unordered_connections AS $connection)
        {
            if(in_array($connection['class'], $classArray) && in_array($connection['relation_type'], $relationshipTypeArray))
            {
                $connections[] = $connection;

            }
        }
        return $connections;

    }

	function _getContributorLinks($allow_unmatched_records = false)
	{
		/* Step 4 - Contributor */
		$my_connections = array();

		$this->db->select('r.registry_object_id, r.class, r.title, r.slug, r.status, r.key')
						 ->from('institutional_pages i')
						 ->join('registry_objects r','i.registry_object_id = r.registry_object_id')
						 ->where('i.group',$this->ro->group);
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			if ($row['registry_object_id'] != $this->ro->id)
			{
				$row['origin'] = "CONTRIBUTOR";
				$row['class'] = "contributor";
				$row['relation_type'] = "(Automatically generated contributor page link)";
				$my_connections[] = $row;
			}
		}

		return $my_connections;
	}

	function _getDuplicateConnections()
	{
		$my_connections = array();
		$relatedByIdentifiers = $this->ro->findMatchingRecords();

		foreach($relatedByIdentifiers as $r_id){
			$ro = $this->_CI->ro->getByID($r_id);

			$matches = $ro->getAllRelatedObjects();

			foreach($matches as $i=>&$match){
				if ($match['origin']!='CONTRIBUTOR') {
					$match['origin'] = 'IDENTIFIER_MATCH';
					$match['relation_type'] = '(Automatically inferred link from records with matching identifiers)';
				} else {
					unset($matches[$i]);
				}
			}

			$my_connections = array_merge($my_connections, $matches);
		}
		return $my_connections;
	}

	
}