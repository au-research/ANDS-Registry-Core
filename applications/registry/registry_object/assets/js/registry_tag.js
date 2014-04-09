angular.module('registry_tag', ['slugifier', 'ui.sortable', 'ngSanitize', 'ui.bootstrap', 'ui.utils']).

controller('tag', function($scope, $http){
	$scope.suggest = function(what, q){
		return $http.get(real_base_url+'registry/services/registry/suggest/'+what+'/'+q).then(function(response){
			return response.data;
		});
	}
});
