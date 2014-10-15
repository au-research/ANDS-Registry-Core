var login_app = angular.module('login_app', ['ngRoute']).config(function ($routeProvider) {
    $routeProvider
    	.when('/', {
        	controller:'loginCtrl',
        	template:$('#main').html()
    	})
    	.when('/:method', {
    		controller: 'loginCtrl',
    		template:$('#main').html()
    	})
    	;
});

login_app.controller('loginCtrl', function ($scope, $location, loginService, $routeParams) {
	$scope.tab = $('#default_authenticator').val();
	if($routeParams.method) $scope.tab = $routeParams.method;
    $scope.data = loginService.data;
    $scope.authenticate = function(method) {
    	if(method=='built_in' || method=='ldap') {
    		loginService.authenticate(method, $scope.username, $scope.password).then($scope.handle);
    	}
    }

    $scope.handle = function(data){
    	$scope.message = false;
    	if(data.status=='ERROR') {
    		$scope.message = data;
    	} else {
    		console.log(data.message.redirect_to);
    		if(data.message.redirect_to) {
    			location.replace(data.message.redirect_to);
    		}
    	}
    }

});

login_app.factory('loginService', function ($http) {
    return { 
        data : {},
        handle: function(response){
        	this.data = response.data;
     		return this.data;
        },
        authenticate: function(method, username, password) {
        	return $http.post(base_url+'auth/authenticate/'+method, {username:username,password:password}).then(this.handle);
        }
    }; 
});