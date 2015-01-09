var app = angular.module('search', ['search_components'], function($interpolateProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');
});

app.controller('searchCtrl', function($scope){

	$scope.q = '';
	$scope.search_type = 'all';

	$scope.hashChange = function(){
		var search_url = base_url+'search/#!/';
		if ($scope.search_type!='all') {
			search_url+=$scope.search_type+'='+$scope.q;
		} else {
			search_url+='q='+$scope.q;
		}

		window.location = search_url;
	}

	$scope.advanced = function(){
		$('#advanced_search').modal();
	}
});