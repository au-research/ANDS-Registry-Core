angular.module('status_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize', 'ui.bootstrap', 'ui.utils', 'portal-filters']).
	config(function($routeProvider){
		$routeProvider
			.when('/',{
				controller:indexCtrl,
				template:$('#index_template').html()
			})
	})
	.factory('status', function($http) {
		return {
			get: function() {
				return $http.get(api_url + 'status')
					.then(function(response) {
						return response.data.data;
					})
			}
		}
	})
;

function indexCtrl($scope, status, $timeout) {
	$scope.status = false;
	$scope.config = {};
	$scope.modules = ['harvester', 'taskmanager', 'elasticsearch', 'solr', 'neo4j']

	status.get().then(function(data){
		$scope.status = data;
	})
}