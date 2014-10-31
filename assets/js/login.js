var login_app = angular.module('login_app', ['ngRoute']);

login_app.config(['$routeProvider', function($routeProvider){
	$routeProvider
		.when('/', {
			template:$('#main').html(),
			controller:'loginCtrl'
		})
		.when('/:method', {
			template:$('#main').html(),
			controller:'loginCtrl'
		})
		;
}]);

function loginCtrl($scope, $routeParams, loginService){
	$scope.tab = $routeParams.method ? $routeParams.method : $('#default_authenticator').val();
	
	$scope.authenticate = function(method){
		var data = {
			username:$scope.username,
			password:$scope.password
		}
		loginService.authenticate(method, data).then(function(data){
			console.log(data);
			if(data.status=='ERROR'){
				$scope.message = data.message;
			} else if(data.status=='SUCCESS'){
				document.location.href = data.message.redirect_to;
			}
		});
	}
}

login_app.factory('loginService', function($http){
	return {
		authenticate: function(method, data) {
			return $http.post(base_url+'auth/authenticate/'+method, data).then(function(response){return response.data});
		}
	}
});