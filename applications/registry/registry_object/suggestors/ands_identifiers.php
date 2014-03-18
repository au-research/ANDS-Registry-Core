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