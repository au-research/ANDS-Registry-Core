<?php

/**
 * This suggestor links records which are similar to the record suggested
 *
 * @return array of items which will be displayed as suggested links
 */
class Suggestor_ands_duplicates implements GenericSuggestor
{
	

	public function getSuggestedLinksForRegistryObject(_registry_object $registry_object, $start, $rows)
	{
		// First, get published records with the same identifier as us
		// Note: whilst we can use SOLR to get the linked-to records, 
		//       we shouldn't use SOLR to get our own information, as 
		//       this would mean that DRAFT requests fail (drafts NOT 
		// 		 in SOLR index).

		$sxml = $registry_object->getSimpleXML();
		if ($sxml->registryObject)
		{
			$sxml = $sxml->registryObject;
		}

		// Identifier matches (if another object has the same identifier)
		//var_dump($sxml);
		$my_identifiers = array('');
		if($sxml->{strtolower($registry_object->class)}->identifier)
		{
			foreach($sxml->{strtolower($registry_object->class)}->identifier AS $identifier)
			{
				$my_identifiers[] = '"' . (string) $identifier . '"';
			}
		}
		$identifier_search_query = implode(" +identifier_value:", $my_identifiers);
		$identifier_search_query = " -slug:".$registry_object->slug.$identifier_search_query;

		$suggestions = $this->getSuggestionsBySolrQuery($identifier_search_query);

		return $suggestions;
	}



	private function getSuggestionsBySolrQuery($search_query)
	{
		$CI =& get_instance();
		$CI->load->library('solr');
		$CI->solr->init();
		$CI->solr->setOpt("q", $search_query);
		$result = $CI->solr->executeSearch(true);
		$suggestions = array();

		if (isset($result['response']['numFound']) && $result['response']['numFound'] > 0)
		{
			$links = array();

			foreach($result['response']['docs'] AS $doc)
			{
				$links[] = array("url"=>portal_url($doc['slug']),
								"title"=>$doc['display_title'],
								"group"=>$doc['group'],
								"slug"=>$doc['slug']);
			}
			$suggestions = array(
				"count" => $result['response']['numFound'],
				"links" => $links,
				"suggestor" => 'ands_duplicates'
			);
		}
		else{
			$suggestions = array(
				"search_query" => $search_query
			);
		}
		return $suggestions;
	}



	/* May be necessary for future use?? */
	public function getSuggestedLinksForString($query_string, $start, $rows)
	{
		return array();
	}


}