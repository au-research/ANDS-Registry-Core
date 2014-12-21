<?php
/**
 * This suggestor links records which are similar to the record suggested
 *
 * @return array of items which will be displayed as suggested links
 */
class Suggestor_subjects implements GenericSuggestor {

	public function getSuggestedLinksForRegistryObject(_registry_object $registry_object, $limit, $offset){
		$suggestions = array();
		$sxml = $registry_object->getSimpleXML();
		if ($sxml->registryObject) {
			$sxml = $sxml->registryObject;
		}
		
		// Subject matches
		$my_subjects = array();
		if($sxml->{strtolower($registry_object->class)}->subject) {
			foreach($sxml->{strtolower($registry_object->class)}->subject AS $subject) {
				$my_subjects[] = (string) removeBadValue($subject);
			}
		}

		$str = '';
		foreach($my_subjects as $s) {
			$str.='subject_value_unresolved:('.$s.') ';
		}
		var_dump($str);

		// var_dump($my_subjects);
	}


	public function getSuggestedLinksForString($query_string, $limit, $offset) {return false;}
}