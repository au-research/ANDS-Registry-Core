<?php

class Suggestedlinks_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	/* This should be a loader for classes in a seperate directory called "suggestors" 

		Workflow should be:

			- check if there is a file with the name of the suggestor in our suggestors directory
			- instantiate that class and pass it the reference to this registry object
			- have the logic for each suggestor in it's own file and class to avoid clutter
			- the suggester's ->suggest() method returns an array of suggested links (not sure what format this object should be in?)

	*/
	function getSuggestedLinks($suggestor, $start=0, $rows=20)
	{
		$suggested_links = array();
		if (!$suggestor) { throw new Exception("No suggestor specified..."); }

		// Get the necessary classes
		require_once(APP_PATH . 'registry_object/suggestors/_suggestor.php');

		if (file_exists(APP_PATH . 'registry_object/suggestors/' . $suggestor . '.php'))
		{
			require_once(APP_PATH . 'registry_object/suggestors/' . $suggestor . '.php');
		}
		
		// Try and instantiate the class
		if (class_exists("Suggestor_" . $suggestor))
		{
			$classname = "Suggestor_" . $suggestor;
			$suggestor = new $classname();
			$suggested_links = $suggestor->getSuggestedLinksForRegistryObject($this->ro, $start, $rows);
		}
		else
		{
			throw new Exception("Suggestor could not be found: " . $suggestor);
		}

		return $suggested_links;

	}

}