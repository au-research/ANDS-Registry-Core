<?php
/**
 * [format_relationship description]
 *
 * @param        $from_class
 * @param        $relationship_type
 * @param bool   $origin
 * @param string $to_class
 * @return string [type]                    [description]
 * @internal param $ [type] $from_class        [description]
 * @internal param $ [type] $to_class          [description]
 * @internal param $ [type] $relationship_type [description]
 * @internal param $ [type] $reverse           [description]
 */
function format_relationship($from_class, $relationship_type, $origin=false, $to_class='collection'){
    // default $to_class to collection in case ro not there!!
  //  return $origin;

    if(str_replace('party','',$to_class)!=$to_class){
            $to_class='party';
    }
    if(str_replace('party','',$from_class)!=$from_class){
        $from_class='party';
    }
	$typeArray['collection'] = array(
		"describes" => array("Describes", "Described by"),
		"hasAssociationWith" => array("Associated with", "Associated with"),
		"hasCollector" => array("Aggregated by", "Collector of"),
		"hasPart" => array("Contains", "Part of"),
		"isDescribedBy" => array("Described by","Describes"),
		"isLocatedIn" => array("Located in", "Location for"),
		"isLocationFor" => array("Location for","Located in"),
		"isManagedBy" => array("Managed by","Manages"),
		"isOutputOf" => array("Output of","Produces"),
		"isOwnedBy" => array("Owned by","Owner of"),
		"isPartOf" => array("Part of","Contains"),
		"supports" => array("Supports", "Supported by"),
		"enriches" =>array("Enriches", "Enriched by"),
		"isEnrichedBy" =>array("Enriched by", "Enriches"),
		"makesAvailable" =>array("Makes available", "Available through"),
		"isPresentedBy" =>array("Presented by", "Presents"),
		"presents" =>array("Presents", "Presented by"),
		"isDerivedFrom" =>array("Derived from", "Derives"),
		"hasDerivedCollection" =>array("Derives", "Derived From"),
		"isAvailableThrough" =>array("Available through", "Makes available"),	
		"isProducedBy" =>array("Produced by", "Produces"),
		"produces" =>array("Produces", "Produced by"),
		"isOperatedOnBy" =>array("Is operated on by", "Operates on"),
        "hasParticipant" => array("Participant","Undertaken by"),
		"hasPrincipalInvestigator" =>array("Principal investigator", "Principal investigator of"),
		"isPrincipalInvestigator" =>array("Principal investigator of", "Principal investigator"),
		"hasValueAddedBy" =>array("Value added by", "Adds value"),
		"pointOfContact" =>array("Point of Contact", "Is point of contact for"),
        "isFundedBy" => array("Funded by","Funds"),
        "isVersionOf" =>array("Is version of", "Has version"),
        "hasVersion" =>array("Has version", "Is version of")
	);
	$typeArray['party'] = array(
		"hasAssociationWith" => array("Associated with", "Associated with"),
		"hasMember" => array("Has member", "Member of"),
		"hasPart" => array("Has part", "Part of"),
		"isCollectorOf" => array("Collector of","Aggregated by"),
		"isFundedBy" => array("Funded by","Funds"),
		"isFunderOf" => array("Funds","Funded by"),
		"isManagedBy" => array("Managed by","Manages"),
		"isManagerOf" => array("Manages","Managed by"),
		"isMemberOf" => array("Member of","Has member"),
		"isOwnedBy" => array("Owned by","Owner of"),
		"isOwnerOf" => array("Owner of", "Owned by"),
		"isParticipantIn" => array("Participant in","Participant"),
		"isPartOf" => array("Part of","Participant in"),
		"enriches" =>array("Enriches", "Enriched by"),
		"makesAvailable" =>array("Makes available", "Available through"),
		"isEnrichedBy" =>array("Enriched by", "Enriches"),
        "hasParticipant" => array("Participant","Undertaken by"),
		"hasPrincipalInvestigator" =>array("Principal investigator", "Principal investigator of"),
		"isPrincipalInvestigatorOf" =>array("Principal investigator of", "Principal investigator"),
        "isPrincipalInvestigator" =>array("Principal investigator of", "Principal investigator"),
        "manages" => array("Manages", "Managed by")
	);
	$typeArray['service'] = array(
		"hasAssociationWith" =>  array("Associated with", "Associated with"),
		"hasPart" => array("Includes", "Part of"),
		"isManagedBy" => array("Managed by","Manages"),
		"isOwnedBy" => array("Owned by","Owner of"),
		"isPartOf" => array("Part of","Has part"),
		"isSupportedBy" => array("Supported by","Supports"),
		"enriches" =>array("Enriches", "Enriched by"),
		"makesAvailable" =>array("Makes available", "Available through"),
		"isPresentedBy" =>array("Presented by", "Presents"),
		"presents" =>array("Presents", "Presented by"),
		"produces" =>array("Produces", "Produced by"),
		"isProducedBy" =>array("Produced by", "Produces"),
		"operatesOn" =>array("Operates on", "Operated on"),
		"isOperatedOnBy" =>array("Operated on", "Operates on"),		
		"addsValueTo" =>array("Adds value to", "Value added by"),
		"hasPrincipalInvestigator" =>array("Principal investigator", "Principal investigator of"),
		"isPrincipalInvestigator" =>array("Principal investigator of", "Principal investigator"),
	);
	$typeArray['activity'] = array(
		"hasAssociationWith" =>   array("Associated with", "Associated with"),
		"hasOutput" => array("Produces","Output of"),
		"hasPart" => array("Includes","Part of"),
		"hasParticipant" => array("Participant","Participant in"),
        "isParticipantIn" => array("Participant in","Undertaken by"),
		"isFundedBy" => array("Funded by","Funds"),
		"isManagedBy" => array("Managed by","Manages"),
		"isOwnedBy" => array("Owned by","Owner of"),
		"isPartOf" => array("Part of","Includes"),
		"enriches" =>array("Enriches", "Enriched by"),
		"makesAvailable" =>array("Makes available", "Available through"),
		"hasPrincipalInvestigator" =>array("Principal investigator", "Principal investigator of"),
		"isPrincipalInvestigator" =>array("Principal investigator of", "Principal investigator"),
	);

	//$allTypesArray = array_merge($typeArray['collection'],$typeArray['party'],$typeArray['service'],$typeArray['activity']);
	if ($origin != 'EXPLICIT' && $origin != 'CONTRIBUTOR' && $origin != 'IDENTIFIER' && $origin != 'GRANTS') {
		//reverse
		return (isset($typeArray[$to_class][$relationship_type]) ? $typeArray[$to_class][$relationship_type][1] : from_camel_case($relationship_type));
	} else {
		return (isset($typeArray[$from_class][$relationship_type]) ? $typeArray[$from_class][$relationship_type][0] : from_camel_case($relationship_type));
	}
}

/**
 * Returns the reverse form of a relationship
 *
 * @param $relation
 * @return string
 */
function getReverseRelationshipString($relation) {
	switch ($relation) {
		case "describes" : return "isDescribedBy"; break;
		case "isDescribedBy" : return "describes"; break;
		case "hasPart" : return "isPartOf"; break;
		case "isPartOf" : return "hasPart"; break;
		case "hasAssociationWith" : return "hasAssociationWith"; break;
		case "hasCollector" : return "isCollectorOf"; break;
		case "isCollectorOf" : return "hasCollector"; break;
		case "hasPrincipalInvestigator" : return "isPrincipalInvestigatorOf"; break;
		case "isPrincipalInvestigatorOf" : return "hasPrincipalInvestigator"; break;
		case "isLocatedIn" : return "isLocationFor"; break;
		case "isLocationFor" : return "isLocatedIn"; break;
		case "manages": return "isManagedBy" ; break;
		case "isManagedBy" : return "isManagerOf"; break;
		case "isManagerOf" : return "isManagedBy"; break;
		case "isOutputOf" : return "hasOutput"; break;
		case "hasOutput" : return "isOutputOf"; break;
		case "isOwnedBy" : return "isOwnerOf"; break;
		case "isOwnerOf" : return "isOwnedBy"; break;
		case "supports" : return "isSupportedBy"; break;
		case "isSupportedBy" : return "supports"; break;
		case "isEnrichedBy" : return "enriches"; break;
		case "enriches" : return "isEnrichedBy"; break;
		case "isDerivedFrom" : return "hasDerivedCollection"; break;
		case "hasDerivedCollection" : return "isDerivedFrom"; break;
		case "isAvailableThrough" : return "makesAvailable"; break;
		case "makesAvailable" : return "isAvailableThrough"; break;
		case "isProducedBy" : return "produces"; break;
		case "produces" : return "isProducedBy"; break;
		case "isPresentedBy" : return "presents"; break;
		case "presents" : return "isPresentedBy"; break;
		case "hasValueAddedBy" : return "addsValueTo"; break;
		case "addsValueTo" : return "hasValueAddedBy"; break;
		case "isOperatedOnBy" : return "operatesOn"; break;
		case "operatesOn" : return "isOperatedOnBy"; break;
		case "isCitedBy" : return "cites"; break;
		case "cites" : return "isCitedBy"; break;
		case "isReferencedBy" : return "references"; break;
		case "references" : return "isReferencedBy"; break;
		case "isDocumentedBy" : return "documents"; break;
		case "documents" : return "isDocumentedBy"; break;
		case "isSupplementedBy" : return "isSupplementTo"; break;
		case "isSupplementTo" : return "isSupplementedBy"; break;
		case "hasParticipant" : return "isParticipantIn"; break;
		case "isParticipantIn" : return "hasParticipant"; break;
		case "isFundedBy" : return "isFunderOf"; break;
		case "isFunderOf" : return "isFundedBy"; break;
		case "isMemberOf" : return "hasMember"; break;
		case "hasMember" : return "isMemberOf"; break;
		case "isReviewedBy" : return "reviews"; break;
		case "reviews" : return "isReviewedBy"; break;
        case "isVersionOf" : return "hasVersion"; break;
        case "hasVersion" : return "isVersionOf"; break;
	}
	return $relation;
}


/**
 * Returns the reverse form of a relationship
 *
 * @param $relation
 * @return string
 */
function getReverseRelationshipStringCI($relation) {
    $ciRelation = strtolower($relation);
    switch ($ciRelation) {
        case strtolower("describes") : return "isDescribedBy"; break;
        case strtolower("isDescribedBy") : return "describes"; break;
        case strtolower("hasPart") : return "isPartOf"; break;
        case strtolower("isPartOf") : return "hasPart"; break;
        case strtolower("hasAssociationWith") : return "hasAssociationWith"; break;
        case strtolower("hasCollector") : return "isCollectorOf"; break;
        case strtolower("isCollectorOf") : return "hasCollector"; break;
        case strtolower("hasPrincipalInvestigator") : return "isPrincipalInvestigatorOf"; break;
        case strtolower("isPrincipalInvestigatorOf") : return "hasPrincipalInvestigator"; break;
        case strtolower("isLocatedIn") : return "isLocationFor"; break;
        case strtolower("isLocationFor") : return "isLocatedIn"; break;
        case strtolower("manages") : return "isManagedBy" ; break;
        case strtolower("isManagedBy") : return "isManagerOf"; break;
        case strtolower("isManagerOf") : return "isManagedBy"; break;
        case strtolower("isOutputOf") : return "hasOutput"; break;
        case strtolower("hasOutput") : return "isOutputOf"; break;
        case strtolower("isOwnedBy") : return "isOwnerOf"; break;
        case strtolower("isOwnerOf") : return "isOwnedBy"; break;
        case strtolower("supports") : return "isSupportedBy"; break;
        case strtolower("isSupportedBy") : return "supports"; break;
        case strtolower("isEnrichedBy") : return "enriches"; break;
        case strtolower("enriches") : return "isEnrichedBy"; break;
        case strtolower("isDerivedFrom") : return "hasDerivedCollection"; break;
        case strtolower("hasDerivedCollection") : return "isDerivedFrom"; break;
        case strtolower("isAvailableThrough") : return "makesAvailable"; break;
        case strtolower("makesAvailable") : return "isAvailableThrough"; break;
        case strtolower("isProducedBy") : return "produces"; break;
        case strtolower("produces") : return "isProducedBy"; break;
        case strtolower("isPresentedBy") : return "presents"; break;
        case strtolower("presents") : return "isPresentedBy"; break;
        case strtolower("hasValueAddedBy") : return "addsValueTo"; break;
        case strtolower("addsValueTo") : return "hasValueAddedBy"; break;
        case strtolower("isOperatedOnBy") : return "operatesOn"; break;
        case strtolower("operatesOn") : return "isOperatedOnBy"; break;
        case strtolower("isCitedBy") : return "cites"; break;
        case strtolower("cites") : return "isCitedBy"; break;
        case strtolower("isReferencedBy") : return "references"; break;
        case strtolower("references") : return "isReferencedBy"; break;
        case strtolower("isDocumentedBy") : return "documents"; break;
        case strtolower("documents") : return "isDocumentedBy"; break;
        case strtolower("isSupplementedBy") : return "isSupplementTo"; break;
        case strtolower("isSupplementTo") : return "isSupplementedBy"; break;
        case strtolower("hasParticipant") : return "isParticipantIn"; break;
        case strtolower("isParticipantIn") : return "hasParticipant"; break;
        case strtolower("isFundedBy") : return "isFunderOf"; break;
        case strtolower("isFunderOf") : return "isFundedBy"; break;
        case strtolower("isMemberOf") : return "hasMember"; break;
        case strtolower("hasMember") : return "isMemberOf"; break;
        case strtolower("isReviewedBy") : return "reviews"; break;
        case strtolower("reviews") : return "isReviewedBy"; break;
        case strtolower("isVersionOf") : return "hasVersion"; break;
        case strtolower("hasVersion") : return "isVersionOf"; break;
    }
    return $relation;
}
/**
 * Resolve Identifier to a link
 * References found in links.php
 *
 * todo check viability with team
 *
 * @param $type
 * @param $value
 * @return string
 */
function getResolvedLinkForIdentifier($type, $value)
{

	$urlValue = $value;
	switch ($type) {
		case 'handle':
			if (strpos($value, 'http://hdl.handle.net/') === false) {
				$urlValue = 'http://hdl.handle.net/' . $value;
			}
			return 'Handle : <a class="identifier" href="' . $urlValue . '" title="Resolve this handle">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/handle_icon.png',
				'base_path') . '" alt="Handle icon"></a><br/>';
			break;
        case 'raid':
            if (strpos($value, 'http://hdl.handle.net/') === false) {
                $urlValue = 'http://hdl.handle.net/' . $value;
            }
            return 'RAID : <a class="identifier" href="' . $urlValue . '" title="Resolve this handle">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/handle_icon.png',
                'base_path') . '" alt="Handle icon"></a><br/>';
            break;
		case 'purl':
			if (strpos($value, 'http://purl.org/') === false) {
				$urlValue = 'http://purl.org/' . $value;
			}
			return 'PURL : <a class="identifier" href="' . $urlValue . '" title="Resolve this purl identifier">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/external_link.png',
				'base_path') . '" alt="PURL icon"></a><br/>';
			break;
        case 'igsn':
            if (strpos($value, 'http://igsn.org/') === false) {
                $urlValue = 'http://igsn.org/' . $value;
            }
            return 'IGSN : <a class="identifier" href="' . $urlValue . '" title="Resolve this purl identifier">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/external_link.png',
                'base_path') . '" alt="IGSN icon"></a><br/>';
            break;
        case 'isni':
            if (strpos($value, 'http://') === false) {
                $urlValue = 'http://www.isni.org/' . $value;
            }
            return 'ISNI : <a class="identifier" href="' . $urlValue . '" title="Resolve this purl identifier">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/external_link.png',
                'base_path') . '" alt="ISNI icon"></a><br/>';
            break;
		case 'doi':
			if (strpos($value, 'doi.org/') === false) {
				$urlValue = 'https://doi.org/' . $value;
			}
			return 'DOI: <a class="identifier" href="' . $urlValue . '" title="Resolve this DOI">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/doi_icon.png',
				'base_path') . '" alt="DOI icon"></a><br/>';
			break;
        case 'url':
		case 'uri':
			if (strpos($value, 'http://') === false && strpos($value, 'https://') === false) {
				$urlValue = 'http://' . $value;
			}
			return 'URI : <a class="identifier" href="' . $urlValue . '" title="Resolve this URI">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/external_link.png',
				'base_path') . '" alt="URI icon"></a><br/>';
			break;
		case 'urn':
			return 'URN : <a class="identifier" href="' . $value . '" title="Resolve this URN">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/external_link.png',
				'base_path') . '" alt="URI icon"></a><br/>';
			break;
        case 'grid':
            if (!(strpos($value, 'http://') === false) && !(strpos($value, 'https://') === false)) {
                $urlValue = $value;
                return 'GRID : <a class="identifier" href="' . $urlValue . '" title="Resolve this URI">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/external_link.png',
                    'base_path') . '" alt="URI icon"></a><br/>';
            }
            break;
        case 'scopusID':
            if (!(strpos($value, 'http://') === false) && !(strpos($value, 'https://') === false)) {
                $urlValue = $value;
                return 'Scopus : <a class="identifier" href="' . $urlValue . '" title="Resolve this URI">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/external_link.png',
                    'base_path') . '" alt="URI icon"></a><br/>';
            }
            break;

		case 'orcid':
			if (strpos($value, 'http://orcid.org/') === false) {
				$urlValue = 'http://orcid.org/' . $value;
			}
			return 'ORCID: <a class="identifier" href="' . $urlValue . '" title="Resolve this ORCID">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/orcid_icon.png',
				'base_path') . '" alt="ORCID icon"></a><br/>';
			break;
		case 'AU-ANL:PEAU':
			if (strpos($value, 'http://nla.gov.au/') === false) {
				$urlValue = 'http://nla.gov.au/' . $value;
			}
			return 'NLA: <a class="identifier" href="' . $urlValue . '" title="View the record for this party in Trove">' . $value . '<img class="identifier_logo" src="' . asset_url('assets/core/images/icons/nla_icon.png',
				'base_path') . '" alt="NLA icon"></a><br/>';
			break;
		case 'local':
			return "Local: " . $value . "<br/>";
			break;
		default:
			return strtoupper($type) . ": " . $value . "<br/>";
	}


}


function from_camel_case($str) {
	if(isset($str) && is_array($str))
	{
    	$str[0] = strtolower($str[0]);
    	$func = create_function('$c', 'return " " . strtolower($c[1]);');
    	$newStr = preg_replace_callback('/([A-Z])/', $func, $str);
    	return ucfirst($newStr);
	}elseif(is_string($str)){
        return sentenceCase($str);
    }
	else return '';
  }
