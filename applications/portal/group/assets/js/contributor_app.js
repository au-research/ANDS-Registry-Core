var app = angular.module('app', ['ngRoute','portal-filters', 'groupFactory', 'textAngular'], function($interpolateProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');
});

app.config(['$routeProvider', '$locationProvider',
  function($routeProvider, $locationProvider) {
    $routeProvider.
      when('/groups', {
        templateUrl: base_url+'assets/group/templates/groups.html',
        controller: 'GroupsCtrl'
      }).
      when('/groups/:group', {
        templateUrl: base_url+'assets/group/templates/group.html',
        controller: 'groupCtrl'
      }).
      otherwise({
        redirectTo: '/groups'
      });
  }]);

app.controller('GroupsCtrl', function($scope, groupFactory, $log){
	groupFactory.get().then(function(data){
		$scope.groups = data.groups;
		$log.debug($scope.groups);
	});
});

app.controller('groupCtrl', function($scope, groupFactory, $log, $routeParams){

	$scope.tatoolbar = [['h1','h2','h3'],['bold','italics','underline'],['insertLink'],['ul', 'ol'],['insertImage']];

	groupFactory.get($routeParams.group).then(function(data){
		$scope.group = data;
		$log.debug('group', $scope.group.status);
	});

	$scope.save = function(state){
		$scope.saveMessage = 'loading...';
		$log.debug(state);
		if(state) $scope.group.status = state;
		$log.debug($scope.group);
		delete $scope.group.nodata;
		groupFactory.save($scope.group.name, $scope.group).then(function(data){
			$scope.saveMessage = data.message;
		});
	}

	//identifier operation deprecated in favor of html fields for identifiers
	$scope.addIdentifier = function() {
		if(!$scope.group.data) $scope.group.data = {};
		if($scope.group.data.identifiers) {
			$scope.group.data.identifiers.push(
				{type:'',value:''}
			)
		} else {
			$scope.group.data.identifiers = [
				{type:'',value:''}
			]
		}
	}
	$scope.removeIdentifier = function(index) {
		$log.debug('removing identifier at index', index);
		$scope.group.data.identifiers.splice(index, 1);
	}
});