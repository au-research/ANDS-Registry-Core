

app.config(['$routeProvider', '$locationProvider',
  function($routeProvider, $locationProvider) {
    $routeProvider.
      when('/dashboard', {
        templateUrl: base_url+'assets/profile/templates/dashboard.html',
        controller: 'dashboardCtrl'
      }).
      otherwise({
        redirectTo: '/dashboard'
    });
}]);

app.controller('dashboardCtrl', function($scope, $log, profile_factory){
	$scope.hello = 'Hello World';
	$scope.base_url = base_url;

	profile_factory.get_user().then(function(data){
		$scope.user = data;
		$log.debug($scope.user);
	});

	$scope.action = 'action';
	$scope.available_actions = profile_factory.get_user_available_actions();
	// $log.debug($scope.available_actions);
	
});