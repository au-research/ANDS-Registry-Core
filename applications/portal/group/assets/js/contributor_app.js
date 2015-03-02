var app = angular.module('app', ['ngRoute','portal-filters', 'groupFactory', 'textAngular','angularFileUpload', 'profile_components'], function($interpolateProvider){
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
		// $log.debug($scope.groups);
	});
});

app.controller('groupCtrl', function($scope, groupFactory, $log, $routeParams, profile_factory, $upload){

	$scope.base_url = base_url;
	$scope.tatoolbar = [['h1','h2','h3'],['bold','italics','underline'],['insertLink'],['ul', 'ol'],['insertImage']];

	groupFactory.get($routeParams.group).then(function(data){
		$scope.group = data;
	});

	profile_factory.get_user().then(function(data){
		$scope.user = data;
		// $log.debug($scope.user);
		if($scope.user.function.indexOf('REGISTRY_SUPERUSER')!=-1) $scope.superuser = true;
		// $log.debug($scope.superuser);
	});

	$scope.save = function(state){
		$scope.saveMessage = 'loading...';
		// $log.debug(state);
		if(state) $scope.group.status = state;
		// $log.debug($scope.group);
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

	$scope.upload = function(files) {
		if (files && files.length) {
			for (var i = 0; i < files.length; i++) {
				var file = files[i];
				$upload.upload({
					url: base_url+'group/upload',
					file: file
				}).progress(function (evt) {
					var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
					// $log.debug('progress: ' + progressPercentage + '% ' + evt.config.file.name);
				}).success(function (data, status, headers, config) {
					// $log.debug(data);
					if(data.status=='OK' && data.url) {
						$scope.group.data.logo = data.url;
					}
				});
			}
		}
	}
	$scope.removeIdentifier = function(index) {
		// $log.debug('removing identifier at index', index);
		$scope.group.data.identifiers.splice(index, 1);
	}
});