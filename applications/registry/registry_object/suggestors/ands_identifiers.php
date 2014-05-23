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
		$my_identifiers = array();
		if($sxml->{strtolower($registry_object->class)}->identifier)
		{
			foreach($sxml->{strtolower($registry_object->class)}->identifier AS $identifier)
			{
				if((string) $identifier != '')
				{
					$my_identifiers[] = (string) $identifier;
				}
			}
		}

        if (count($my_identifiers) == 0) {
        	return $suggestions;
        }

		$identifier_search_query = '';
		if(sizeof($my_identifiers) > 0){
            $identifier_search_query = join('","', $my_identifiers);
			$identifier_search_query = '+identifier_value:("'.$identifier_search_query.'")';
		}

		// But exclude already related objects
		$my_relationships = array_map(function($elt){ return $elt; }, $registry_object->getRelatedKeys());
		$my_relationships[] = $registry_object->key;

		$relationship_search_query = '';
		if(sizeof($my_relationships) > 0) {
			$relationship_search_query = implode('") -key:("', $my_relationships);
			$relationship_search_query = '-key:("'.$relationship_search_query.'")';
		}

		$query = $relationship_search_query;
		if($identifier_search_query!='') $query .= ' AND '. $identifier_search_query;


		$suggestions = $this->getSuggestionsBySolrQuery($query, $start, $rows);
		if(sizeof($suggestions)> 0){
			$suggestions['values'] = $my_identifiers;
		}
		return $suggestions;
	}



	private function getSuggestionsBySolrQuery($search_query, $start, $rows)
	{
		$CI =& get_instance();

		$start = ($start ? $start: 0);
		$rows = ($rows ? $rows: 10);

		$CI->load->library('solr');
		$CI->solr->init();
		$CI->solr->setOpt("q", $search_query);
		$CI->solr->setOpt("start", $start);
		$CI->solr->setOpt("rows", $rows);
		$result = $CI->solr->executeSearch(true);
		$suggestions = array();

		if (isset($result['response']['numFound']) && $result['response']['numFound'] > 0)
		{
			$links = array();

			foreach($result['response']['docs'] AS $doc)
			{
				$links[] = array("url"=>portal_url($doc['slug']),
								"title"=>isset($doc['display_title'])?$doc['display_title']:$doc['list_title'],
								"class"=>$doc['class'],
								"description"=>isset($doc['description'])?$doc['description']:'',
								"slug"=>$doc['slug']);
			}
			if(!$rows) $rows=10;
			$pagination = array();
			if($start==0){
				$currentPage = 1;
			}else{
				$currentPage = ceil($start/$rows)+1;
			}
			$totalPage = ceil($result['response']['numFound'] / (int) $rows);

			if($currentPage!=1){
				$prev = $start-$rows;
				$next = $start+$rows;
			}
			else if($currentPage==1&&$totalPage==1){
				$prev = false;
				$next = false;
			}elseif($currentPage==$totalPage){
				$prev = $start-$rows;
				$next = false;
			}else{
				$prev = false;
				$next = $start+$rows;
			}
			$pagination = array("currentPage"=>$currentPage,"totalPage"=>$totalPage);
			if($prev !== false) $pagination['prev']=(string)$prev;
			if($next !== false) $pagination['next']=(string)$next;

			$suggestions = array(
				"count" => $result['response']['numFound'],
				"links" => $links,
				"pagination" => $pagination,
				"suggestor" => 'ands_identifiers'
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