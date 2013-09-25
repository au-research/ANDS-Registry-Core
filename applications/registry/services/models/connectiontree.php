<?php

class ConnectionTree extends CI_Model
{
	public $parent_relationships = array("isPartOf");
	public $recursed_parents = array(); // used to stop circular recursion
	public $recursed_children = array(); // a map of all children already in the tree
	public $child_relationships = array("hasPart");
	public $default_relation_type = "isRootElementOf";
	public $max_width = 10;
	public $published_only = TRUE;
	public $root_ro_key;
	public $collections_only = true;


	function get($root_registry_object, $depth, $published_only, $target_id = null)
	{
		$this->published_only = $published_only;

		if ($root_registry_object)
		{
			$this->root_ro_key = $root_registry_object->key;
			$this->recursed_children[$root_registry_object->id] = true;
			$relationship_tree = $this->getChildren($root_registry_object->id, $depth, array($root_registry_object->id => true), $target_id);

			$relationship_tree = array(
				//"id" => $root_registry_object->id,
				"title" => $root_registry_object->title,

				"registry_object_id"=>$root_registry_object->id,
				"class"=>$root_registry_object->class,
				"slug"=>$root_registry_object->slug,
				"status"=>$root_registry_object->status, 
				"relation_type"=>$root_registry_object->relation_type,

				"children" => $relationship_tree
			);
			return $relationship_tree;
		}
		else
		{
			return array();
		}
	}

	function formatMappingForGoogleCharts($mappings, $root_registry_object)
	{
		foreach($mappings AS &$map)
		{
			$key = ($this->published_only ? $map[1]['slug'] : $map[1]['registry_object_id']);
			$link = ($this->published_only ? $map[1]['slug'] : "view/?id=" . $map[1]['registry_object_id']);
			$map = 	array( 
							"title"=> $map[1]['title'],
							$map[0],
							"",
							$link
					);
		}
		// Add in the root element
		$link = ($this->published_only ? $root_registry_object->slug : "view/?id=" . $root_registry_object->id);
		array_unshift($mappings, array(array("v"=>($this->published_only ? $root_registry_object->slug : $root_registry_object->id), 
												"f"=>$this->formatNodeForGoogleCharts($root_registry_object)), "", "", $link));
		return $mappings;
	}

	function formatNodeForGoogleCharts($node)
	{
		$key = ($this->published_only ? $node->slug : $node->id);

		base_url();

		$html = $node->title;

		return $html;
	}

	function getParentMapping($root, $tree)
	{
		$relationships = array();

		foreach ($tree AS $branch)
		{
			if (isset($branch['children']) && is_array($branch['children']))
			{
				
				$relationships = array_merge($relationships, $this->getParentMapping($branch, $branch['children']));
				unset($branch['children']);
			}
			
			$relationships[] = array( ($this->published_only ? $root['slug'] : $root['id']), $branch);
			
			
		}
		return $relationships;
	}


	// Traverse up the database tree to find our parent
	public function getImmediateAncestors($child_registry_object, $published_only)
	{
		$immediateAncestors = array();
		$this->published_only = $published_only;

		if (!isset($child_registry_object->id)) { return array(); }

		/* Explicit relationships (i.e. `a` isPartOf `b`) */
		$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type')
						 ->from('registry_object_relationships rr')
						 ->join('registry_objects r','rr.related_object_key = r.key')
						 ->where('rr.registry_object_id',$child_registry_object->id)
						 ->where_in('rr.relation_type',$this->parent_relationships);
		if ($this->published_only) { $this->db->where('r.status', PUBLISHED); }
		if ($this->collections_only) { $this->db->where('r.class', 'collection'); }
		$query = $this->db->get();
		foreach ($query->result_array() AS $row)
		{
			$immediateAncestors[] = $row;
		}

		/* Inverse relationships (i.e. `b` hasPart `a`) */
		$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type')
						 ->from('registry_object_relationships rr')
						 ->join('registry_objects r','rr.registry_object_id = r.registry_object_id')
						 ->where('rr.related_object_key',$child_registry_object->key)
						 ->where_in('rr.relation_type',$this->child_relationships);

		if ($this->published_only) { $this->db->where('r.status', PUBLISHED); }
		if ($this->collections_only) { $this->db->where('r.class', 'collection'); }

		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{
			$immediateAncestors[] = $row;
		}

		return $immediateAncestors;

	}

	// Traverse up the database tree to find our parent
	public function getRootAncestor($root_registry_object, $published_only, &$traversed_ancestors = array())
	{
		$this->published_only = $published_only;
		$traversed_ancestors[] = $root_registry_object->id;

		$ancestors = $this->getImmediateAncestors($root_registry_object, $published_only);
		//print_pre($root_registry_object->slug); print_pre($ancestors);
		if (count($ancestors) > 0)
		{
			// We arbitrarily just select the first ancestor if there are many
			$this_registry_object = $this->ro->getPublishedByKey($ancestors[0]['key']);
			if (!$this_registry_object && !$published_only)
			{
				$this_registry_object = $this->ro->getDraftByKey($ancestors[0]['key']);
			}

			$traversed_ancestors[] = $this_registry_object->id;

			// prevent infinite recursion
			if (!isset($this->recursed_parents[$this_registry_object->id]))
			{
				$this->recursed_parents[$this_registry_object->id] = true;
				return $this->getRootAncestor($this_registry_object, $published_only, $traversed_ancestors);
			}
			else
			{
				return $root_registry_object;
			}
		}
		else
		{
			return $root_registry_object;
		}
	}


	function getChildren($root_registry_object_id, $depth, $accumulated_ids = array(), $target_id = null)
	{
		$my_children = array();
		$overflow_ids = array();
		$more = 0;
		$depth--;

		$this->load->model('registry_object/registry_objects','ro');
		$root_registry_object = $this->ro->getByID($root_registry_object_id);

		if (!$root_registry_object) { return array(); }

		/* Explicit relationships (i.e. `a` hasPart `b`) */
		$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type')
						 ->from('registry_object_relationships rr')
						 ->join('registry_objects r','rr.related_object_key = r.key')
						 ->where('rr.registry_object_id',$root_registry_object->id)
						 ->where_in('rr.relation_type',$this->child_relationships);
		if ($this->published_only) 
		{
			$this->db->where('r.status', PUBLISHED);
		}
		if ($this->collections_only) { $this->db->where('r.class', 'collection'); }
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{

			if ($depth > 0 && !isset($accumulated_ids[$row['registry_object_id']]))
			{
				// If we're over the widget limit (and this isn't the target RO), then add more...
				if (count($my_children) >= $this->max_width) 
				{
					if ($row['registry_object_id'] != $target_id)
					{
						$overflow_ids[] = $row['registry_object_id'];
						continue;
					}
				}

				$row['children'] = $this->getChildren($row['registry_object_id'], $depth, $accumulated_ids, $target_id);
				$accumulated_ids[$row['registry_object_id']] = true;
				
				$my_children[] = array(
					//"id"=>$row['registry_object_id'],
					"title"=>$row['title'],
					"registry_object_id"=>$row['registry_object_id'],
					"class"=>$row['class'],
					"slug"=>$row['slug'],
					"status"=>$row['status'],
					"relation_type"=>$row['relation_type'],
					"children" => $row['children']
				);
			}
			
		}
		$query->free_result();

		/* Inverse relationships (i.e. `b` isPartOf `a`) */
		$this->db->select('r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type')
						 ->from('registry_object_relationships rr')
						 ->join('registry_objects r','rr.registry_object_id = r.registry_object_id')
						 ->where('rr.related_object_key',$root_registry_object->key)
						 ->where_in('rr.relation_type',$this->parent_relationships);
		if ($this->published_only) 
		{
			$this->db->where('r.status', PUBLISHED);
		}
		if ($this->collections_only) { $this->db->where('r.class', 'collection'); }
		$query = $this->db->get();

		foreach ($query->result_array() AS $row)
		{

			if ($depth > 0 && !isset($accumulated_ids[$row['registry_object_id']]))
			{
				// If we're over the widget limit (and this isn't the target RO), then add more...
				if (count($my_children) >= $this->max_width) 
				{
					if ($row['registry_object_id'] != $target_id)
					{
						$overflow_ids[] = $row['registry_object_id'];
						continue;
					}
				}

				$row['children'] = $this->getChildren($row['registry_object_id'], $depth, $accumulated_ids, $target_id);
				$accumulated_ids[$row['registry_object_id']] = true;

				$my_children[] = array(
					//"id"=>$row['registry_object_id'],
					"title"=>$row['title'],
					"registry_object_id"=>$row['registry_object_id'],
					"class"=>$row['class'],
					"slug"=>$row['slug'],
					"status"=>$row['status'],
					"relation_type"=>$row['relation_type'],
					"children" => $row['children']
				);
			}
		}

		$overflow_ids = array_unique($overflow_ids);
		if (count($overflow_ids) > 0)
		{
			$my_children[] = array(
					"title"=> "<i>+ " . count($overflow_ids) . " more collection".(count($overflow_ids) != 1 ? "s" : "") ."...</i>",
					"registry_object_id"=>$root_registry_object->id,
					"class"=>"more",
					"slug"=>$root_registry_object->slug,
					"status"=>$root_registry_object->status,
					"relation_type"=>null,
					"children" => array()
				);
		}

		foreach ($accumulated_ids AS $i => $_)
		{
			$this->recursed_children[$i] = true;
		}

		return $my_children;
	}


}