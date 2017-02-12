angular.module('config_app', ['portal-filters']).
	config(function($routeProvider){
		$routeProvider
			.when('/',{
				controller:indexCtrl,
				template:$('#index_template').html()
			})
	}).
	factory('config', function($http){
		return{
			all: function(){
				return $http.get(base_url+'/maintenance/config').then(function(response){return response.data;});
			},
			save: function(data){
				return $http.post(base_url+'/maintenance/config/save', {data:data}).then(function(response){return response.data;});
			}
		}
	})
;

function indexCtrl($scope, config, $timeout) {
	$scope.config = {};
	$scope.refresh = function() {
		config.all().then(function(data){
			$scope.config = data;
		});
	}

	$scope.save = function() {
		var data = {};
		$scope.response = false;
		$.each($scope.config, function(i, k) {
			if(this.type=='string'){
				data[i] = this;
			}
		});
		config.save(data).then(function(response){
			$scope.response = response;
			if($scope.response.status=='OK') {
				$scope.response.status='success';
			} else if ($scope.response=='ERROR') {
				$scope.response.status='error';
			}
		});
	}

	$scope.refresh();
}