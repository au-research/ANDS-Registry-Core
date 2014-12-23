<?php
require_once(APP_PATH. 'registry_object/models/_GenericSuggestor.php');

/**
 * Class Subjects Suggestor
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Subjects extends _GenericSuggestor {
	
	/**
	 * Suggest Records based on subject_value_unresolved value from the local SOLR core
	 * Score of similar record is calculated based on the amount of subjects shared
	 * Using the edismax minimum match value for the SOLR search (mm)
	 * @return array suggested_records
	 */
	function suggest() {

		//Get subjects from the XML
		$sxml = $this->ro->getSimpleXML();
		if ($sxml->registryObject) {
			$sxml = $sxml->registryObject;
		}
		
		// Subject matches
		$my_subjects = array();
		if ($sxml->{strtolower($this->ro->class)}->subject) {
			foreach ($sxml->{strtolower($this->ro->class)}->subject as $subject) {
				$my_subjects[] = (string) removeBadValue($subject);
			}
		}

		//construct the query stirng
		$str = '';
		foreach($my_subjects as $s) {
			$str.='subject_value_unresolved:('.$s.') ';
		}
		
		//call SOLR library
		$ci =& get_instance();
		$ci->load->library('solr');
		$ci->solr->init();
		$ci->solr
			->init()
			->setOpt('q', $str)
			->setOpt('rows', '10')
			->setOpt('fl', 'id,key,slug,title')
			->setOpt('defType', 'edismax')
			->setOpt('lowerCaseOperators', 'true');

		$suggestions = array();

		//repeately reduce the mm attribute and re-search based on the number of subjects
		//highest scores will be records that shares all subjects
		for ( $i = sizeof($my_subjects) ; $i > 1 ; $i-- ) {
			$ci->solr->clearOpt('mm');
			$ci->solr->setOpt('mm', $i);
			$result = $ci->solr->executeSearch(true);

			if($result['response']['numFound'] > 0) {
				//score is static for now, algorithms could be applied here for different subjects type
				//[subject_type] is available
				foreach($result['response']['docs'] as $doc) {
					$doc['score'] = $i;
					$suggestions[] = $doc;
				}
			}
		}

		return $suggestions;
	}

	function __construct() {
		parent::__construct();
		set_exception_handler('json_exception_handler');
	}
}