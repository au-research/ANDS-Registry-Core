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

login_app.directive('autoFillSync', function($timeout) {
   return {
      require: 'ngModel',
      link: function(scope, elem, attrs, ngModel) {
          var origVal = elem.val();
          $timeout(function () {
              var newVal = elem.val();
              if(ngModel.$pristine && origVal !== newVal) {
                  ngModel.$setViewValue(newVal);
              }
          }, 500);
      }
   }
});

function loginCtrl($scope, $routeParams, loginService, $location){
	$scope.tab = $routeParams.method ? $routeParams.method : $('#default_authenticator').val();
	$scope.redirect = $location.search().redirect ? $location.search().redirect : '';
	$scope.error = $location.search().error ? $location.search().error : '';
	$scope.message = $location.search().message ? $location.search().message : false;
	
	$scope.authenticate = function(method){
		var data = {
			username:$scope.username,
			password:$scope.password
		}
		$('form button').button('loading');
		loginService.authenticate(method, data).then(function(data){
			if(data.status=='ERROR'){
				$scope.message = data.message;
				$('form button').button('reset');
			} else if(data.status=='SUCCESS'){
				if(!$scope.redirect){
					document.location.href = data.message.redirect_to;	
				} else {
					document.location.href = $scope.redirect;
				}
				
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