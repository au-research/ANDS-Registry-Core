angular.module('groupFactory',[])
	.factory('groupFactory', function($http, $log){

		return {
			get: function(name){
				var para = '';
				if (name) para = '?group='+name;
				var promise = $http.get(base_url+'group/get'+para).then(function(response){
					if(response.data.data) response.data.data = angular.fromJson(response.data.data);
					// $log.debug(response.data.data);
					return response.data;
				});
				return promise;
			},
			save: function(name, data) {
				var promise = $http.post(base_url+'group/save/?group='+name, {'data':data}).then(function(response){
					return response.data;
				});
				return promise;
			}
		}

	});