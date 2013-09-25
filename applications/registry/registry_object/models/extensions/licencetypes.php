<?php

class LicenceTypes_Extension extends ExtensionBase
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
	function processLicence()
	{
		$rights = array();
		$sxml = $this->ro->getSimpleXML();	
		$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
		foreach ($sxml->xpath('//ro:'.$this->ro->class.'/ro:rights') AS $theRights)
		{
			$right = array();
			foreach($theRights as $key=>$theRight)
			{
				$right['value']= (string)$theRight;
				$right['type'] = (string)$key;
				if((string)$theRight['rightsUri']!='') $right['rightsUri'] = (string)$theRight['rightsUri'];

				if($right['type']=='licence')
				{
					if((string)$theRight['type']!='')
					{
						$right['licence_type'] = (string)$theRight['type'];
					}else{
						$right['licence_type'] = 'Unknown';
					}

					$right['licence_group'] = $this->getLicenceGroup($right['licence_type']);	
					if($right['licence_group']=='') $right['licence_group'] = 'Unknown';
				}
				$rights[] = $right;
				unset($right);
			}

		}
		$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
		foreach ($sxml->xpath('//ro:'.$this->ro->class.'/ro:description') AS $theRightsDescription)
		{

				if($theRightsDescription['type']=='rights'||$theRightsDescription['type']=='accessRights')
				{
					
					$right = array();
					$right['value']= (string)$theRightsDescription;

					$right['type'] = $theRightsDescription['type'];

					if($this->checkRightsText($right['value']))
					{
						$right['licence_group'] = $this->checkRightsText($right['value']);
					}

					$rights[] = $right;
				}
		
		}	

		return $rights;

	}


	// Temporary workaround for storing "groupings" of licence identifiers
	// XXX: Long term solution should use a vocabulary service (such as ANDS's)
	private static $licence_groups = array(
        "GPL" => "Open Licence",
        "CC-BY-SA" => "Open Licence",
        "CC-BY-ND" => "Non-Derivative Licence",
        "CC-BY-NC-SA" => "Non-Commercial Licence",
        "CC-BY-NC-ND" => "Non-Derivative Licence",
        "CC-BY-NC" => "Non-Commercial Licence",
        "CC-BY" => "Open Licence",
        "AusGOALRestrictive" => "Restrictive Licence",
        "NoLicence" => "No Licence"
        
    );
    
	function getLicenceGroup($licence_type)
	{
		if (isset(self::$licence_groups[(string)$licence_type]))
		{
			return self::$licence_groups[(string)$licence_type];
		}
		else
		{
			return '';
		}

	}

	function checkRightsText($value)
	{

		if((str_replace("http://creativecommons.org/licenses/by/","",$value)!=$value)||(str_replace("http://creativecommons.org/licenses/by-sa/","",$value)!=$value))
		{
			return "Open Licence";
		}
		elseif((str_replace("http://creativecommons.org/licenses/by-nc/","",$value)!=$value)||(str_replace("http://creativecommons.org/licenses/by-nc-sa/","",$value)!=$value))
		{
			return "Non-Commercial Licence";
		}
		elseif((str_replace("http://creativecommons.org/licenses/by-nd/","",$value)!=$value)||(str_replace("http://creativecommons.org/licenses/by-nc-nd/","",$value)!=$value))
		{
			return "Non-Derivative Licence";
		}
		else
		{
			return false;
		}
}
}