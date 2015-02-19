angular.module('record_components',[])

.factory('record_factory', function($http){
	return{
		stat: function(id) {
			var promise = $http.get(base_url+'registry_object/stat/'+id).then(function(response){
				return response.data;
			});
			return promise;
		}
	}
})

;