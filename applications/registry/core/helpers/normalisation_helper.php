<?php
/* These functions are intended to be used to transform RIFCS structured elements into a plain-text representation

/* Takes a RIFCS location/address element and formats it into a concatenated string format */
function normalisePhysicalAddress(SimpleXMLElement &$sxml)
{
	// Order elements according to the following metric
	$type_weights = array(
		"fullName" => 10,
		"organisationName" => 15,
		"buildingOrPropertyName" => 20,
		"flatOrUnitNumber" => 25,
		"floorOrLevelNumber" => 30,
		"lotNumber" => 35,
		"houseNumber" => 40,
		"streetName" => 45,
		"postalDeliveryNumberPrefix" => 50,
		"postalDeliveryNumberValue" => 55,
		"postalDeliveryNumberSuffix" => 60,
		"addressLine" => 65,
		"suburbOrPlaceOrLocality" => 70,
		"stateOrTerritory" => 75,
		"postCode" => 80,
		"country" => 85,
		"locationDescriptor" => 90,
		"deliveryPointIdentifier" => 95
	);
	
	$address_lines = array();
	if(isset($sxml->addressPart))
	{
		$count = 0;
		foreach ($sxml->addressPart AS $a)
		{
			$count++;
			$type = trim((string) $a['type']);
			if (array_key_exists($type, $type_weights))
			{
				$key = $type_weights[$type];
			}
			else
			{
				$key = 99;
			}
			$key .= sprintf("%02d", $count); // yeah, this will fail if there are more than 10 address parts of the same type...

			$address_lines[$key] = trim((string) $a);
		}

		ksort($address_lines, SORT_NUMERIC);
	}
	return implode(NL, $address_lines);
}

/* Takes a RIFCS location/address element and formats it into an array of identifier elements */
function normaliseIdentifier(SimpleXMLElement &$sxml)
{
	$_orcidPrefix = "http://orcid.org/";
	$_nlaPrefix = "http://nla.gov.au/";

	if (!$sxml) { return ""; }

	if (strtolower($sxml['type']) == "orcid")
	{
		if (strpos($sxml, $_orcidPrefix) === FALSE)
		{
			return $_orcidPrefix . (string) $sxml;
		}
		else
		{
			return (string) $sxml;
		}
	}
	else if ($sxml['type'] == "AU-ANL:PEAU")
	{
		if (strpos($sxml, $_nlaPrefix) === FALSE)
		{
			return $_nlaPrefix . (string) $sxml;
		}
		else
		{
			return (string) $sxml;
		}
	}
	else if (in_array(strtolower($sxml['type']), array("uri","purl")))
	{
		return (string) $sxml;
	}
	else 
	{
		return (strtolower((string) $sxml['type']) . ": " . (string) $sxml);
	}

	return "";
}


/* Takes a RIFCS relatedInfo element and normalise it into a concatenated string  */
function normalisePublicationRelatedInfo(SimpleXMLElement &$sxml)
{
	if (!$sxml || $sxml['type'] != 'publication') { return ""; }
	$normalised_string = "";

	if (isset($sxml->title) && $sxml->title)
	{
		$normalised_string .= $sxml->title;
	}

	if (isset($sxml->identifier) && $sxml->identifier)
	{
		$normalised_string .= " <" . normaliseIdentifier($sxml->identifier) . ">";
	}

	if (isset($sxml->notes) && $sxml->notes)
	{
		$normalised_string .= " (" . $sxml->notes . ")";
	}

	return $normalised_string;
}