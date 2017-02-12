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
			/* From Joel Benn, 2014 */
			$title = strtolower(" ".$this->ro->title." ");
			$CommonWords = array(' a ', ' about ', ' above ', ' above ', ' across ', ' after ', ' afterwards ',
				' again ', ' against ', ' all ', ' almost ', ' alone ', ' along ', ' already ', ' also ', ' although ',
				' always ', ' am ', ' among ', ' amongst ', ' amoungst ', ' amount”,  “an ', ' and ', ' another ', ' any ',
				' anyhow ', ' anyone ', ' anything ', ' anyway ', ' anywhere ', ' are ', ' around ', ' as ', ' at ', ' back ',
				' be ', ' became ', ' because ', ' become ', ' becomes ', ' becoming ', ' been ', ' before ', ' beforehand ',
				' behind ', ' being ', ' below ', ' beside ', ' besides ', ' between ', ' beyond ', ' bill ', ' both ',
				' bottom ', ' but ', ' by ', ' call ', ' can ', ' cannot ', ' cant ', ' co ', ' con ', ' could ', ' couldnt ',
				' cry ', ' de ', ' describe ', ' detail ', ' do ', ' done ', ' down ', ' due ', ' during ', ' each ', ' eg ',
				' eight ', ' either ', ' eleven”,”else ', ' elsewhere ', ' empty ', ' enough ', ' etc ', ' even ', ' ever ',
				' every ', ' everyone ', ' everything ', ' everywhere ', ' except ', ' few ', ' fifteen ', ' fify ', ' fill ',
				' find ', ' fire ', ' first ', ' five ', ' for ', ' former ', ' formerly ', ' forty ', ' found ', ' four ',
				' from ', ' front ', ' full ', ' further ', ' get ', ' give ', ' go ', ' had ', ' has ', ' hasnt ', ' have ',
				' he ', ' hence ', ' her ', ' here ', ' hereafter ', ' hereby ', ' herein ', ' hereupon ', ' hers ', ' herself ',
				' him ', ' himself ', ' his ', ' how ', ' however ', ' hundred ', ' ie ', ' if ', ' in ', ' inc ', ' indeed ', ' interest ',
				' into ', ' is ', ' it ', ' its ', ' itself ', ' keep ', ' last ', ' latter ', ' latterly ', ' least ', ' less ', ' ltd ',
				' made ', ' many ', ' may ', ' me ', ' meanwhile ', ' might ', ' mill ', ' mine ', ' more ', ' moreover ', ' most ',
				' mostly ', ' move ', ' much ', ' must ', ' my ', ' myself ', ' name ', ' namely ', ' neither ', ' never ',
				' nevertheless ', ' next ', ' nine ', ' no ', ' nobody ', ' none ', ' noone ', ' nor ', ' not ', ' nothing ',
				' now ', ' nowhere ', ' of ', ' off ', ' often ', ' on ', ' once ', ' one ', ' only ', ' onto ', ' or ', ' other ',
				' others ', ' otherwise ', ' our ', ' ours ', ' ourselves ', ' out ', ' over ', ' own”,”part ', ' per ', ' perhaps ',
				' please ', ' put ', ' rather ', ' re ', ' same ', ' see ', ' seem ', ' seemed ', ' seeming ', ' seems ', ' serious ',
				' several ', ' she ', ' should ', ' show ', ' side ', ' since ', ' sincere ', ' six ', ' sixty ', ' so ', ' some ',
				' somehow ', ' someone ', ' something ', ' sometime ', ' sometimes ', ' somewhere ', ' still ', ' such ', ' system ',
				' take ', ' ten ', ' than ', ' that ', ' the ', ' their ', ' them ', ' themselves ', ' then ', ' thence ', ' there ',
				' thereafter ', ' thereby ', ' therefore ', ' therein ', ' thereupon ', ' these ', ' they ', ' thickv ', ' thin ',
				' third ', ' this ', ' those ', ' though ', ' three ', ' through ', ' throughout ', ' thru ', ' thus ', ' to ',
				' together ', ' too ', ' top ', ' toward ', ' towards ', ' twelve ', ' twenty ', ' two ', ' un ', ' under ',
				' until ', ' up ', ' upon ', ' us ', ' very ', ' via ', ' was ', ' we ', ' well ', ' were ', ' what ',
				' whatever ', ' when ', ' whence ', ' whenever ', ' where ', ' whereafter ', ' whereas ', ' whereby ',
				' wherein ', ' whereupon ', ' wherever ', ' whether ', ' which ', ' while ', ' whither ', ' who ', ' whoever ',
				' whole ', ' whom ', ' whose ', ' why ', ' will ', ' with ', ' within ', ' without ', ' would ', ' yet ', ' you ',
				' your ', ' yours ', ' yourself ', ' yourselves ', ' the ', ' data ', ' record ');			
		return str_replace($CommonWords, array_fill(0, count($CommonWords), ' '), $title);
		}
	}

/*
a very primitive way of breaking up title into two list of words
based on their frequency in the description
a list of significants and non-significant words from the registry would be needed rather than relying on someone's skill on writing a description
*/

	function splitTitleBySignificance($title)
	{
		$titleArray = explode(' ', $title);
		$rankArray = array();
		$rankedQueryArray = array();
		$desciption = $this->ro->getMetadata('the_description');
		$minRank = 9999;
		$maxRank = 0;

		foreach($titleArray as $word)
		{
			if(strlen($word) > 2){
				preg_match_all("/".$word."/i", $desciption, $foundArray);
				
				$rank = sizeof($foundArray[0]);
				
				if($minRank > $rank) 
					$minRank = $rank;

				if($maxRank < $rank) 
					$maxRank = $rank;

    			$rankArray[$word] = $rank;
    		}
		}

		$mid = (($maxRank - $minRank +1) / 2);
		foreach($rankArray as $word=>$rank)
		{
			if($mid >= $rank)
			{
				if(isset($rankedQueryArray[0]))
					$rankedQueryArray[0] .= ',"'.$word.'"';
				else
					$rankedQueryArray[0] = '"'.$word.'"';
			}				
			else{
				if(isset($rankedQueryArray[1]))
					$rankedQueryArray[1] .= ',"'.$word.'"';
				else
					$rankedQueryArray[1] = '"'.$word.'"';
			}
		}
		return $rankedQueryArray;
	}
		
}
	
	