/**
 * Helper filters file for angularjs modules used within the PORTAL apps, useful for rendering data in different ways
 * 
 * NOTE: This file should be kept in sync across different branches of CORE
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
angular.module('portal-filters', []).
	filter('truncate', function () {
		return function (text, length, end) {
			if (isNaN(length))
				length = 10;
			if (end === undefined)
				end = "...";
			if (text.length <= length || text.length - end.length <= length) {
				return text;
			}
			else {
				return String(text).substring(0, length-end.length) + end;
			}
		};
	}).
    filter('removeHtml', function (){
		return function(text) {
			var html = $('<div/>').html(text).text();
			return $('<div/>').html(html).text();
		}
    }).
	filter('class_name', function(){
		return function(text){
			switch(text){
				case 'collection': return 'Collections';break;
				case 'activity': return 'Activities';break;
				case 'party': return 'Parties';break;
				case 'party_one': return 'People';break;
				case 'party_multi': return 'Organisations & Groups';break;
				case 'service': return 'Services';break;
				default: return text;break;
			}
		}
	}).
	filter('relationship', function(){
		return function(text, from_class){
			if(!from_class) return text;
			from_class = from_class.toLowerCase();

			if(from_class=='collection'){
				switch(text){
					case "describes": return "Describes";break;
					case "hasAssociationWith": return "Associated with";break;
					case "hasCollector": return "Aggregated by";break;
					case "hasPart": return "Contains";break;
					case "isDescribedBy": return "Described by";break;
					case "isLocatedIn": return "Located in";break;
					case "isLocationFor": return "Location for";break;
					case "isManagedBy": return "Managed by";break;
					case "isOutputOf": return "Output of";break;
					case "isOwnedBy": return "Owned by";break;
					case "isPartOf": return "Part of";break;
					case "supports": return "Supports";break;
					case "enriches" : return "Enriches";break;
					case "isEnrichedBy" : return "Enriched by";break;
					case "makesAvailable" : return "Makes available";break;
					case "isPresentedBy" : return "Presented by";break;
					case "presents" : return "Presents";break;
					case "isDerivedFrom" : return "Derived from";break;
					case "hasDerivedCollection" : return "Derives";break;
					case "supports" : return "Supports";break;
					case "isAvailableThrough" : return "Available through";break;
					case "isProducedBy" : return "Produced by";break;
					case "produces" : return "Produces";break;
					case "isOperatedOnBy" : return "Is operated on by";break;
					case "hasPrincipalInvestigator" : return "Principal investigator";break;
					case "isPrincipalInvestigator" : return "Principal investigator of";break;
					case "hasValueAddedBy" : return "Value added by";break;
					case "pointOfContact" : return "Point of Contact";break;
				}
			}else if(from_class=='party'){
				switch(text){
					case "hasAssociationWith" : return "Associated with"; break;
					case "hasMember" : return "Has member"; break;
					case "hasPart" : return "Has part"; break;
					case "isCollectorOf" : return "Collector of";break;
					case "isFundedBy" : return "Funded by";break;
					case "isFunderOf" : return "Funds";break;
					case "isManagedBy" : return "Managed by";break;
					case "isManagerOf" : return "Manages";break;
					case "isMemberOf" : return "Member of";break;
					case "isOwnedBy" : return "Owned by";break;
					case "isOwnerOf" : return "Owner of"; break;
					case "isParticipantIn" : return "Participant in";break;
					case "isPartOf" : return "Part of";break;
					case "enriches" :return "Enriches"; break;
					case "makesAvailable" :return "Makes available"; break;
					case "isEnrichedBy" :return "Enriched by"; break;
					case "hasPrincipalInvestigator" :return "Principal investigator"; break;
					case "isPrincipalInvestigatorOf" :return "Principal investigator of"; break;
				}
			}else if(from_class=='service'){
				switch(text){
					case "hasAssociationWith" : return "Associated with";break;
					case "hasPart" : return "Includes";break;
					case "isManagedBy" : return "Managed by";break;
					case "isOwnedBy" : return "Owned by";break;
					case "isPartOf" : return "Part of";break;
					case "isSupportedBy" : return "Supported by";break;
					case "enriches" :return "Enriches";break;
					case "makesAvailable" :return "Makes available";break;
					case "isPresentedBy" :return "Presented by";break;
					case "presents" :return "Presents";break;
					case "produces" :return "Produces";break;
					case "isProducedBy" :return "Produced by";break;
					case "operatesOn" :return "Operates on";break;
					case "isOperatedOnBy" :return "Operated on";break;
					case "addsValueTo" :return "Adds value to";break;
					case "hasPrincipalInvestigator" :return "Principal investigator";break;
					case "isPrincipalInvestigator" :return "Principal investigator of";break;
				}
			}else if(from_class=='activity'){
				switch(text){
					case "hasAssociationWith" : return "Associated with";break;
					case "hasOutput" : return "Produces";break;
					case "hasPart" : return "Includes";break;
					case "hasParticipant" : return "Undertaken by";break;
					case "isFundedBy" : return "Funded by";break;
					case "isManagedBy" : return "Managed by";break;
					case "isOwnedBy" : return "Owned by";break;
					case "isPartOf" : return "Part of";break;
					case "enriches" : return "Enriches";break;
					case "makesAvailable" : return "Makes available";break;
					case "hasPrincipalInvestigator" : return "Principal investigator";break;
					case "isPrincipalInvestigator" : return "Principal investigator of";break;
				}
			}
			return text;
		}
	})
;