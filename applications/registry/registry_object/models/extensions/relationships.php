<?php


class Relationships_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	function addRelationships()
	{

		// Delete any old relationships (we only run this on ingest, so its a once-off update)
		$this->db->where(array('registry_object_id' => $this->ro->id));
		$this->db->delete('registry_object_relationships');	
		$sxml = $this->ro->getSimpleXml();

		/* Explicit relationships */
		$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
		$explicit_keys = array();

		foreach ($sxml->xpath('//ro:relatedObject') AS $related_object)
		{
			$related_object_key = (string)$related_object->key;
			$related_object_type = (string)$related_object->relation[0]['type'];
			$related_object_relation_description = (string)$related_object->relation[0]->description;
			$related_object_relation_url = (string)$related_object->relation[0]->url;

			$result = $this->db->select('class, title')->get_where('registry_objects', array('key'=>(string)$related_object_key));
			
			$class = NULL;
			$title = 'no title';

			if ($result->num_rows() > 0)
			{
				$record = $result->result_array();
				$record = array_shift($record);
				$result->free_result();
				$class = $record['class'];
				$title = $record['title'];
			}
			
			$explicit_keys[] = (string) $related_object_key;

			$this->db->insert('registry_object_relationships', 
				array(
						"registry_object_id"=>$this->ro->id, 
						"related_object_key" => (string) $related_object_key,
						'related_object_class'=> (string) $class,
						"relation_type" => (string) $related_object_type,
						"relation_description" => (string) $related_object_relation_description,
						"relation_url" => (string) $related_object_relation_url,
				)
			);
		}

		$processedTypesArray = array('collection','party','service','activity');
		$this->db->where(array('registry_object_id' => $this->ro->id));
		$this->db->delete('registry_object_identifier_relationships');	
		foreach ($sxml->xpath('//ro:relatedInfo') AS $related_info)
		{
			
			$related_info_type = (string)$related_info['type'];
			if(in_array($related_info_type, $processedTypesArray))
			{
				$related_info_title = (string)$related_info->title;
				$relation_type = "";
				$related_description = "";
				$related_url = "";
				$relation_type_disp = "";
				$connections_preview_div = "";
				if($related_info->relation){
					foreach($related_info->relation as $r)
					{
						$relation_type .= (string)$r['type'].", ";
						$relation_type_disp .= format_relationship($this->ro->class, (string)$r['type'], 'IDENTIFIER').", ";
						$relateddescription = (string)$r->description."<br/>";
						if($related_url == '' && (string)$r->url != ''){
							$related_url = (string)$r->url;
						}
						$urlStr = trim((string)$r->url);
						if((string)$r->description != '' && (string)$r->url != '')
						{
							$connections_preview_div .= "<div class='description'><p>".(string)$r->description.'<br/><a href="'.$urlStr.'">'.(string)$r->url."</a></p></div>";
						}
					}
					$relation_type = substr($relation_type, 0, strlen($relation_type)-2);
					$relation_type_disp = substr($relation_type_disp, 0, strlen($relation_type_disp)-2);
					//$connections_preview_div .= '<p>('.$relation_type.')</p>';
				}
				$identifiers_div = "";
				$identifier_count = 0;
				foreach($related_info->identifier as $i)
				{
					$identifiers_div .= $this->getResolvedLinkForIdentifier((string)$i['type'],trim((string)$i));  	
					$identifier_count++;
				}
				$identifiers_div = "<h5>Identifier".($identifier_count > 1 ? 's' : '').": </h5>".$identifiers_div;
				if($related_info->notes){
					$connections_preview_div .= '<p>Notes: '.(string)$related_info->notes.'</p>';
				}
			    $imgUrl = asset_url('img/'.$related_info_type.'.png', 'base');
			    $classImg = '<img class="icon-heading" src="'.$imgUrl.'" alt="'.$related_info_type.'" style="width:24px; float:right;">';
				$connections_preview_div = '<div class="previewItemHeader">'.$relation_type_disp.'</div>'.$classImg.'<h4>'.$related_info_title.'</h4><div class="post">'.$identifiers_div."<br/>".$connections_preview_div.'</div>';
								
				foreach($related_info->identifier as $i)
				{
					$this->db->insert('registry_object_identifier_relationships', 
						array(
							"registry_object_id"=>$this->ro->id, 
						  	"related_object_identifier"=>trim((string)$i),
						  	"related_info_type"=>$related_info_type ,
						  	"related_object_identifier_type"=>(string)$i['type'],
						  	"relation_type"=>$relation_type,
						  	"related_title"=>$related_info_title,
						  	"related_description"=>$related_description,
						  	"related_url"=>$related_url,
						  	"connections_preview_div"=>$connections_preview_div
						)
					);
				}
			}			
		}

		/* Create primary relationships links */
		$this->_CI->load->model('registry/data_source/data_sources', 'ds');
		$ds = $this->_CI->ds->getByID($this->ro->data_source_id);

		if ($ds->create_primary_relationships == DB_TRUE && $ds->primary_key_1 && $ds->primary_key_1 != $this->ro->key && !in_array($ds->primary_key_1, $explicit_keys))
		{
			$this_relationship = $ds->{strtolower($this->ro->class) . "_rel_1"};
			$this->db->insert('registry_object_relationships', 
				array(
						"registry_object_id"=>$this->ro->id, 
						"related_object_key" => (string) $ds->primary_key_1,
						'related_object_class'=> (string) $ds->class_1,
						"relation_type" => (string) $this_relationship,
						"origin" => PRIMARY_RELATIONSHIP
				)
			);
		}

		if ($ds->create_primary_relationships == DB_TRUE && $ds->primary_key_2 && $ds->primary_key_2 != $this->ro->key && !in_array($ds->primary_key_2, $explicit_keys))
		{
			$this_relationship = $ds->{strtolower($this->ro->class) . "_rel_2"};
			$this->db->insert('registry_object_relationships', 
				array(
						"registry_object_id"=>$this->ro->id, 
						"related_object_key" => (string) $ds->primary_key_2,
						'related_object_class'=> (string) $ds->class_2,
						"relation_type" => (string) $this_relationship,
						"origin" => PRIMARY_RELATIONSHIP
				)
			);
		}

		return $explicit_keys;
	}

	function getRelationships()
	{
		$related_keys = array();
		$result = $this->db->select('related_object_key')->get_where('registry_object_relationships', array('registry_object_id'=>(string)$this->ro->id));
		foreach ($result->result_array() AS $row)
		{
			$related_keys[] = $row['related_object_key'];
		}
		return $related_keys;
	}


	function getRelatedObjectsByIdentifier()
	{
		$my_connections = array();
		$this->db->select('r.title, r.registry_object_id as related_id, r.class as class, rir.*')
				 ->from('registry_object_identifier_relationships rir')
				 ->join('registry_object_identifiers ri','ri.identifier = rir.related_object_identifier and ri.identifier_type = rir.related_object_identifier_type','left')
				 ->join('registry_objects r','r.registry_object_id = ri.registry_object_id','left')			 
				 ->where('rir.registry_object_id',(string)$this->ro->id)
				 ->where('r.status','PUBLISHED');
		$query = $this->db->get();
		foreach ($query->result_array() AS $row)
		{
			$my_connections[] = $row;
		}

		return $my_connections;
	}

	function getRelatedObjects()
	{
		$my_connections = array();
		$this->db->select('r.title, r.registry_object_id as related_id, r.class as class, rr.*')
				 ->from('registry_object_relationships rr')
				 ->join('registry_objects r','rr.related_object_key = r.key','left')
				 ->where('rr.registry_object_id',(string)$this->ro->id)
				 ->where('r.status','PUBLISHED');
		$query = $this->db->get();
		foreach ($query->result_array() AS $row)
		{
			$my_connections[] = $row;
		}

		return $my_connections;
	}
	
	function getRelatedClasses()
	{
		/* Holy crap! Use getConnections to infer relationships to drafts and reverse links :-))) */
		$classes = array();
		$connections = $this->ro->getConnections(false);
		$connections = array_pop($connections);
		if (isset($connections['activity']))
		{
			$classes[] = "Activity";
		}
		if (isset($connections['collection']))
		{
			$classes[] = "Collection";
		}
		if (isset($connections['party']) || isset($connections['party_one']) || isset($connections['party_multi']) || isset($connections['contributor']))
		{
			$classes[] = "Party";
		}
		if (isset($connections['service']))
		{
			$classes[] = "Service";
		}

		return $classes;
	}
	
	function getRelatedClassesString()
	{
		$classes = "";
		$list = $this->getRelatedClasses();
		return implode($list);
	}

	function getResolvedLinkForIdentifier($type, $value)
	{
		
		$urlValue = $value;
		switch ($type){
			case 'handle':
				if (strpos($value,'http://hdl.handle.net/') === false){
    				$urlValue = 'http://hdl.handle.net/'.$value;
				}
		        return 'Handle : <a class="identifier" href="'.$urlValue.'" title="Resolve this handle">'.$value.'<img class="identifier_logo" src="'.asset_url('assets/core/images/icons/handle_icon.png', 'base_path').'" alt="Handle icon"></a><br/>';
		        break;
			case 'purl':
				if (strpos($value,'http://purl.org/') === false){
    				$urlValue = 'http://purl.org/'.$value;
				}
		        return 'PURL : <a class="identifier" href="'.$urlValue.'" title="Resolve this purl identifier">'.$value.'<img class="identifier_logo" src="'.asset_url('assets/core/images/icons/external_link.png', 'base_path').'" alt="PURL icon"></a><br/>';
		        break;
		    case 'doi':
		    	if (strpos($value,'http://dx.doi.org/') === false){
    				$urlValue = 'http://dx.doi.org/'.$value;
				}
		        return 'DOI: <a class="identifier" href="'.$urlValue.'" title="Resolve this DOI">'.$value.'<img class="identifier_logo" src="'.asset_url('assets/core/images/icons/doi_icon.png', 'base_path').'" alt="DOI icon"></a><br/>';
		        break;
		    case 'uri':
		    	if (strpos($value,'http://') === false && strpos($value,'https://') === false){
    				$urlValue = 'http://'.$value;
				}
		        return 'URI : <a class="identifier" href="'.$urlValue.'" title="Resolve this URI">'.$value.'<img class="identifier_logo" src="'.asset_url('assets/core/images/icons/external_link.png', 'base_path').'" alt="URI icon"></a><br/>';
		        break;
		    case 'urn':
		        return 'URN : <a class="identifier" href="'.$value.'" title="Resolve this URN">'.$value.'<img class="identifier_logo" src="'.asset_url('assets/core/images/icons/external_link.png', 'base_path').'" alt="URI icon"></a><br/>';
		        break;
		    case 'orcid':
		    	if (strpos($value,'http://orcid.org/') === false){
    				$urlValue = 'http://orcid.org/'.$value;
				}
		        return 'ORCID: <a class="identifier" href="'.$urlValue.'" title="Resolve this ORCID">'.$value.'<img class="identifier_logo" src="'.asset_url('assets/core/images/icons/orcid_icon.png', 'base_path').'" alt="ORCID icon"></a><br/>';
		        break;
		    case 'AU-ANL:PEAU':
		    	if (strpos($value,'http://nla.gov.au/') === false){
    				$urlValue = 'http://nla.gov.au/'.$value;
				}
		        return 'NLA: <a class="identifier" href="'.$urlValue.'" title="View the record for this party in Trove">'.$value.'<img class="identifier_logo" src="'.asset_url('assets/core/images/icons/nla_icon.png', 'base_path').'" alt="NLA icon"></a><br/>';
		        break;
		    case 'local':
				return "Local: ".$value."<br/>";
		        break;
		    default:
		       return strtoupper($type).": ".$value."<br/>";
		}


	}
}