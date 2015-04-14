<?php
class Extrif_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
		include_once("applications/registry/registry_object/models/_transforms.php");
	}		
	
	/*
	 * 	Extrif
	 */
	function enrich($runBenchMark = false)
	{
		$this->_CI->load->model('data_source/data_sources','ds');	
		$this->_CI->load->library('purifier');
		// Save ourselves some computation by avoiding creating the whole $ds object for 
		$ds = $this->_CI->ds->getByID($this->ro->data_source_id);

		//same as in relationships.php
		$xml = $this->ro->getSimpleXML();

		// Reset our namespace object (And go down one level from the wrapper if needed)
		$xml =  addXMLDeclarationUTF8(($xml->registryObject ? $xml->registryObject->asXML() : $xml->asXML()));

		$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOENT);

		// Clone across the namespace (if applicable)
		$namespaces = $xml->getNamespaces(true);
		if ( !in_array(RIFCS_NAMESPACE, $namespaces) )
		{    
			$xml->addAttribute("xmlns",RIFCS_NAMESPACE);
		}

		$xml = simplexml_load_string(addXMLDeclarationUTF8($xml->asXML()), 'SimpleXMLElement', LIBXML_NOENT);
		// Cannot enrich already enriched RIFCS!!
		if(true)//!isset($rifNS[EXTRIF_NAMESPACE])) //! (string) $attributes['enriched'])//! (string) $attributes['enriched'])
		{
			$xml->addAttribute("extRif:enriched","true",EXTRIF_NAMESPACE);
			if (count($xml->key) == 1)
			{
				/* EXTENDED METADATA CONTAINER */
				$contributor = $this->getContributorExists($this->ro->id);
				$extendedMetadata = $xml->addChild("extRif:extendedMetadata", NULL, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:slug", $this->ro->slug, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:dataSourceKey", $ds->key, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:status", $this->ro->status, EXTRIF_NAMESPACE);				
				$extendedMetadata->addChild("extRif:id", $this->ro->id, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:dataSourceTitle", $ds->title, EXTRIF_NAMESPACE);				
				$extendedMetadata->addChild("extRif:dataSourceID", $this->ro->data_source_id, EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:updateTimestamp", $this->ro->updated, EXTRIF_NAMESPACE);					
	
				$extendedMetadata->addChild("extRif:displayTitle", str_replace("&", "&amp;", $this->ro->title), EXTRIF_NAMESPACE);
				$extendedMetadata->addChild("extRif:listTitle", str_replace("&", "&amp;", $this->ro->list_title), EXTRIF_NAMESPACE);
				try{
					$extendedMetadata->addChild("extRif:simplifiedTitle", iconv('UTF-8', 'ASCII//TRANSLIT', str_replace("&", "&amp;", $this->ro->list_title)), EXTRIF_NAMESPACE);
				}catch(Exception $e){
					throw new Exception ('iconv installation/configuration required for simplified title <br/>'.$e);
				}

				$is_contributor_page = false;
				if($contributor)
				{
					$extendedMetadata->addChild("extRif:contributor", htmlspecialchars_decode($contributor[0]), EXTRIF_NAMESPACE);

					// also mark whether this is a contributor page (used for boosting later)
					if ($contributor[0] == $this->ro->slug) { $is_contributor_page = true; }
				}
				$theDescription = '';
				$theDescriptionType = '';
				
				if($runBenchMark) $this->_CI->benchmark->mark('ro_enrich_s1_end');
				
				if($xml->{$this->ro->class}->description)
				{
					$logoAdded = false;
					foreach ($xml->{$this->ro->class}->description AS $description)
					{					
						$type = (string) $description['type'];
						$description_str = (string) $description;

						//add logo to the extrif
						if($type=='logo' && !$logoAdded){
							$logoAdded = true;
							$logoRef = $this->getLogoUrl($description);
							$extendedMetadata->addChild("extrif:logo", $logoRef, EXTRIF_NAMESPACE);
							$this->ro->set_metadata('the_logo', $logoRef);
						}
						if($type == 'brief' && $theDescriptionType != 'brief')
						{
							$theDescription = $description_str;
							$theDescriptionType = $type;
						}
						else if($type == 'full' && ($theDescriptionType != 'brief' || $theDescriptionType != 'full'))
						{
							$theDescription = $description_str;
							$theDescriptionType = $type;
						}
						else if($type != '' && $theDescriptionType == '')
						{
							$theDescription = $description_str;
							$theDescriptionType = $type;
						}
						else if($theDescription == '')
						{
							$theDescription = $description_str;
							$theDescriptionType = $type;
						}
					}
					$theDescription = htmlentities(strip_tags(html_entity_decode($theDescription)));
                    $extendedMetadata->addChild("extRif:dci_description", str_replace("&", "&amp;", $theDescription), EXTRIF_NAMESPACE);

				}
				
				if($runBenchMark) $this->_CI->benchmark->mark('ro_enrich_s2_end');
				
				$subjects = $extendedMetadata->addChild("extRif:subjects", NULL, EXTRIF_NAMESPACE);
				
				foreach ($this->ro->processSubjects() AS $subject)
				{
					$subject_node = $subjects->addChild("extRif:subject", "", EXTRIF_NAMESPACE);
					$subject_node->addChild("extRif:subject_value", str_replace("&", "&amp;", $subject['value']), EXTRIF_NAMESPACE);
					$subject_node->addChild("extRif:subject_type", str_replace("&", "&amp;", $subject['type']), EXTRIF_NAMESPACE);
					$subject_node->addChild("extRif:subject_resolved", str_replace("&", "&amp;", $subject['resolved']), EXTRIF_NAMESPACE);
					$subject_node->addChild("extRif:subject_uri", str_replace("&", "&amp;", $subject['uri']), EXTRIF_NAMESPACE);
				}
				
				if($runBenchMark) $this->_CI->benchmark->mark('ro_enrich_s3_end');
	
				foreach ($this->ro->processLicence() AS $right)
				{
					$theright = $extendedMetadata->addChild("extRif:right", str_replace("&", "&amp;", $right['value']), EXTRIF_NAMESPACE);
					$theright->addAttribute("type", $right['type']);
                    if (isset($right['accessRights_type'])) {
                        $theright->addAttribute("accessRights_type", $right['accessRights_type']);
                    }
					if(isset($right['rightsUri']))$theright->addAttribute("rightsUri", str_replace("&", "&amp;", $right['rightsUri']));
					if(isset($right['licence_type']))$theright->addAttribute("licence_type", str_replace("&", "&amp;", $right['licence_type']));
					if(isset($right['licence_group']))$theright->addAttribute("licence_group", str_replace("&", "&amp;", $right['licence_group']));
				}

				// Include the count of any linked records based on identifier matches
				if($this->ro->class!='collection') $extendedMetadata->addChild("extRif:matching_identifier_count", sizeof($this->ro->findMatchingRecords()), EXTRIF_NAMESPACE);

				//ANNOTATIONS
				$annotations = $extendedMetadata->addChild("extRif:annotations", NULL, EXTRIF_NAMESPACE);

				//tags
				if($tags = $this->ro->getTags()){
					$extRifTags = $annotations->addChild('extRif:tags', NULL, EXTRIF_NAMESPACE);

					foreach($tags as $tag){
						$tag_tag = $extRifTags->addChild('extRif:tag', str_replace("&", "&amp;", $tag['name']) , EXTRIF_NAMESPACE);
						$tag_tag->addAttribute('type', $tag['type']);
					}
				}

				//Theme Page stuff
				if($own_themepages = $this->ro->getThemePages()){
					foreach($own_themepages as $t){
						$extendedMetadata->addChild("extRif:theme_page", $t['slug'], EXTRIF_NAMESPACE);
					}
				}
				
				// xxx: spatial extents (sanity checking?)
				if($runBenchMark) $this->_CI->benchmark->mark('ro_enrich_s4_end');
				// NO spatial in extrif
				if($runBenchMark) $this->_CI->benchmark->mark('ro_enrich_s5_end');
				// NO temporal in extrif
				
				if($runBenchMark) $this->_CI->benchmark->mark('ro_enrich_s6_end');

				// Friendlify dates =)
				$xml = $this->ro->extractDatesForDisplay($xml);

//				$allRelatedObjects = array();
				/* 
				Add some logic to boost highly connected records & contributor pages
				*/
				if($is_contributor_page)
				{
					$this->ro->search_boost = SEARCH_BOOST_CONTRIBUTOR_PAGE;
				}
//				elseif (count($allRelatedObjects) > 0)
//				{
//					// Give credit to "highly connected" records (but limit to 10)
//					$this->ro->search_boost = min(pow(SEARCH_BOOST_PER_RELATION_EXP,count($allRelatedObjects)), SEARCH_BOOST_RELATION_MAX);
//				}

				/* Names EXTRIF */
				//$descriptions = $xml->xpath('//'.$this->ro->class.'/description');
				
				//$ds->append_log(var_export($xml->asXML(), true));
				$this->ro->pruneExtrif();
				$this->ro->updateXML($xml->asXML(),TRUE,'extrif');
                if($this->ro->status == PUBLISHED){
                    $this->ro->processLinks();
                }

				//return $this;
			}
			else
			{
				throw new Exception ("Unable to enrich RIFCS. Not valid RIFCS XML");
			}
		}
	}

	function updateExtRif(){
		$this->_CI->load->model('data_source/data_sources','ds');

		$options = array(
			'single_values' => true,
			'theme_pages' => true,
			'tags' => true,
			'subjects' => true,
			'relationships' => true
		);

		$ds = $this->_CI->ds->getByID($this->ro->data_source_id);

		$extRif = $this->ro->getSimpleXML(null, true);
		$namespaces = $extRif->getNameSpaces();
		$extRifNameSpace = $namespaces['extRif'];

		$ext = $extRif->children($extRifNameSpace);

		if ($options['single_values']) {
			$ext->extendedMetadata->slug = $this->ro->slug;
			$ext->extendedMetadata->dataSourceKey = $ds->key;
			$ext->extendedMetadata->status = $this->ro->status;
			$ext->extendedMetadata->id = $this->ro->id;
			$ext->extendedMetadata->dataSourceTitle = $ds->title;
			$ext->extendedMetadata->dataSourceID = $this->ro->data_source_id;
			$ext->extendedMetadata->updateTimestamp = $this->ro->updated;
			$ext->extendedMetadata->displayTitle = str_replace('&', '&amp;' , $this->ro->title);
			$ext->extendedMetadata->listTitle = str_replace('&', '&amp;' , $this->ro->list_title);
			try{
				$ext->extendedMetadata->simplifiedTitle = iconv('UTF-8', 'ASCII//IGNORE', str_replace('&', '&amp;' , $this->ro->list_title));
			}catch(Exception $e){
				throw new Exception ('iconv installation/configuration required for simplified title');
			}
			$ext->extendedMetadata->matching_identifier_count = sizeof($this->ro->findMatchingRecords());
		}

		if ($options['theme_pages']) {
			unset($ext->extendedMetadata->theme_page);
			if($own_themepages = $this->ro->getThemePages()){
				foreach($own_themepages as $t){
					$ext->extendedMetadata->theme_page[] = $t['slug'];
				}
			}
		}

		if ($options['tags']) {
			if($tags = $this->ro->getTags()) {
				unset($ext->extendedMetadata->annotations);
				$ext->extendedMetadata->addChild('annotations', null, EXTRIF_NAMESPACE);
				$ext->extendedMetadata->annotations->addChild('tags', null, EXTRIF_NAMESPACE);
				foreach($tags as $tag) {
					$tag_node = $ext->extendedMetadata->annotations->tags->addChild('extRif:tag', str_replace('&', '&amp;' , $tag['name']), EXTRIF_NAMESPACE);
					$tag_node->addAttribute('type', $tag['type']);
				}
			}
		}

		if ($options['subjects']) {
			if($subjects = $this->ro->processSubjects()){
				unset($ext->extendedMetadata->subjects);
				$ext->extendedMetadata->addChild('subjects');
				foreach ($subjects AS $subject) {
					$subject_node = $ext->extendedMetadata->subjects->addChild("extRif:subject", "", EXTRIF_NAMESPACE);
					$subject_node->addChild("extRif:subject_value", $subject['value'], EXTRIF_NAMESPACE);
					$subject_node->addChild("extRif:subject_type", $subject['type'], EXTRIF_NAMESPACE);
					$subject_node->addChild("extRif:subject_resolved", $subject['resolved'], EXTRIF_NAMESPACE);
					$subject_node->addChild("extRif:subject_uri", $subject['uri'], EXTRIF_NAMESPACE);
				}
			}
		}

		if ($options['relationships']) {
			$allRelatedObjects = $this->ro->getAllRelatedObjects(false, true, true);
			unset($ext->extendedMetadata->related_object);
			foreach ($allRelatedObjects AS $relatedObject) {
				$relatedObj = $ext->extendedMetadata->addChild("extRif:related_object", NULL, EXTRIF_NAMESPACE);
				$relatedObj->addChild("extRif:related_object_key", $relatedObject['key'], EXTRIF_NAMESPACE);
				$relatedObj->addChild("extRif:related_object_id", $relatedObject['registry_object_id'], EXTRIF_NAMESPACE);
				$relatedObj->addChild("extRif:related_object_class", $relatedObject['class'], EXTRIF_NAMESPACE);
				$relatedObj->addChild("extRif:related_object_display_title", str_replace('&', '&amp;' , $relatedObject['title']), EXTRIF_NAMESPACE);
				$relatedObj->addChild("extRif:related_object_relation", $relatedObject['relation_type'], EXTRIF_NAMESPACE);
			}
		}

		$this->ro->pruneExtrif();
		$this->ro->updateXML($extRif->asXML(),TRUE,'extrif');
	}
	
	function getLogoUrl($str)
	{
		$urlStr = '';
		if(preg_match('%(https?://[^\s^"^\'^&]+|[^\/\s^"^\'^&]+www\.[^\s^"^\'^&]+)%', $str, $url)) 
			$urlStr = $url[0];
		return $urlStr;    
	}	
	function getReverseLinksStatusforEXTRIF($ds) 
	{
		$reverseLinks = 'NONE';
		if($ds->allow_reverse_internal_links == DB_TRUE && $ds->allow_reverse_external_links == DB_TRUE)
		{
			$reverseLinks = 'BOTH';
		}
		else if($ds->allow_reverse_internal_links == DB_TRUE)
		{
			$reverseLinks = 'INT';

		}
		else if($ds->allow_reverse_external_links == DB_TRUE)
		{
			$reverseLinks = 'EXT';
		}
		return $reverseLinks;
	}

	function getContributorExists($ro_id)
	{

		// Get the RO instance for this registry object so we can fetch its contributor datat
		//$this->load->model('registry_object/registry_objects', 'ro');

		$registry_object = $this->_CI->ro->getByID($ro_id);
	
		$contributor_details = array();

		if (!$registry_object)
		{
			throw new Exception("Unable to fetch contributor data registry object.");
		}
		
		$contributor = $this->_CI->db->get_where('institutional_pages',array('group' => $registry_object->getAttribute('group')));

		if ($contributor->num_rows() >0)
		{
				$row = $contributor->row_array();
				$contributor_object = $this->_CI->ro->getByID($row['registry_object_id']);
				if($contributor_object && $contributor_object->getAttribute('status')==PUBLISHED)
				{
					$contributor_details[0] = $contributor_object->getAttribute('slug');
					$contributor_details[1] = $registry_object->getAttribute('group');
					return $contributor_details;
				}
		}
		return false;
	}

	function isHtml($string)
	{
     	/* preg_match("/<\/?\w+((\s+\w+(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/",$string, $matches);
     	if(count($matches)==0){
        	//return FALSE;
      	}else{
         	return TRUE;
      	} */
      	if(str_replace("&gt;","",$string)!=$string || str_replace("&lt;","",$string)!=$string || str_replace("<","",$string)!=$string || str_replace(">","",$string)!=$string || str_replace("& ","",$string)!=$string )
      	{
      		return true;
      	}else{
      		return false;
      	}
    }
}