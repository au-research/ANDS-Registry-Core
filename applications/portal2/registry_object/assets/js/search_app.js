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
	$scope.q = '';
	$scope.search_type = 'all';
	$scope.filters = {};
	$scope.result = {};
	$scope.fields = ['title', 'description', 'subject'];

	$scope.advanced_search = {};
	$scope.advanced_search.fields = search_factory.advanced_fields();
	$scope.selectAdvancedField = function(field) {
		$.each($scope.advanced_search.fields, function(){
			this.active = false;
		});
		field.active = true;
	}

	$scope.$on('$locationChangeSuccess', function() {
		$scope.filters = search_factory.filters_from_hash($location.path());
		$scope.populateFilters();
		$scope.search();
	});

	$scope.search = function() {
		$scope.filters.q = $scope.q;
		if ($scope.search_type!='all') {
			$scope.cleanfilters();
			$scope.filters[$scope.search_type] = $scope.q;
		}
		$scope.populateFilters();
		search_factory.search($scope.filters).then(function(data){
			$scope.result = data;
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
			$.each($scope.result.highlighting, function(i,k){
				$.each($scope.result.response.docs, function(){
					if(this.id==i) {
						this.hl = k;
					}
				});
			});
		});
	}

	$scope.advanced = function() {
		$('#advanced_search').modal();
	}

	$scope.addKeyWord = function(key) {
		$scope.q += ' '+key;
		$scope.search();
	}

	$scope.toggleFilter = function(type, value) {
		if ($scope.filters[type]) {
			$scope.clearFilter(type);
		} else {
			$scope.addFilter(type, value);
		}
		$scope.search();
	}

	$scope.addFilter = function(type, value) {
		$scope.filters[type] = value;
	}

	$scope.clearFilter = function(type) {
		delete $scope.filters[type];
	}

	$scope.cleanfilters = function() {
		$.each($scope.fields, function(){
			delete $scope.filters[this];
		});
	}

	$scope.populateFilters = function() {
		$scope.q = ($scope.filters.q ? $scope.filters.q : '');
		$.each($scope.fields, function(){
			if($scope.filters[this]) {
				$scope.search_type = this.toString();
				$scope.q = $scope.filters[this];
			}
		});
	}
});