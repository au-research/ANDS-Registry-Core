<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sync_extension extends ExtensionBase{
		
	function __construct($ro_pointer){
		parent::__construct($ro_pointer);
	}

	/**
	 * Do an enrich and commit, if full is provided, do add relationships and update quality metadata as well
	 * With great power comes great responsibility
	 * @param boolean $full determine whether to do addRelationships() and update quality metadata
	 * @return boolean/string [if it's a string, it's an error message]
	 */
	function sync($full = true){
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
				$docs[] = $this->indexable_json();
				$this->_CI->solr->add_json(json_encode($docs));
			}
		} catch (Exception $e) {
			return 'error: '.$e;
		}
		return true;
	}
	
	function indexable_json() {
		$xml = $this->ro->getSimpleXML();
		$xml = addXMLDeclarationUTF8(($xml->registryObject ? $xml->registryObject->asXML() : $xml->asXML()));
		$xml = simplexml_load_string($xml);
		$xml = simplexml_load_string( addXMLDeclarationUTF8($xml->asXML()) );
		$json = array();

		$single_values = array(
			'id', 'slug', 'key', 'status', 'data_source_id', 'data_source_key', 'display_title', 'list_title', 'group', 'class', 'type'
		);

		foreach($single_values as $s){
			$json[$s] = $this->ro->{$s};
		}
		$json['display_title'] = $this->ro->title;

		$json['record_modified_timestamp'] = gmdate('Y-m-d\TH:i:s\Z', ($this->ro->updated ? $this->ro->updated : $this->ro->created));
		$json['record_created_timestamp'] = gmdate('Y-m-d\TH:i:s\Z', $this->ro->created);

		try{
			$json['simplified_title'] = iconv('UTF-8', 'ASCII//TRANSLIT', $this->ro->list_title);
		} catch (Exception $e) {
			throw new Exception ('iconv installation/configuration required for simplified title');
		}

		//macthing identifier count
		$json['matching_identifier_count'] = sizeof($this->ro->findMatchingRecords());

		//contributor
		$is_contributor = false;
		$contributor = $this->ro->getContributorExists($this->ro->id);
		if($contributor) {
			$json['contributor'] = $contributor[0];
			if($contributor[0]==$this->ro->slug) $is_contributor = true;
		}

		//descriptions
		$this->_CI->load->library('purifier');
		$fields = array('description_type', 'description_value');
		foreach($fields as $f) $json[$f] = array();
		$theDescription = '';
		$theDescriptionType = '';
		foreach($xml->{$this->ro->class}->description as $description){
			$type = (string) $description['type'];
			$description_str = html_entity_decode((string) $description);

			//clean the HTML
			$clean_html = $this->_CI->purifier->purify_html($description_str);

			//clean brs
			if (strpos($description_str, "&lt;br") !== FALSE || strpos($description_str, "&lt;p") !== FALSE || strpos($description_str, "&amp;#60;p") !== FALSE) {
				$encoded_html = $clean_html;
			} else {
				$encoded_html = nl2br($clean_html);
			}

			//the one and only THE description
			if($type == 'brief' && $theDescriptionType != 'brief') {
				$theDescription = $encoded_html;
				$theDescriptionType = $type;
			} else if($type == 'full' && ($theDescriptionType != 'brief' || $theDescriptionType != 'full')) {
				$theDescription = $encoded_html;
				$theDescriptionType = $type;
			} else if($type != '' && $theDescriptionType == '') {
				$theDescription = $encoded_html;
				$theDescriptionType = $type;
			} else if($theDescription == '') {
				$theDescription = $encoded_html;
				$theDescriptionType = $type;
			}

			$json['description_value'][] = $encoded_html;
			$json['description_type'][] = $type;
		}

		if($theDescription && $theDescriptionType) {
			$json['description'] = htmlentities(strip_tags(html_entity_decode($theDescription), '<p></p><br><br />'));
		}

		//license
		if($rights = $this->ro->processLicence()){
			foreach($rights as $right) {
				if(isset($right['licence_group'])) $json['license_class'] = $right['licence_group'];
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
		foreach($xml->{$this->ro->class}->relatedInfo as $relatedInfo){
			$innerXML = $relatedInfo->saveXML();
			$dom = new DOMDocument();
			$dom->loadXML($innerXML);
			$xpt = new DOMXpath($dom);
			foreach($xpt->query('//relatedInfo') as $node) {
				$json['related_info_search'] .= trim($node->nodeValue);
			}
		}

		//citation metadata text
		$json['citation_info_search'] = '';
		foreach($xml->{$this->ro->class}->citationInfo as $citationInfo){
			$innerXML = $citationInfo->saveXML();
			$dom = new DOMDocument();
			$dom->loadXML($innerXML);
			$xpt = new DOMXpath($dom);
			foreach($xpt->query('//citationInfo') as $node) {
				$json['citation_info_search'] .= trim($node->nodeValue);
			}
		}

		//spatial
		if($spatialLocations = $this->ro->getLocationAsLonLats()){
			$fields = array('spatial_coverage_extents', 'spatial_coverage_polygons', 'spatial_coverage_centres');
			foreach($fields as $f) $json[$f] = array();
			$sumOfAllAreas = 0;
			foreach ($spatialLocations AS $lonLat) {
				$json['spatial_coverage_polygons'][] = $lonLat;
				$extents = $this->ro->calcExtent($lonLat);
				$json['spatial_coverage_extents'][] = $extents['extent'];
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
		$fields = array('subject_value_resolved', 'subject_value_unresolved', 'subject_type', 'subject_vocab_uri');
		foreach($fields as $f) $json[$f] = array();
		foreach($subjects as $s){
			$json['subject_value_unresolved'][] = $s['value'];
			$json['subject_value_resolved'][] = $s['resolved'];
			$json['subject_vocab_uri'][] = $s['uri'];
			$json['subject_type'][] = $s['type'];
		}

		//related objects
		$related_objects = $this->ro->getAllRelatedObjects(false, true, true);
		$fields = array('related_object_key', 'related_object_id', 'related_object_class', 'related_object_display_title', 'related_object_relation');
		foreach($fields as $f) $json[$f] = array();
		foreach($related_objects as $related_object){
			$json['related_object_key'][] = $related_object['key'];
			$json['related_object_id'][] = $related_object['registry_object_id'];
			$json['related_object_class'][] = $related_object['class'];
			$json['related_object_display_title'][] = $related_object['title'];
			$json['related_object_relation'][] = $related_object['relation_type'];
		}

		$json = array_filter($json);
		return $json;
	}
}