angular.module('profile_components',[])

.factory('profile_factory', function($http){
	return{
		add_user_data: function(type, data) {
			var promise = $http.post(base_url+'profile/add_user_data/'+type, {'data':data}).then(function(response){
				return response.data;
			});
			return promise;
		},
		get_user_data: function(type) {
			var promise = $http.get(base_url+'profile/current_user').then(function(response){
				return response.data;
			});
			return promise;
		}
	}
})

;