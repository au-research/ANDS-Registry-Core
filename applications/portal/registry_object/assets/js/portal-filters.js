angular.module('portal-filters', [])
	.filter('filter_name', function(){
		return function(text) {
			switch(text) {
				case 'q': return 'All' ;break;
				case 'title': return 'Title' ;break;
				case 'identifier': return 'Identifier' ;break;
				case 'related_people': return 'Related People' ;break;
				case 'related_organisation': return 'Related Organisations' ;break;
				case 'description': return 'Description' ;break;
				case 'subject': return 'Subjects' ;break;
				case 'access_rights': return 'Access Rights'; break;
				case 'group': return 'Contributor'; break;
				case 'license_class': return 'Licenses'; break;
				case 'type': return 'Type'; break;
				case 'subject_vocab_uri': return 'Subject Vocabulary URI'; break;
				default: return text;
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
			var decoded = $('<div/>').html(text).text();
			return decoded;
		}
	}])
	.filter('trustAsHtml', ['$sce', function($sce){
		return function(text){
			var decoded = $('<div/>').html(text).text();
			return $sce.trustAsHtml(decoded);
		}
	}])
;