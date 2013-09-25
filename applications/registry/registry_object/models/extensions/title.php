<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Title_Extension extends ExtensionBase
{
	
	const DEFAULT_TITLE = "(no name/title)";
	
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}	
		 
	function updateTitles($sxml = null)
	{
		$list_title = self::DEFAULT_TITLE;
		$display_title = self::DEFAULT_TITLE;
		
		if (is_null($sxml))
		{
			$sxml = $this->ro->getSimpleXML();
		}
		
		$titles = $this->getTitlesForRegistryObject($sxml);

		$this->ro->title = $titles['display_title'];
		$this->ro->list_title = $titles['list_title'];
		$this->ro->save();
	}
	
	
	function getTitlesForRegistryObject(SimpleXMLElement $sxml)
	{
		
		// Pick a name, given preference to the primary name
		$name = '';
		//var_dump($sxml);
		$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
		$names = $sxml->xpath('//ro:'.$this->ro->class.'/ro:name[@type="primary"]');
		if (count($names) == 0)
		{
			$names = $sxml->xpath('//ro:'.$this->ro->class.'/ro:name');
		}
		
		if (count($names) > 0)
		{
			// Pick the first one (this used to be undeterministic, 
			// but this could result in multiple SLUGs which is stupid)
			$name = $names[0];
			$name = $this->getTitlesForFragment($name, $this->ro->class);
		}
		else
		{
			$name['display_title'] = self::DEFAULT_TITLE . " (" . $this->ro->id . ")";
			$name['list_title'] = $name['display_title'];
		}

		return $name;
	}	
	
	function getTitlesForFragment (SimpleXMLElement $name, $class)
	{
		$list_title = self::DEFAULT_TITLE;
		$display_title = self::DEFAULT_TITLE;
		
		if ($name && $class != 'party')
		{
			// Join together the name parts with spaces
			// N.B. Order is not explicitly defined!
			$parts_accumulator = array();
			foreach($name->namePart AS $np)
			{
				$parts_accumulator[] = (string) $np;
			}
	
			$name = trim(implode(" ", $parts_accumulator));
			if ($name != '')
			{
				$list_title = $name;
				$display_title = $name;
			}
		}
		elseif ($name && $class == 'party') 
		{
			// Ridiculously complex rules for parties
			// First lets accumulate all the name parts into their types
			$partyNameParts = array();
			$partyNameParts['title'] = array();
			$partyNameParts['suffix'] = array();
			$partyNameParts['initial'] = array();
			$partyNameParts['given'] = array();
			$partyNameParts['family'] = array();
			$partyNameParts['user_specified_type'] = array();
			
			foreach($name->namePart AS $namePart)
			{
				if (in_array(strtolower((string) $namePart['type']), array_keys($partyNameParts)))
				{
					$partyNameParts[strtolower($namePart['type'])][] = trim($namePart);
				}
				else
				{
					$partyNameParts['user_specified_type'][] = trim($namePart);
				}
				
				
			}
			
			// Now form up the display title according to the ordering rules
			$display_title = 	trim((count($partyNameParts['title']) > 0 ? implode(" ", $partyNameParts['title']) . " " : "") .
								(count($partyNameParts['given']) > 0 ? implode(" ", $partyNameParts['given']) . " " : "") .
								(count($partyNameParts['initial']) > 0 ? implode(" ", $partyNameParts['initial']) . " " : "") .
								(count($partyNameParts['family']) > 0 ? implode(" ", $partyNameParts['family']) . " " : "") .
								(count($partyNameParts['suffix']) > 0 ? implode(" ", $partyNameParts['suffix']) . " " : "") .
								(count($partyNameParts['user_specified_type']) > 0 ? implode(" ", $partyNameParts['user_specified_type']) . " " : ""));
					
			// And now the list title			
			// initials first, get a full stop
			foreach ($partyNameParts['given'] AS &$givenName)
			{
				$givenName = (strlen($givenName) == 1 ? $givenName . "." : $givenName);
			}
			foreach ($partyNameParts['initial'] AS &$initial)
			{
				$initial = $initial . ".";
			}

			$list_title = 	trim((count($partyNameParts['family']) > 0 ? implode(" ", $partyNameParts['family']) : "") .
							(count($partyNameParts['given']) > 0 ? ", " . implode(" ", $partyNameParts['given']) : "") .
							(count($partyNameParts['initial']) > 0 ? " " . implode(" ", $partyNameParts['initial']) : "") .
							(count($partyNameParts['title']) > 0 ? ", " . implode(" ", $partyNameParts['title']) : "") .
							(count($partyNameParts['suffix']) > 0 ? ", " . implode(" ", $partyNameParts['suffix']) : "") .
							(count($partyNameParts['user_specified_type']) > 0 ? " " . implode(" ", $partyNameParts['user_specified_type']) . " " : ""));
			
			// Stop titles with prefixed commas from incorrectly specified types (above)
			while (substr($list_title,0,2) == ", ")
			{
				$list_title = substr($list_title, 2);
			}
		
		}
		
		// Some length checking...
		if (strlen($display_title) > 255) { $display_title = substr($display_title,0,252) . "..."; }
		if (strlen($display_title) == 0) { $display_title = self::DEFAULT_TITLE; }
		if (strlen($list_title) == 0) { $list_title = self::DEFAULT_TITLE; }
		if (strlen($list_title) > 255) { $list_title = substr($list_title,0,252) . "..."; }
		return array("display_title"=>$display_title, "list_title" => $list_title);		
	}

	/**
	 * Replace all common words in the title with spaces
	 * (used to create more specific keyword queries against
	 * external APIs)
	 */
	function titleWithoutCommonWords()
	{
		if ($this->ro->title)
		{
			/* From L Woods, 2010 */
			$CommonWords = array (
				' at ',
				' the ',
				' and ',
				' of ',
				' in ',
				' is ',
				' to ',
				' a '
			);

			return str_replace($CommonWords, array_fill(0, count($CommonWords), ' '), $this->ro->title);
		}
	}
		
}
	
	