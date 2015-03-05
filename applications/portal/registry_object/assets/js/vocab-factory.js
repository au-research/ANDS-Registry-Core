app.factory('vocab_factory', function($http, $log){
	return {
		tree : {},
		subjects: {},
		get: function (term, filters) {
			var url = '';
			if (term) {
				url = '?uri='+term;
			}
			return $http.post(base_url+'registry_object/vocab/'+url, {'filters':filters}).then(function(response){
				return response.data
			});
		},
		isSelected: function(item, filters) {
			if (filters['subject_vocab_uri']) {
				// $log.debug(decodeURIComponent(filters['subject_vocab_uri']), item.uri);
				if(decodeURIComponent(filters['subject_vocab_uri'])==item.uri) {
					return true;
				} else if(angular.isArray(filters['subject_vocab_uri'])) {
					angular.forEach(filters['subject_vocab_uri'], function(content, index) {
						if(content==item.uri) {
							return true;
						}
					});
				}
			} else if(filters['subject']){
				var found = false;
				angular.forEach(this.subjects[filters['subject']], function(uri){
					if(uri==item.uri && !found) {
						found = true;
					}
				});
				return found;
			} else if(filters['anzsrc-for']){
				var found = false;
				if(filters['anzsrc-for']==item.notation){
					found = true;
				} else if (angular.isArray(filters['anzsrc-for'])) {
					angular.forEach(filters['anzsrc-for'], function(code){
						if(code==item.notation && !found) {
							found =  true;
						}
					});
				}
				return found;
			} else {
				return false;
			}
		},
		getSubjects: function(){
			return $http.get(base_url+'registry_object/getSubjects').then(function(response){
				return response.data
			});
		},
		resolveSubjects: function(subjects){
			return $http.post(base_url+'registry_object/resolveSubjects', {data:subjects}).then(function(response){
				return response.data
			});
		}
	}
});