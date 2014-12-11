angular.module('search_components',[])

.factory('search_factory', function($http){
	return{
		search: function(filters){
			var promise = $http.post('../registry_object/s', {'filters':filters}).then(function(response){
				return response.data;
			});
			return promise;
		},
		filters_from_hash:function(hash) {
			var xp = hash.split('/');
			var filters = {};
			$.each(xp, function(){
				var t = this.split('=');
				var term = t[0];
				var value = t[1];
				if(term && value && term!='')filters[term] = value;
			});
			return filters;
		},
		advanced_fields: function() {
			var fields = [
				{'name':'terms', 'display':'Search Terms', 'active':true},
				{'name':'group', 'display':'Contributors'},
				{'name':'licence_class', 'display':'License'},
				{'name':'types', 'display':'Types'},
			];
			return fields;
		}
	}
})

;