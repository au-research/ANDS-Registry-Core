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
	$scope.status = 'loading';
	groupFactory.get().then(function(data){
		$scope.status = 'done';
		$scope.groups = data.groups;
		// $log.debug($scope.groups);
	});
});

app.controller('groupCtrl', function($scope, groupFactory, $log, $routeParams, profile_factory, $upload){

	$scope.base_url = base_url;
	$scope.tatoolbar = [['h1','h2','h3'],['bold','italics','underline'],['insertLink'],['ul', 'ol'],['insertImage']];

	groupFactory.get($routeParams.group).then(function(data){
        if(data.status=='ERROR') {
            $scope.error_upload_msg = data.message;
            $log.debug(data.message);
            location.href = 'cms#';
        }else{
            $scope.group = data;
        }
	});

	profile_factory.get_user().then(function(data){
		$scope.user = data;
		if($scope.user.function.indexOf('REGISTRY_SUPERUSER')!=-1) $scope.superuser = true;
		// $log.debug($scope.superuser);
	});

	$scope.save = function(state){

		$scope.saveMessage = 'loading...';
		// $log.debug(state);
		if(state) $scope.group.status = state;
		delete $scope.group.nodata;
		
		if(!$scope.group.data) $scope.group.data = {};
		if(!$scope.group.status) $scope.group.status = 'DRAFT';
        $scope.group.date_modified = new Date().toISOString();
        $scope.group.modified_who = $scope.user.name;
		// $log.debug($scope.group);
		groupFactory.save($scope.group.name, $scope.group).then(function(data){
			$scope.saveMessage = data.message;
            $scope.contributor_form.$setPristine();
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
				$scope.uploading = true;
				delete $scope.error_upload_msg;
				$upload.upload({
					url: base_url+'group/upload',
					file: file
				}).progress(function (evt) {
					var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
					// $log.debug('progress: ' + progressPercentage + '% ' + evt.config.file.name);
				}).success(function (data, status, headers, config) {
					$scope.uploading = false;
					if(data.status == 'OK' && data.url) {
						if(!$scope.group.data) $scope.group.data = {};
						$scope.group.data.logo = data.url;
					} else if(data.status=='ERROR') {
						$scope.error_upload_msg = data.message;
					}
				});
			}
		}
	}
	$scope.removeIdentifier = function(index) {
		// $log.debug('removing identifier at index', index);
		$scope.group.data.identifiers.splice(index, 1);
	}
    $scope.$on('$locationChangeStart', function(ev, nextUrl) {
        if($scope.contributor_form.$dirty)
        {
            var r = confirm('You have unsaved changes. Would you like to save these before continuing?')
            if (r == true) {
                $scope.save();
            }
        }
    });
});

app.directive('resolveUser', function($log, $http, profile_factory) {
	return {
		template: '{{name}}',
		scope: {
			roleid: '='
		},
		transclude: true,
		link: function(scope) {
			scope.name = scope.roleid;
			scope.$watch('roleid', function(newv){
				if (newv) {
					profile_factory.get_specific_user(scope.roleid).then(function(data){
						if(data.name) scope.name = data.name;
					});
				}
			});
		}
	}
});