angular.module('status_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize', 'ui.bootstrap', 'ui.utils', 'portal-filters']).
	config(function($routeProvider){
		$routeProvider
			.when('/',{
				controller:indexCtrl,
				template:$('#index_template').html()
			})
	}).
	factory('registry', function($http){
		return{
			status: function(){
				return $http.get(base_url+'/maintenance/status').then(function(response){return response.data;});
			},
			config: function() {
				return $http.get(base_url+'/maintenance/config').then(function(response){return response.data;});
			},
			config_save: function(data) {
				return $http.post(base_url+'/maintenance/config_save', {data:data}).then(function(response){return response.data;});
			}
		}
	})
;

function indexCtrl($scope, registry, $timeout) {
	$scope.status = {};
	$scope.config = {};

	registry.status().then(function(data) {
		$scope.status = data;
	});
	registry.config().then(function(data) {
		$scope.config = data;
	});

	$scope.save_config = function(){
		$scope.message = '';
		$scope.error = '';
		registry.config_save($scope.config).then(function(data){
			if(data.status=='OK') {
				$scope.message = data.message;
			} else {
				$scope.error = data.message;
			}
		});
	}
}