angular.module('harvester_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize', 'ui.bootstrap', 'ui.utils']).
	config(function($routeProvider){
		$routeProvider
			.when('/',{
				controller:indexCtrl,
				template:$('#index_template').html()
			})
	}).
	factory('harvester', function($http){
		return{
			all: function(){
				return $http.get(base_url+'/import/list_harvests').then(function(response){return response.data;});
			},
			start: function(id) {
				return $http.get(base_url+'/data_source/trigger_harvest/'+id).then(function(response){return response.data;});
			},
			stop: function(id) {
				return $http.get(base_url+'/data_source/stop_harvest/'+id).then(function(response){return response.data;});
			}
		}
	})
;

function indexCtrl($scope, harvester, $timeout) {
	$scope.requests = {};
	
	$scope.refresh = function(click) {
		harvester.all().then(function(data){
			$scope.requests = data.harvests;
			$.each($scope.requests, function() {
				if(data.status=='OK'){
					switch(this.status) {
						case 'IDLE': this.can_start = true; this.can_stop = true; break;
						case 'HARVESTING': this.can_start = false; this.can_stop = true; break;
						case 'IMPORTING': this.can_start = false; this.can_stop = true; break;
						case 'STOPPED': this.can_start = true; this.can_stop = false; break;
						case 'COMPLETED': this.can_start = true; this.can_stop = false; break;
						case 'SCHEDULED': this.can_start = true; this.can_stop = true; break;
						case 'WAITING': this.can_start = false; this.can_stop = true; break;
					} try {
						if(this.message){
							this.message = this.message.replace(/(\r\n|\n|\r)/gm,"");
							this.message = JSON.parse(this.message);
						}
					} catch(err) {
						console.error(err + this.data_source_id);
					}
					// if(!click) $timeout($scope.refresh, 10000);
				}
			});
		});
	}
	$scope.refresh(false);

	$scope.start_harvest = function(r) {
		if (r.can_start) {
			harvester.start(r.data_source_id).then(function(data){
				$scope.refresh();
			});
		}
	}

	$scope.stop_harvest = function(r) {
		if (r.can_stop) {
			harvester.stop(r.data_source_id).then(function(data){
				$scope.refresh();
			});
		}
	}
}