app.factory('vocabs_factory', function($http){
	return {
		getAll: function() {
			var promise = $http.get(base_url+'vocabs/services/vocabs').then(function(response){
				return response.data;
			});
			return promise;
		},
		add: function(data) {
			var promise = $http.post(base_url+'vocabs/services/vocabs', {data:data}).then(function(response){
				return response.data;
			});
			return promise;
		},
		get: function(slug) {
			var promise = $http.get(base_url+'vocabs/services/vocabs/'+slug).then(function(response){
				return response.data;
			});
			return promise;
		},
		modify: function(slug, data) {
			var promise = $http.post(base_url+'vocabs/services/vocabs/'+slug, {data:data}).then(function(response){
				return response.data;
			});
			return promise;
		}
	}
})