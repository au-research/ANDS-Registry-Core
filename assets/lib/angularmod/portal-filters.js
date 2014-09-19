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
			if(text){
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
	filter('config_readable', function(){
		return function(text){
			switch(text){
				case 'environment_colour': return 'Environment Colour';break;
				case 'environment_name': return 'Environment Name';break;
				case 'harvested_contents_path': return 'Harvested Content Path';break;
				case 'site_admin': return 'Site Administrator Name';break;
				case 'site_admin_email': return 'Site Administrator Email';break;
				case 'solr_url': return 'SOLR URL';break;
				case 'sissvoc_url': return 'Sissvoc Vocab Server URL';break;
				case 'shibboleth_sp': return 'Enable Shibboleth';break;
				case 'ROLE_USER': return 'User'; break;
				case 'ROLE_ORGANISATIONAL': return 'Organisation'; break;
				case 'ROLE_FUNCTIONAL': return 'Function'; break;
				case 'ROLE_DOI_APPID': return 'DOI Application ID'; break;
				case 'AUTHENTICATION_LDAP': return 'LDAP'; break;
				case 'AUTHENTICATION_SHIBBOLETH': return 'Shibboleth'; break;
				case 'AUTHENTICATION_BUILT_IN': return 'Built In'; break;
				case 'authentication_service_id': return 'Authentication Service ID';break;
				case 'created_when': return 'Created When';break;
				case 'created_who': return 'Created Who';break;
				case 'enabled': return 'Enabled';break;
				case 'id': return 'ID';break;
				case 'last_login': return 'Last Login';break;
				case 'modified_when': return 'Last Modified';break;
				case 'modified_who': return 'Modified Who';break;
				case 'name': return 'Name';break;
				case 'persistent_id': return 'Shibboleth Persistent ID';break;
				case 'role_id': return 'Role ID';break;
				case 'role_type_id': return 'Type';break;
				case 'shared_token': return 'Shibboleth Shared Token';break;
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
	.filter("timeago", function () {
	    //time: the time
	    //local: compared to what time? default: now
	    //raw: wheter you want in a format of "5 minutes ago", or "5 minutes"
	    return function (time, local, raw) {
	        if (!time) return "never";
	 
	        if (!local) {
	            (local = Date.now())
	        }

	 
	        if (angular.isDate(time)) {
	            time = time.getTime();
	        } else if (typeof time === "string") {
	        	var s = time;
				var bits = s.split(/\D/);
				var date = new Date(bits[0], --bits[1], bits[2], bits[3], bits[4]);
				time = date.getTime();
	        }

	     
	        if (angular.isDate(local)) {
	            local = local.getTime();
	        }else if (typeof local === "string") {
	            local = new Date(local).getTime();
	        }

	        // console.log(local, time);
	 
	        if (typeof time !== 'number' || typeof local !== 'number' || isNaN(time) || isNaN(local)) {
	            return;
	        }
	 
	        var
	            offset = Math.abs((local - time) / 1000),
	            span = [],
	            MINUTE = 60,
	            HOUR = 3600,
	            DAY = 86400,
	            WEEK = 604800,
	            MONTH = 2629744,
	            YEAR = 31556926,
	            DECADE = 315569260;
	        
	 
	        if (offset <= MINUTE)              span = [ '', raw ? 'now' : parseInt(offset) + ' seconds' ];
	        else if (offset < (MINUTE * 60))   span = [ Math.round(Math.abs(offset / MINUTE)), 'min' ];
	        else if (offset < (HOUR * 24))     span = [ Math.round(Math.abs(offset / HOUR)), 'hr' ];
	        else if (offset < (DAY * 7))       span = [ Math.round(Math.abs(offset / DAY)), 'day' ];
	        else if (offset < (WEEK * 52))     span = [ Math.round(Math.abs(offset / WEEK)), 'week' ];
	        else if (offset < (YEAR * 10))     span = [ Math.round(Math.abs(offset / YEAR)), 'year' ];
	        else if (offset < (DECADE * 100))  span = [ Math.round(Math.abs(offset / DECADE)), 'decade' ];
	        else if (isNaN(offset))			   span = [''];
	        else                               span = [ '', 'a long time' ];
	 
	        span[1] += (span[0] === 0 || span[0] > 1) ? 's' : '';
	        span = span.join(' ');
	 
	        if (raw === true) {
	            return span;
	        }
	        return (time <= local && !isNaN(time)) ? span + ' ago' : 'in ' + span;
	    }
	}).
	filter('orderObjectBy', function(){
	 return function(input, attribute) {
	    if (!angular.isObject(input)) return input;

	    var array = [];
	    for(var objectKey in input) {
	        array.push(input[objectKey]);
	    }

	    array.sort(function(a, b){
	        var alc = a[attribute].toLowerCase(),
	        	blc = b[attribute].toLowerCase();
	        return alc > blc ? 1 : alc < blc ? -1 : 0;
	    });
	    return array;
	 }
	});
;