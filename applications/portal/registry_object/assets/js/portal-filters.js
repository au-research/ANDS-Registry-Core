angular.module('portal-filters', [])
	.filter('filter_name', function(){
		return function(text) {
			switch(text) {
				case 'q': return 'All Fields' ;break;
				case 'cq': return 'Advanced Query' ;break;
				case 'title': return 'Title' ;break;
				case 'identifier': return 'Identifier' ;break;
				case 'related_people': return 'Related People' ;break;
				case 'related_organisations': return 'Related Organisations' ;break;
				case 'description': return 'Description' ;break;
				case 'subject': return 'Subject' ;break;
				case 'access_rights': return 'Access'; break;
				case 'group': return 'Data Provider'; break;
				case 'license_class': return 'Licence'; break;
				case 'type': return 'Type'; break;
				case 'subject_vocab_uri': return 'Subject Vocabulary URI'; break;
				case 'anzsrc-for': return 'Subjects ANZSRC-FOR'; break;
				case 'anzsrc-seo': return 'Subjects ANZSRC-SEO'; break;
				case 'year_from': return 'Time Period (from)'; break;
				case 'year_to': return 'Time Period (to)'; break;
				case 'funding_scheme': return 'Funding Scheme'; break;
				case 'funding_from': return 'Funding From'; break;
				case 'funding_to': return 'Funding To'; break;
				case 'funders': return 'Funder'; break;
				case 'administering_institution': return 'Managing Institution'; break;
				case 'institution': return 'Institution'; break;
				case 'activity_status': return 'Status'; break;
				case 'researcher': return 'Researcher'; break;
				case 'related_party_one_id': return 'Related Researcher'; break;
				case 'scot': return 'Schools of Online Thesaurus'; break;
				case 'pont': return 'Powerhouse Museum Object Name Thesaurus'; break;
				case 'psychit': return 'Thesaurus of psychological index terms'; break;
				case 'anzsrc': return 'ANZSRC'; break;
				case 'apt': return 'Australian Pictorial Thesaurus'; break;
				case 'gcmd': return 'GCMD Keyword'; break;
				case 'lcsh': return 'LCSH'; break;
				case 'keywords': return 'Keyword'; break;
				case 'refine': return 'Keyword'; break;
				case 'subject_value_resolved': return 'Subject'; break;
				case 'commencement_to': return 'Commencement To'; break;
				case 'commencement_from': return 'Commencement From'; break;
				case 'completion_to': return 'Completion To'; break;
				case 'completion_from': return 'Completion From'; break;
				case 'spatial': return 'Location'; break;
				default: return text;
			}
		}
	})
	.filter('highlightreadable', function() {
		return function(text) {
			switch(text) {

				case 'identifier_value_search' : return 'Identifier' ; break;
				// case 'access' : return 'Access Details' ; break;
				case 'related_party_one_search' : return 'Related People' ; break;
				case 'related_party_multi_search' : return 'Related Organisations' ; break;
				case 'group_search' : return 'Data Provider' ; break;
				case 'related_info_search' : return 'Related Data' ; break;
				case 'related_activity_search' : return 'Related Project or Grant' ; break;
				case 'related_service_search' : return 'Related Tool or Service' ; break;
				case 'related_info_search' : return 'Related Resource' ; break;
				case 'subject_value_resolved_search' : return 'Subject' ; break;
				case 'description_value' : return 'Description' ; break;
				case 'date_to' : return 'Dates' ; break;
				case 'date_to' : return 'Dates' ; break;
				case 'date_from' : return 'Coverage' ; break;
				case 'citation_info_search' : return 'Citation ' ; break;
				default : return text;
			}
		}
	})
	.filter('socialreadable', function(){
		return function(text) {
			switch(text) {
				case 'AUTHENTICATION_SOCIAL_FACEBOOK' : return 'Facebook'; break;
				case 'AUTHENTICATION_SOCIAL_TWITTER' : return 'Twitter'; break;
				case 'AUTHENTICATION_SOCIAL_GOOGLE' : return 'Google'; break;
				case 'AUTHENTICATION_SOCIAL_LINKEDIN' : return 'LinkedIn'; break;
			}
		}
	})
	.filter('filter_value', function($sce){
		return function(text) {
			if (angular.isArray(text)) {
				var html = '<ul>';
				angular.forEach(text, function(content) {
					html+='<li>'+content+'</li>';
				});
				html+='</ul>';
				return $sce.trustAsHtml(html);
			} else {
				return $sce.trustAsHtml(text);
			}
		}
	})
	.filter('toTitleCase', function($log){
		return function(str){
			return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});	
		}
	})
	.filter('getLabelFor', function($log){
		return function(value, filter) {
			var ret = '';
			angular.forEach(filter, function(f){
				if(f.value==value) {
					ret = f.label;
				}
			});
			return ret;
		}
	})
	.filter('truncate', function () {
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
	})
	.filter('text', ['$sce', function($sce){
		return function(text){
			var tmp = document.createElement("DIV");
			tmp.innerHTML = text;
			return tmp.textContent || tmp.innerText || "";
			// var decoded = $('<div/>').html(text).text();
			// return decoded;
		}
	}])
	.filter('trustAsHtml', ['$sce', function($sce){
		return function(text){
			var decoded = $('<div/>').html(text).text();
			return $sce.trustAsHtml(decoded);
		}
	}])
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
	})
	.filter('orderObjectBy', function($log) {
	  return function(items, field, reverse) {
	    var filtered = [];
	    angular.forEach(items, function(item) {
	      filtered.push(item);
	    });
	    filtered.sort(function (a, b) {
	    	var asort = (typeof(a[field])=='string' ? a[field].toLowerCase() : a[field]);
	    	var bsort = (typeof(b[field])=='string' ? b[field].toLowerCase() : b[field]);
	    	return (asort > bsort ? 1 : -1);
	    });
	    if(reverse) filtered.reverse();
	    return filtered;
	  };

}).filter('sortObjectBy', function($log) {
    return function(items, field, reverse) {
        var sortArray = ['open','conditional','restricted','open licence','non-commercial licence','non-derivative licence','restrictive licence','no licence','other','unknown']
        var filtered = [];
        sortArray.forEach(function(element){
            angular.forEach(items, function(item) {
                if(item.name==element){
                filtered.push(item);
                }
            });
        });
        return filtered;
    };

})

;

;