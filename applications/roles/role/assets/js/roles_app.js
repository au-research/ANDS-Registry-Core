angular.module('roles_app', ['portal-filters']).
	config(function($routeProvider){
		$routeProvider
			.when('/',{
				controller:indexCtrl,
				template:$('#index_template').html()
			})
			.when('/view/:id', {
				controller: indexCtrl,
				template:$('#index_template').html()
			})
			.when('/view/:id/:filter',{
				controller: indexCtrl,
				template:$('#index_template').html()
			})
			.when('/:add/', {
				controller: indexCtrl,
				template:$('#index_template').html()
			})
	}).
	factory('roles', function($http){
		return{
			all: function(){
				return $http.get(base_url+'role/all_roles').then(function(response){return response.data;});
			},
			get: function(role_id){
				return $http.get(base_url+'role/get/?role_id='+role_id).then(function(response){return response.data});
			},
			add_relation: function(child, parent){
				return $http.post(base_url+'role/add_relation/',{child:child, parent:parent}).then(function(response){return response.data});
			},
			remove_relation: function(child, parent){
				return $http.post(base_url+'role/remove_relation/',{child:child, parent:parent}).then(function(response){return response.data});
			},
			add: function(data){
				return $http.post(base_url+'role/add/',{data:data}).then(function(response){return response.data});
			},
			update: function(id, data){
				return $http.post(base_url+'role/update/',{role_id:id, data:data}).then(function(response){return response.data});
			},
			resetpw: function(id) {
				return $http.get(base_url+'role/resetPassphrase/?role_id='+id).then(function(response){return response.data});
			},
			delete_role: function(id){
				return $http.post(base_url+'role/delete/',{role_id:id}).then(function(response){return response.data});
			},
			get_merge_missing: function(id, sid) {
				return $http.get(base_url+'role/role_missing?first_role='+id+'&second_role='+sid).then(function(response){return response.data});
			},
			get_merge_missing_commit: function(id, sid) {
				return $http.get(base_url+'role/role_missing/true?first_role='+id+'&second_role='+sid).then(function(response){return response.data});
			}
		}
	})
;

function indexCtrl($scope, roles, $timeout, $routeParams, $location) {
	$scope.roles = {};
	$scope.limit = 50;
	$scope.newrole = {
		'role_id':'','name':'','role_type_id':'ROLE_USER','enabled':'1','authentication_service_id':'AUTHENTICATION_BUILT_IN'
	}
	$scope.tab = 'add_rel';
	$scope.tab1 = 'search';
	$scope.filter = '';
	$scope.filterType = '';

	if($routeParams.add) $scope.tab1 = 'new';

	roles.all().then(function(data){
		$scope.roles = data;
	});

	$scope.select = function(role_id){
		// $scope.role = {};
		$scope.loading = true;
		$scope.tab1 = 'view';
		$scope.tab = 'add_rel';
		roles.get(role_id).then(function(data){
			$scope.loading = false;
			$scope.role = data;
			$scope.role_type_id = $.trim($scope.role.role.role_type_id);
		});
	}

	$scope.add_relation = function(child, parent){
		roles.add_relation(child, parent).then(function(data){
			$scope.select($scope.role.role.role_id);
		});
	}

	$scope.remove_relation = function(child, parent){
		roles.remove_relation(child, parent).then(function(data){
			$scope.select($scope.role.role.role_id);
		});
	}

	$scope.add = function() {
		$('#add_role').button('loading');
		roles.add($scope.newrole).then(function(data){
			if(data.status=='ERROR') {
				$('#add_role').button('reset');
				alert(data.message);
			} else {
				$location.path('/view/'+$scope.newrole.role_id);
			}
		});
	}

	$scope.update = function(){
		roles.update($scope.role.role.role_id, $scope.role.role).then(function(data){
			$scope.select($scope.role.role.role_id);
		});
	}

	$scope.resetPassword = function(){
		roles.resetpw($scope.role.role.role_id).then(function(data){
			if(data.status=='OK'){
				alert('Password has been reset successfully for '+$scope.role.role.role_id);
			}
		});
	}

	$scope.delete = function() {
		if(confirm('Are you sure you want to delete this role:'+$scope.role.role.name+'? This action is irreversible')){
			roles.delete_role($scope.role.role.role_id).then(function(data){
				$location.path('/');
			});
		}
	}

	$scope.search = function(item) {
		var name = item.name.toLowerCase();
		var email = item.email ? item.email.toLowerCase() : '';
		if(name.indexOf($scope.filter.toLowerCase())!=-1 || email.indexOf($scope.filter.toLowerCase())!=-1) {
			return true;
		}
		return false;
	}

	$scope.searchtype = function(item) {
		if(item.role_type_id==$scope.filterType || $scope.filterType==''){
			return true;
		}
		return false;
	}

	$scope.$watch('merge_target', function(newv, oldv){
		if(newv!='' && $scope.role && $scope.role.role){
			roles.get_merge_missing($scope.role.role.role_id, newv).then(function(data){
				$scope.role_merge_missing = data.missing;
			});
		}
	})

	$scope.commit_merge_role = function(target, role){
		roles.get_merge_missing_commit(role, target).then(function(data){
			$scope.select($scope.role.role.role_id);
		});
	}

	if($routeParams.id) $scope.select($routeParams.id);
	if($routeParams.filter) $scope.filter = $routeParams.filter;
}