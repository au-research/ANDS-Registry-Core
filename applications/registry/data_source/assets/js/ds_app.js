angular.module('ds_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize', 'ui.bootstrap', 'ui.utils', 'portal-filters']).
	factory('ds_factory', function($http){
		return {
			get: function(id) {
				if(!id) id='';
				return $http.get(base_url+'data_source/get/'+id).then(function(response){return response.data});
			},
			get_log: function(id, offset) {
				return $http.get(base_url+'data_source/get_log/'+id+'/'+offset).then(function(response){return response.data});
			},
			get_harvester_status: function(id) {
				return $http.get(base_url+'data_source/harvester_status/'+id).then(function(response){return response.data});
			}
		}
	}).
	config(function($routeProvider, $locationProvider){
		$routeProvider
			.when('/',{
				controller:ListCtrl,
				template:$('#list_template').html()
			})
			.when('/view/:id', {
				controller: ViewCtrl,
				template:$('#view_template').html()
			});
		$locationProvider
		  .html5Mode(false)
		  .hashPrefix('!');
	})
;

function ListCtrl($scope, ds_factory) {
	$scope.stage = 'loading';
	$scope.datasources = [];
	ds_factory.get().then(function(data){
		$scope.stage = 'complete';
		$scope.datasources = data.items;
	});
}

function ViewCtrl($scope, $routeParams, ds_factory) {
	$scope.stage = 'loading';
	$scope.ds = {};
	$scope.offset = 0;
	$scope.harvester = {};

	ds_factory.get($routeParams.id).then(function(data){
		$scope.ds = data.items[0];
		$scope.refresh_harvest_status();
	});

	$scope.more_logs = function() {
		$scope.offset = $scope.offset + 10;
		ds_factory.get_log($scope.ds.id, $scope.offset).then(function(data){
			$.each(data.items, function(){
				$scope.ds.logs.push(this);
			});
		});
	}

	$scope.refresh_harvest_status = function() {
		ds_factory.get_harvester_status($scope.ds.id).then(function(data){
			$scope.harvester = data.items[0];
			console.log($scope.harvester);
		});
	}
}