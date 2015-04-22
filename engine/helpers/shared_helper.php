<?php
/**
 * [format_relationship description]
 * @param  [type] $from_class        [description]
 * @param  [type] $to_class          [description]
 * @param  [type] $relationship_type [description]
 * @param  [type] $reverse           [description]
 * @return [type]                    [description]
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
		"isOutputOf" => array("Output of","Outputs"),
		"isOwnedBy" => array("Owned by","Owns"),
		"isPartOf" => array("Part of","Contains"),
		"supports" => array("Supports", "Supported by"),
		"enriches" =>array("Enriches", "Enriched by"),
		"isEnrichedBy" =>array("Enriched by", "Enriches"),
		"makesAvailable" =>array("Makes available", "Available through"),
		"isPresentedBy" =>array("Presented by", "Presents"),
		"presents" =>array("Presents", "Presented by"),
		"isDerivedFrom" =>array("Derived from", "Derives"),
		"hasDerivedCollection" =>array("Derives", "Derived From"),
		"supports" =>array("Supports", "Supported by"),	
		"isAvailableThrough" =>array("Available through", "Makes available"),	
		"isProducedBy" =>array("Produced by", "Produces"),
		"produces" =>array("Produces", "Produced by"),
		"isOperatedOnBy" =>array("Is operated on by", "Operates on"),
		"hasPrincipalInvestigator" =>array("Principal investigator", "Principal investigator of"),
		"isPrincipalInvestigator" =>array("Principal investigator of", "Principal investigator"),
		"hasValueAddedBy" =>array("Value added by", "Adds value"),
		"pointOfContact" =>array("Point of Contact", "Is point of contact for"),
	);
	$typeArray['party'] = array(
		"hasAssociationWith" => array("Associated with", "Associated with"),
		"hasMember" => array("Has member", "Member of"),
		"hasPart" => array("Has part", "Part of"),
		"isCollectorOf" => array("Collector of","Collected by"),
		"isFundedBy" => array("Funded by","Funds"),
		"isFunderOf" => array("Funds","Funded by"),
		"isManagedBy" => array("Managed by","Manages"),
		"isManagerOf" => array("Manages","Managed by"),
		"isMemberOf" => array("Member of","Has member"),
		"isOwnedBy" => array("Owned by","Owns"),
		"isOwnerOf" => array("Owner of", "Owned by"),
		"isParticipantIn" => array("Participant in","Participant"),
		"isPartOf" => array("Part of","Participant in"),
		"enriches" =>array("Enriches", "Enriched by"),
		"makesAvailable" =>array("Makes available", "Available through"),
		"isEnrichedBy" =>array("Enriched by", "Enriches"),
		"hasPrincipalInvestigator" =>array("Principal investigator", "Principal investigator of"),
		"isPrincipalInvestigatorOf" =>array("Principal investigator of", "Principal investigator"),
        "isPrincipalInvestigator" =>array("Principal investigator of", "Principal investigator"),
	);
	$typeArray['service'] = array(
		"hasAssociationWith" =>  array("Associated with", "Associated with"),
		"hasPart" => array("Includes", "Part of"),
		"isManagedBy" => array("Managed by","Manages"),
		"isOwnedBy" => array("Owned by","Owns"),
		"isPartOf" => array("Part of","Has part"),
		"isSupportedBy" => array("Supported by","Supports"),
		"enriches" =>array("Enriches", "Enriched by"),
		"makesAvailable" =>array("Makes available", "Available through"),
		"isPresentedBy" =>array("Presented by", "Presents"),
		"presents" =>array("Presents", "Presented by"),
		"produces" =>array("Produces", "Produced by"),
		"isProducedBy" =>array("Produced by", "Produces"),
		"operatesOn" =>array("Operates on", "Operated by"),
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
		"isOwnedBy" => array("Owned by","Owns"),
		"isPartOf" => array("Part of","Includes"),
		"enriches" =>array("Enriches", "Enriched by"),
		"makesAvailable" =>array("Makes available", "Available through"),
		"hasPrincipalInvestigator" =>array("Principal investigator", "Principal investigator of"),
		"isPrincipalInvestigator" =>array("Principal investigator of", "Principal investigator"),
	);
	
	//$allTypesArray = array_merge($typeArray['collection'],$typeArray['party'],$typeArray['service'],$typeArray['activity']);

	if($origin != 'EXPLICIT' && $origin != 'CONTRIBUTOR' && $origin != 'IDENTIFIER'){//reverse
		return (isset($typeArray[$to_class][$relationship_type]) ? $typeArray[$to_class][$relationship_type][1] : from_camel_case($relationship_type));
	}
	else 
	{
		return (isset($typeArray[$from_class][$relationship_type]) ? $typeArray[$from_class][$relationship_type][0] : from_camel_case($relationship_type));
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
