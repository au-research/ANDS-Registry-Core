var app = angular.module('app', ['ngRoute', 'ngSanitize', 'search_components'], function($interpolateProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');
});

app.config(function($routeProvider, $locationProvider) {
    $locationProvider.hashPrefix('!');
});

app.filter('trustAsHtml', ['$sce', function($sce){
	return function(text){
		var decoded = $('<div/>').html(text).text();
		return $sce.trustAsHtml(decoded);
	}
}]);

app.controller('mainController', function($scope, search_factory, $location, $sce) {
	$scope.filters = {};
	$scope.result = {};

	$scope.$on('$locationChangeSuccess', function() {
		$scope.filters = search_factory.filters_from_hash($location.path());
		$scope.search();
	});

	$scope.search = function() {
		search_factory.search($scope.filters).then(function(data){
			$scope.result = data;
			console.log($scope.result);
			$scope.result.facets = {};
			$.each($scope.result.facet_counts.facet_fields, function(j,k){
				$scope.result.facets[j] = [];
				for (var i = 0; i < $scope.result.facet_counts.facet_fields[j].length;i+=2){
					var fa = {
						name: $scope.result.facet_counts.facet_fields[j][i],
						value: $scope.result.facet_counts.facet_fields[j][i+1]
					}
					$scope.result.facets[j].push(fa);
				}
			});
		});
	}
});