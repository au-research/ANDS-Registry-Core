<?php

/* use static definitions to only load the transform 
 * XSLT once
 */
class HarvestTransforms {
	static $feed_to_rif_transformer = NULL;
		
	static function get_feed_to_rif_transformer()
	{
		if (is_null(self::$feed_to_rif_transformer))
		{
			$getRifFromFeed = new DomDocument();
			$getRifFromFeed->load('applications/registry/data_source/transforms/extract_rif_from_feed.xsl');
			$getRifFromFeedproc = new XSLTProcessor();
			$getRifFromFeedproc->importStyleSheet($getRifFromFeed);
			self::$feed_to_rif_transformer =	$getRifFromFeedproc;
		}

		return self::$feed_to_rif_transformer;
	}
	
}		