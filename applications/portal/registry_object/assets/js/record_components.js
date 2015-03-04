angular.module('record_components',[])

.factory('record_factory', function($http){
	return{
		stat: function(id) {
			var promise = $http.get(base_url+'registry_object/stat/'+id).then(function(response){
				return response.data;
			});
			return promise;
		},
        add_stat: function(id, type, value) {
            var data = {
                type:type,
                value:value
            };
            var promise = $http.post(base_url+'registry_object/add_stat/'+id, {data:data}).then(function(response){
                return response.data;
            });
            return promise;
        }
	}
})

;