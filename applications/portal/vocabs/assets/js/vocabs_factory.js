/**
 * Vocabulary ANGULARJS Factory
 * A component that deals with the vocabulary service point directly
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
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
		},
		search: function(filters) {
			var promise = $http.post(base_url+'vocabs/filter', {filters:filters}).then(function(response){
				return response.data;
			});
			return promise;
		},
		toolkit: function(req) {
			var promise = $http.get(base_url+'vocabs/toolkit?request='+req).then(function(response){
				return response.data;
			});
			return promise;
		},
		getMetadata: function(id) {
			var promise = $http.get(base_url+'vocabs/toolkit?request=getMetadata&ppid='+id).then(function(response){
				return response.data;
			});
			return promise;
		},
		suggest: function(type) {
			var promise = $http.get(base_url+'vocabs/services/vocabs/all/related?type='+type).then(function(response){
				return response.data;
			});
			return promise;
		},
        user: function() {
            var promise = $http.get(base_url+'vocabs/services/vocabs/all/user').then(function(response){
                return response.data;
            });
            return promise;
        }
	}
})