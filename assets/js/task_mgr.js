var task_mgr = angular.module('task_mgr_app', ['ngRoute']);

task_mgr.config(['$routeProvider', function($routeProvider){
	$routeProvider
		.when('/', {
			template:$('#main').html(),
			controller:'mainCtrl'
		})
		;
}]);

function mainCtrl($scope, $routeParams, tasksFactory, $location){
	
}

task_mgr.factory('tasksFactory', function($http){
	return {
		authenticate: function(method, data) {
			return $http.post(base_url+'auth/authenticate/'+method, data).then(function(response){return response.data});
		}
	}
});