angular.module('profile_components',[])

.factory('profile_factory', function($http){
	return{
        check_is_bookmarked: function(id) {
           return $http.get(base_url+'profile/is_bookmarked/'+id).then(function(response){
               return response.data;
           });
        },
		add_user_data: function(type, data) {
			var promise = $http.post(base_url+'profile/modify_user_data/'+type+'/add', {'data':data}).then(function(response){
				return response.data;
			});
			return promise;
		},
        modify_user_data: function(type, action, data) {
            var promise = $http.post(base_url+'profile/modify_user_data/'+type+'/'+action, {'data':data}).then(function(response){
                return response.data;
            });
            return promise;
        },
		get_user: function() {
			var promise = $http.get(base_url+'profile/current_user').then(function(response){
				return response.data;
			});
			return promise;
		},
		get_user_available_actions: function(){
			var actions = {
                "saved_record_group":['move', 'delete', 'export'],
                "saved_record":['open', 'move', 'delete', 'export'],
                "saved_search_group":['refresh', 'delete'],
                "saved_search":['refresh', 'delete']
            };
			return actions;
		}
	}
})

;