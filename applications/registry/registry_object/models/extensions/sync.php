<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sync_extension extends ExtensionBase{

    private $party_one_types = array('person','administrativePosition');
    private $party_multi_types = array('group');


	function __construct($ro_pointer){
		parent::__construct($ro_pointer);
	}

	/**
	 * Do an enrich and commit, if full is provided, do add relationships and update quality metadata as well
	 * With great power comes great responsibility
	 * @param boolean $full determine whether to do addRelationships() and update quality metadata
	 * @return boolean/string [if it's a string, it's an error message]
	 */
	function sync($full = true, $conn_limit=20){
		try {
			$this->_CI->load->library('solr');
			if($full){
				$this->ro->processIdentifiers();
				$this->ro->addRelationships();
				$this->ro->update_quality_metadata();
			}
			$this->ro->enrich();
			if($this->ro->status=='PUBLISHED'){
				$docs = array();
				$docs[] = $this->indexable_json($conn_limit);
				$r = $this->_CI->solr->add_json(json_encode($docs));
				$r = $this->_CI->solr->commit();
			}
			$this->_dropCache();
		} catch (Exception $e) {
			return 'error: '.$e;
		}
		return true;
	}

	function index_solr() {
		try{
			$this->_CI->load->library('solr');
			if($this->ro->status=='PUBLISHED'){
				$docs = array();
				$docs[] = $this->indexable_json();
				$this->_CI->solr->add_json(json_encode($docs));
				$this->_CI->solr->commit();
			}
		} catch (Exception $e) {
			return $e;
		}
		return true;
	}
	
	function indexable_json($limit=null) {
		$xml = $this->ro->getSimpleXML();
        $rifDom = new DOMDocument();
        $rifDom->loadXML($this->ro->getRif());
        $gXPath = new DOMXpath($rifDom);
        $gXPath->registerNamespace('ro', RIFCS_NAMESPACE);
		$json = array();
        $party_service_conn_limit = 200;

        if($limit && (int)$limit > 0)
            $party_service_conn_limit = $limit;

        $single_values = array(
			'id', 'slug', 'key', 'status', 'data_source_id', 'data_source_key', 'display_title', 'list_title', 'group', 'class', 'type'
		);

        $include_rights_type = array('open','restricted','conditional');
        $include_descriptions = array('brief','full');

		foreach($single_values as $s){
			$json[$s] = html_entity_decode($this->ro->{$s}, ENT_QUOTES);
		}
		$json['display_title'] = strip_tags(html_entity_decode($this->ro->title, ENT_QUOTES));

		$json['record_modified_timestamp'] = gmdate('Y-m-d\TH:i:s\Z', ($this->ro->updated ? $this->ro->updated : $this->ro->created));
		$json['record_created_timestamp'] = gmdate('Y-m-d\TH:i:s\Z', $this->ro->created);

		try{
			$json['simplified_title'] = strip_tags(html_entity_decode(iconv('UTF-8', 'ASCII//TRANSLIT', $this->ro->list_title), ENT_QUOTES));
		} catch (Exception $e) {
			throw new Exception ('iconv installation/configuration required for simplified title');
		}

		//macthing identifier count
		$json['matching_identifier_count'] = sizeof($this->ro->findMatchingRecords());

		//contributor
		$is_contributor = false;
		$contributor = $this->ro->getContributorExists($this->ro->id);
		if($contributor) {
			$json['contributor_page'] = $contributor[0];
			if($contributor[0]==$this->ro->slug) $is_contributor = true;
		}

		//descriptions
		$this->_CI->load->library('purifier');
		$fields = array('description_type', 'description_value');
		foreach($fields as $f) $json[$f] = array();
		$theDescription = '';
		$theDescriptionType = '';
		foreach($xml->registryObject->{$this->ro->class}->description as $description){
			$type = (string) $description['type'];
			$description_str = strip_tags(html_entity_decode((string) $description, ENT_QUOTES));
			//the one and only THE description
			if($type == 'brief' && $theDescriptionType != 'brief') {
				$theDescription = (string) $description;
				$theDescriptionType = $type;
			} else if($type == 'full' && ($theDescriptionType != 'brief' || $theDescriptionType != 'full')) {
				$theDescription = (string) $description;
				$theDescriptionType = $type;
			} else if($type != '' && $theDescriptionType == '' && $this->ro->class!='activity') {
				$theDescription = (string) $description;
				$theDescriptionType = $type;
			} else if($theDescription == '' && $this->ro->class!='activity') {
				$theDescription = (string) $description;
				$theDescriptionType = $type;
			}
            if($this->ro->class=='activity'&& in_array($type,$include_descriptions)){
                $json['description_value'][] = $description_str;
                $json['description_type'][] = $type;
            }elseif($this->ro->class!='activity'){
                $json['description_value'][] = $description_str;
                $json['description_type'][] = $type;
            }
		}
        $listDescription = trim(strip_tags(html_entity_decode(html_entity_decode($theDescription)), ENT_QUOTES));
        $json['list_description'] = $listDescription;
        $theDescription = htmlentities(strip_tags(html_entity_decode($theDescription, ENT_QUOTES), '<p></p><br><br />'));

		//will have a description field even if it's blank
        //add <br/> for NL if doesn't already have <p> or <br/>
        if (strpos($theDescription, "&lt;br") !== FALSE || strpos($theDescription, "&lt;p") !== FALSE || strpos($theDescription, "&amp;#60;p") !== FALSE) {
            $json['description'] = $theDescription;
        } else {
            $json['description'] = nl2br($theDescription);
        }
        $this->ro->set_metadata('the_description',$json['description']);

		//license
      /*  if($json['class'] == 'collection')
            $json['access_rights'] = 'Unknown';
		if($rights = $this->ro->processLicence()){
			foreach($rights as $right) {
				if(isset($right['licence_group'])) $json['license_class'] = $right['licence_group'];
                if(isset($right['accessRights_type'])) $json['access_rights'] = $right['accessRights_type'];

			}
		} */


        if ($json['class'] == 'collection') {
            $json['access_rights'] = 'Other';
        }

        //if there's a secret tag of SECRET_TAG_ACCESS_OPEN defined in constants, assign access_rights to open
        if ($this->ro->hasTag(SECRET_TAG_ACCESS_OPEN)) {
        	$json['access_rights'] = 'open';
        } elseif ($this->ro->hasTag(SECRET_TAG_ACCESS_CONDITIONAL)) {
        	$json['access_rights'] = 'conditional';
        } elseif ($this->ro->hasTag(SECRET_TAG_ACCESS_RESTRICTED)) {
        	$json['access_rights'] = 'restricted';
        }

        if ($rights = $this->ro->processLicence()) {
            foreach($rights as $right) {
                if(isset($right['licence_group'])) {
                    $json['license_class'] = strtolower($right['licence_group']);
                    if($json['license_class']=='unknown') $json['license_class']='Other';
                }
                if(isset($right['accessRights_type']) && in_array($right['accessRights_type'], $include_rights_type)) $json['access_rights'] = $right['accessRights_type'];
            }
        }

		//identifier
		if($identifiers = $this->ro->getIdentifiers()) {
			$fields = array('identifier_value', 'identifier_type');
			foreach ($fields as $f) $json[$f] = array();
			foreach ($identifiers as $identifier) {
				$json['identifier_value'][] = $identifier['identifier'];
				$json['identifier_type'][] = $identifier['identifier_type'];
			}
		}

		//related info text for searching
		$json['related_info_search'] = '';
        foreach($gXPath->query('//ro:relatedInfo') as $node) {
            $json['related_info_search'] .= trim($node->nodeValue);
        }


		//citation metadata text
		$json['citation_info_search'] = '';
		foreach($gXPath->query('//ro:citationInfo') as $node) {
			$json['citation_info_search'] .= trim($node->nodeValue);
		}

		//spatial
		if($spatialLocations = $this->ro->getLocationAsLonLats()){
			$fields = array('spatial_coverage_extents', 'spatial_coverage_polygons', 'spatial_coverage_centres');
			foreach($fields as $f) $json[$f] = array();
			$sumOfAllAreas = 0;
			foreach ($spatialLocations AS $lonLat) {
                $extents = $this->ro->calcExtent($lonLat);
                if( $extents['west'] +  $extents['east'] < 5 &&  $extents['east'] > 175)
                {
                    //need to insert zero bypass
                    $lonLat = $this->ro->insertZeroBypassCoords($lonLat, $extents['west'], $extents['east']);
                }
                $json['spatial_coverage_polygons'][] = $lonLat;
				$json['spatial_coverage_extents'][] = $extents['extent'];
				// $json['spatial_coverage_extents'] = '-74.093 41.042 -69.347 44.558';
				$sumOfAllAreas += $extents['area'];
				$json['spatial_coverage_centres'][] = $extents['center'];
			}
			$json['spatial_coverage_area_sum'] = $sumOfAllAreas;
		}

		//temporal
		if($temporalCoverageList = $this->ro->processTemporal()){
			$fields = array('date_from', 'date_to');
			foreach($fields as $f) $json[$f] = array();
			foreach ($temporalCoverageList AS $temporal) {
				if($temporal['type'] == 'dateFrom'){
					$json['date_from'][] = $temporal['value'];
				} elseif ($temporal['type'] == 'dateTo') {
					$json['date_to'][] = $temporal['value'];
				}
			}
			$json['earliest_year'] = $this->ro->getEarliestAsYear();
			$json['latest_year'] = $this->ro->getLatestAsYear();
		}

		//theme pages
		if($own_themepages = $this->ro->getThemePages()){
			$json['theme_page'] = array();
			foreach($own_themepages as $t){
				$json['theme_page'][] = $t['slug'];
			}
		}

		//tags
		if($tags = $this->ro->getTags()){
			$json['tag'] = array();
			$json['tag_type'] = array();
			foreach($tags as $tag){
				$json['tag'][] = $tag['name'];
				$json['tag_type'][] = $tag['type'];
			}
		}

		//subjects
		$subjects = $this->ro->processSubjects();
		$fields = array('subject_value_resolved', 'subject_value_unresolved', 'subject_type', 'subject_vocab_uri', 'subject_anzsrcfor', 'subject_anzsrcseo');
		foreach($fields as $f) $json[$f] = array();
		foreach($subjects as $s){
			$json['subject_value_unresolved'][] = $s['value'];
			$json['subject_value_resolved'][] = html_entity_decode($s['resolved'], ENT_QUOTES);
			$json['subject_vocab_uri'][] = $s['uri'];
			$json['subject_type'][] = $s['type'];
			// if (trim(strtolower($s['type']))=='anzsrc-for') {
			// 	$json['subject_anzsrcfor'][] = $s['resolved'];
			// } else if(trim(strtolower($s['type']))=='anzsrc-seo') {
			// 	$json['subject_anzsrcseo'][] = $s['resolved'];
			// }
		}

		//related objects
        if($limit && (int)$limit > 0 || $json['class'] == 'party' || $json['class'] == 'service')
		    $related_objects = $this->ro->getAllRelatedObjects(false, true, true, $party_service_conn_limit);
        else
            $related_objects = $this->ro->getAllRelatedObjects(false, true, true);

		$fields = array('related_collection_id', 'related_party_one_id', 'related_party_multi_id', 'related_activity_id', 'related_service_id');
		foreach($fields as $f) $json[$f] = array();
        $fields = array('related_collection_search', 'related_party_one_search', 'related_party_multi_search', 'related_activity_search', 'related_service_search');
        $processedIds = array();
        foreach($fields as $f) $json[$f] = array();
		    foreach($related_objects as $related_object){
                if($related_object['registry_object_id'] == null || !in_array($related_object['registry_object_id'], $processedIds))
                {
                    $processedIds[] = $related_object['registry_object_id'];
                    if($related_object['class']=='collection') {
                        $json['related_collection_title'][] = $related_object['title'];
                        if($related_object['registry_object_id'])
                            $json['related_collection_id'][] = $related_object['registry_object_id'];
                    } else if($related_object['class']=='activity') {
                        $json['related_activity_title'][] = $related_object['title'];
                        if($related_object['registry_object_id'])
                            $json['related_activity_id'][] = $related_object['registry_object_id'];
                    } else if($related_object['class']=='service') {
                        $json['related_service_title'][] = $related_object['title'];
                        if($related_object['registry_object_id'])
                            $json['related_service_id'][] = $related_object['registry_object_id'];
                    } else if($related_object['class']=='party' && $related_object['registry_object_id']) {

                        $this->_CI->db->select('value')
                            ->from('registry_object_attributes')
                            ->where('attribute', 'type')
                            ->where('registry_object_id',$related_object['registry_object_id']);
                        $query = $this->_CI->db->get();
                            foreach($query->result_array() AS $row)
                            {
                                if (isset($row['value']))
                                {
                                    if (in_array($row['value'],$this->party_multi_types))
                                    {
                                        $json['related_party_multi_title'][] = $related_object['title'];
                                        $json['related_party_multi_id'][] = $related_object['registry_object_id'];
                                    }
                                    else
                                    {
                                        $json['related_party_one_title'][] = $related_object['title'];
                                        $json['related_party_one_id'][] = $related_object['registry_object_id'];
                                    }
                                }
                        }
                }
            }
		}

        foreach($gXPath->query('//ro:description[@type="fundingAmount"]') as $node) {
            $json['funding_amount'] = preg_replace("/[^\d\.]+/","",$node->nodeValue);
        }

        foreach($gXPath->query('//ro:description[@type="fundingScheme"]') as $node) {
            $json['funding_scheme'] = strip_tags(html_entity_decode($node->nodeValue));
        }

        //researchers for activity
        $json['researchers'] = [];
        foreach($gXPath->query('//ro:description[@type="researchers"]') as $node) {
            $json['researchers'][] = strip_tags(html_entity_decode($node->nodeValue));
        }

        $activityStatus = 'other';
        foreach ($xml->xpath('//ro:existenceDates') AS $date)
        {

            $now = time();
            $start = false;
            $end = false;
            //$date->startDate = NaN;
            //$date->endDate = NaN;
            if ($date->startDate){

                if(strlen(trim($date->startDate)) == 4)
                    $date->startDate = "Jan 1, ".$date->startDate;
                $start = strtotime($date->startDate);
                $json['earliest_year'] = date("Y",$start);


            }

            if ($date->endDate){
                if(strlen(trim($date->endDate)) == 4)
                    $date->endDate = "Dec 31, ".$date->endDate;
                $end = strtotime($date->endDate);
                $json['latest_year'] = date("Y",$end);

            }

            $activityStatus = 'other';
            if ($start || $end){
                $activityStatus = 'PENDING';
                if(!$start || $start < $now)
                    $activityStatus = 'ACTIVE';
                if($end && $end < $now)
                    $activityStatus = 'CLOSED';
            }
        }
        $json['activity_status'] = $activityStatus;

        //Administering Institution, Funders and Researchers from related objects for activities
        if ($this->ro->class=='activity') {
        	$json['administering_institution'] = array();
        	$json['funders'] = array();
        	if(!isset($related_objects)) $related_objects = $this->ro->getAllRelatedObjects(false, false, true);
        	foreach ($related_objects as $related_object) {
                if(!isset($related_object['status']) || $related_object['status']!=DRAFT){
                    if ($related_object['class']=='party' && $related_object['relation_type']=='isManagedBy') {
                        $json['administering_institution'][] = $related_object['title'];
                    } else if($related_object['class']=='party' && $related_object['relation_type']=='isFundedBy') {
                        $json['funders'][] = $related_object['title'];
                    } else if($related_object['class']=='party') {
                        $tmp_ro = $this->_CI->ro->getByID($related_object['registry_object_id']);
                        if ( $tmp_ro && strtolower($tmp_ro->type)=='person' ) {
                            $json['researchers'][] = $related_object['title'];
                        }elseif(isset($related_object['related_info_type']) && $related_object['related_info_type']=='party'){
                            $json['researchers'][] = $related_object['title'];
                        }
                        unset($tmp_ro);
                    }
                }
        	}
        }



        //default values if none present
        if(!isset($json['license_class'])) $json['license_class'] = 'unknown';

        //lowercase all facet-able values
        $lowercase = array('type', 'license_class', 'access_rights', 'activity_status');
        foreach ($lowercase as $l) {
        	if(isset($json[$l])) {
        		if (is_array($json[$l])) {
        			foreach($json[$l] as &$v) {
        				$v = strtolower($v);
        			}
        		} else {
        			$json[$l] = strtolower($json[$l]);
        		}
        	}
        }
        $this->_dropCache();
        $json = array_filter($json);
		return $json;
	}

	function update_field_index($field){
		$json = array();
		$json['id'] = $this->ro->id;

		if($field=='slug'){
			$json['slug'] = array('set'=>$this->ro->slug);
		}
        if($field=='tag')
        {
            $tags = $this->ro->getTags();
            if(isset($tags) && sizeof($tags) > 0)
            {
                $json['tag'] = array();
                $json['tag_type'] = array();
                $json['tag']['set'] = array();
                $json['tag_type']['set'] = array();
                foreach($tags as $tag){
                    $json['tag']['set'][] = $tag['name'];
                    $json['tag_type']['set'][] = $tag['type'];
                }
            }else{
                $json['tag']['set'] = null;
                $json['tag_type']['set'] = null;
            }

        }
		$docs = array();
		$docs[] = $json;
		$this->_CI->load->library('solr');
		$result = json_decode($this->_CI->solr->add_json(json_encode($docs)), true);
		$this->_CI->solr->commit();

		if(isset($result['responseHeader']) && $result['responseHeader']['status']==0){
            $this->_dropCache();
			return true;
		} else return false;
	}

    function _dropCache()
    {
        $api_id = 'ro-api-'.$this->ro->id.'-portal';
        $portal_id = 'ro-portal-'.$this->ro->id;
        $ci =& get_instance();
        $ci->load->driver('cache');
        try{
        $ci->cache->file->delete($api_id);
        $ci->cache->file->delete($portal_id);
        }
        catch(Exception $e){

        }
    }
}