angular.module('search_components',[])

.factory('search_factory', function($http){
	return{
		search: function(filters){
			var promise = $http.post(base_url+'registry_object/filter', {'filters':filters}).then(function(response){
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
				if(term=='rows') value = parseInt(value);
				if(term && value && term!=''){

					if(filters[term]) {
						if(typeof filters[term]=='string') {
							var old = filters[term];
							filters[term] = [];
							filters[term].push(old);
							filters[term].push(value);
						} else if(typeof filters[term]=='object') {
							filters[term].push(value);
						}
					} else {
						filters[term] = value;
					}
					
				}
			});
			return filters;
		},
		filters_to_hash: function(filters) {
			var hash = '';
			$.each(filters, function(i,k){
				if(typeof k!='object'){
					hash+=i+'='+k+'/';
				} else if (typeof k=='object'){
					$.each(k, function(){
						hash+=i+'='+this+'/';
					});
				}
			});
			return hash;
		},
		advanced_fields: function() {
			var fields = [
				{'name':'terms', 'display':'Search Terms', 'active':true},
				{'name':'group', 'display':'Contributors'},
				{'name':'license_class', 'display':'License'},
				{'name':'type', 'display':'Types'},
				{'name':'spatial', 'display':'Spatial'},
				{'name':'class', 'display':'Class'}
			];
			return fields;
		}
	}
})

;