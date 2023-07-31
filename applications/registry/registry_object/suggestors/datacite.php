<?php

/**
 * This suggestor links records which are similar to the record suggested
 *
 * @return array of items which will be displayed as suggested links
 */
class Suggestor_datacite implements GenericSuggestor
{
	const DATACITE_SOLR_URL = 'http://search.datacite.org/api';
	const DATACITE_URL_PREFIX = 'http://data.datacite.org/';
	const DATACITE_URL_FIELD = 'doi';
	const DATACITE_TITLE_LENGTH = 250;

	public function getSuggestedLinksForRegistryObject(_registry_object $registry_object, $start, $rows)
	{
		$CI =& get_instance();	
		$q = preg_replace("/[^A-Za-z0-9\- ]/", ' ',$registry_object->titleWithoutCommonWords()); // preg_replace("/[^A-Za-z0-9 ]/", ' ', 
		$significantsArray = $registry_object->splitTitleBySignificance(strtolower($q));
		$q=rawurlencode($q);
		$q=str_replace("%5C%22", "\"", $q);//silly encoding

		$q = '?q=+title:('.$significantsArray[0].")+description:(".$significantsArray[0].")";

		// Result variable
		$links = array();

		// Build the weighted URL based on datacite:resourceTypeGeneral
		//  - First Collections/Datasets
		//  - Then Film, Image, Sound, PhysicalObject, InteractiveResource
		//  - Then Model, Software, Service
		//  - Then Event, Text
		//  - Finally those without a resourceType  

		$url = self::DATACITE_SOLR_URL;
		$urlTail = '&defType=disMax';
		$urlTail = '&fq=language_facet%3A"English"'; // only English
		$urlTail .= '&qf=resourceTypeGeneral:("Collection","Dataset")^9999';
		$urlTail .= '%20+resourceTypeGeneral:("Film","Image","Sound","PhysicalObject","InteractiveResource")^5555';
		$urlTail .= '%20+resourceTypeGeneral:("Model","Software","Service")^1777';
		$urlTail .= '%20+resourceTypeGeneral:("Event","Text")^111';
		$urlTail .= '%20+resourceTypeGeneral:""^1';
		$urlTail .= '&fl=*,score';
		$urlTail .= '&start='.$start;
		$urlTail .= '&rows='.$rows;
		$urlTail .= '&version=2.2&wt=json';
   		
   		$content= json_decode(file_get_contents($url . $q . $urlTail), true);

   		// Check for a valid SOLR response
   		if (!isset($content['response']['numFound']))
   		{
   			return $links;
   		}

		$found = $content['response']['numFound'];
		/* If we got no results, then lets be more tolerant and add the non-significant words and do fuzzy match */
		$fuzzy_match_url = "";
		if($found<10)
		{
			if(isset($significantsArray[1]) && $significantsArray[1] != '')
				$q .= '+title:('.$significantsArray[1].")+description:(".$significantsArray[1].")";

			$fuzzy_match_url = $url . $q . $urlTail . '&defType=dismax&mm=3%3C75%25';
			$content= json_decode(file_get_contents($fuzzy_match_url),true);
			$found = $content['response']['numFound'];					
		}

		/* Generate the links data */
		if (isset($content['response']['docs']))
		{
			foreach($content['response']['docs'] AS $doc)
			{
				if ($doc['title'][0])
				{
					$links[] = array("url"=>self::DATACITE_URL_PREFIX . $doc[self::DATACITE_URL_FIELD],
									"title"=>ellipsis($doc['title'][0], self::DATACITE_TITLE_LENGTH),
									"class"=>"external",
									"expanded_html"=>$CI->load->view("registry_object/datacite_preview", $doc, true)
									);
				}
			}
		}

		if(!$rows) $rows=10;
		$pagination = array();
		if($start==0){
			$currentPage = 1;
		}else{
			$currentPage = ceil($start/$rows)+1;
		}
		$totalPage = ceil($content['response']['numFound'] / (int) $rows);

		if($currentPage!=1){
			$prev = $start-$rows;
			$next = $start+$rows;
		}
		else if($currentPage==1&&$totalPage==1){
			$prev = false;
			$next = false;
		}else if($currentPage==$totalPage){
			$prev = $start-$rows;
			$next = false;
		}else{
			$prev = false;
			$next = $start+$rows;
			$debug = '1';
		}
		$pagination = array("currentPage"=>$currentPage,"totalPage"=>$totalPage);
		if($prev) $pagination['prev']=$prev;
		if($next) $pagination['next']=$next;
		if($start==10)$pagination['prev']='0';


		$response = array(
				"count"=>$found, 
				"links"=>$links,
				//"query"=>$url . $q . $urlTail,
				//"fuzzy_query"=>$fuzzy_match_url,
				//"significantsArray"=>$significantsArray,
				"pagination"=>$pagination,
				"suggestor"=>"datacite"
		);

		return $response;
	}



	/* May be necessary for future use?? */
	public function getSuggestedLinksForString($query_string, $start, $rows)
	{
		return array();
	}

}