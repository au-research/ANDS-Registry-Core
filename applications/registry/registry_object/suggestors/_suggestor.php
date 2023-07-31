<?php

interface GenericSuggestor
{
	// Bare minimum necessary to suggest "links"
	public function getSuggestedLinksForString($query_string, $limit, $offset);
	public function getSuggestedLinksForRegistryObject(_registry_object $registry_object, $limit, $offset);

}