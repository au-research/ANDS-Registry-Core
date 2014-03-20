<?php

/**
 * This suggestor links records which are similar to the record suggested
 *
 * @return array of items which will be displayed as suggested links
 */
class Suggestor_ands_identifiers implements GenericSuggestor
{
	

	public function getSuggestedLinksForRegistryObject(_registry_object $registry_object, $start, $rows)
	{

		// First, get published records with the same identifier as us
		// Note: whilst we can use SOLR to get the linked-to records, 
		//       we shouldn't use SOLR to get our own information, as 
		//       this would mean that DRAFT requests fail (drafts NOT 
		// 		 in SOLR index).
		$suggestions = array();
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
				if((string) $identifier != '')
				{
					$my_identifiers[] = '"' . (string) $identifier . '"';
				}
			}
		}

        if (count($my_identifiers) == 0)
        {
        	return $suggestions;
        }
		
		$identifier_search_query = implode(" +identifier_value:", $my_identifiers);

		// But exclude already related objects
		$my_relationships = array_map(function($elt){ return '"' . $elt . '"'; }, $registry_object->getRelatedKeys());
		$my_relationships[] = '"'. $registry_object->key . '"';
		array_unshift($my_relationships, ''); // prepend an element so that implode works
		$relationship_search_query = " " . implode(" -key:", $my_relationships);

		$suggestions = $this->getSuggestionsBySolrQuery($relationship_search_query . " AND " . $identifier_search_query, $start, $rows);

		return $suggestions;
	}



	private function getSuggestionsBySolrQuery($search_query, $start, $rows)
	{
		$CI =& get_instance();
		$links = array();
		$relatedByIdentifiers = $registry_object->findMatchingRecords();

		foreach($relatedByIdentifiers as $r_id){
			$ro = $CI->ro->getByID($r_id);

			$match = array("url"=>portal_url($ro->slug),
								"title"=>$ro->title,
								"class"=>$ro->class,
								"description"=>$ro->getMetadata('the_description'),
								"slug"=>$ro->slug);
			$links[] = $match;
		}
			$pagination = array("currentPage"=>1,"totalPage"=>1);
			$suggestions = array(
				"count" => sizeof($relatedByIdentifiers),
				"links" => $links,
				"pagination" => $pagination,
				"suggestor" => 'ands_identifiers'
			);

		return $suggestions;
	}

	/* May be necessary for future use?? */
	public function getSuggestedLinksForString($query_string, $start, $rows)
	{
		return array();
	}


}