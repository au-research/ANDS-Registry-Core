app.factory('vocabs_factory', function($http){
	return {
		getAll: function() {
			var promise = $http.get(base_url+'vocabs/services/vocabs').then(function(response){
				return response.data;
			});
			return promise;
		}
	}
})