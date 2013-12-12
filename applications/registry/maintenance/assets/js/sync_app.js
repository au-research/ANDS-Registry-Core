angular.module('sync_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize', 'ui.bootstrap', 'ui.utils']).
	config(function($routeProvider){
		$routeProvider
			.when('/',{
				controller:indexCtrl,
				template:$('#index_template').html()
			})
			.when('/view/:data_source_id', {
				controller: interrogateDS,
				template:$('#view_ds_template').html()
			})
	}).
	service('sync_service', function($http){
		return{
			list_ds: function(){
				return $http.post(base_url+'/maintenance/getDataSourceList').then(function(response){return response.data;});
			},
			detailed_stat: function(){
				return $http.post(base_url+'/maintenance/getDataSourceList/true').then(function(response){return response.data;});
			},
			global_stat: function(){
				return $http.get(base_url+'/maintenance/getStat/').then(function(response){return response.data;});
			},
			get_ds: function(ds_id){
				return $http.get(base_url+'/maintenance/getDataSourceStat/'+ds_id).then(function(response){return response.data});
			},
			analyze: function(task, ds_id){
				return $http.post(base_url+'/maintenance/smartAnalyze/'+task+'/'+ds_id).then(function(response){return response.data;});
			},
			run_task: function(task, ds_id, chunk_pos){
				return $http.get(base_url+'/maintenance/smartSyncDS/'+task+'/'+ds_id+'/'+chunk_pos).then(function(response){return response.data;});
			},
			sync_ro: function(subject){
				return $http.post(base_url+'/maintenance/sync/', {idkey:subject}).then(function(response){return response.data;});
			}
		}
	})
;

function indexCtrl($scope, sync_service){
	$scope.datasources = [];
	sync_service.list_ds().then(function(data){
		$scope.datasources = data;
	});

	$scope.ct = {
		status:'idle',
	};//current task

	$scope.currentChunk = 0;
	$scope.percent = 0;
	$scope.predicate = 'total_published';
	$scope.reverse = true;

	$scope.addTask = function(task, ds_id){
		if($scope.currentChunk == 0){
			$scope.ct = {currentChunk:0,numChunk:0}
			$scope.ct = {
				task:task,
				ds_id:ds_id
			};
			$scope.doTask();
		}else{
			alert("There's already a task running!");
		}
	};

	$scope.doTask = function(){
		sync_service.analyze($scope.ct.task, $scope.ct.ds_id).then(function(data){
			if(data){
				$scope.errors = false;
				$scope.ct.total = data.total;
				$scope.ct.numChunk = data.numChunk;
				$scope.ct.status = 'running';
				$scope.percent = 1;
				$scope.ct.totalTime = 0;
			}
		});
	}

	$scope.$watch('ct.status', function(){
		if($scope.ct.status=='running'){
			$scope.currentChunk = 1;
		}else if($scope.ct.status=='idle'){
			$scope.currentChunk = 0;
		}else if($scope.ct.status=='done'){
			$scope.percent = 100;
			$scope.currentChunk = 0;
		}
	});

	$scope.$watch('currentChunk', function(){
		if($scope.currentChunk > 0){
			if($scope.currentChunk <= $scope.ct.numChunk){
				sync_service.run_task($scope.ct.task, $scope.ct.ds_id, $scope.currentChunk).then(function(data){
					if(data.errors.length > 0) $scope.errors = data.errors;
					//update totalTime
					var total = $scope.ct.totalTime + parseFloat(data.benchMark.totalTime)
					$scope.ct.totalTime = total;
					$scope.currentChunk++;
					$scope.percent = (($scope.currentChunk-1) * 100 / $scope.ct.numChunk);
					if($scope.ct.task=='clear') {
						$scope.ct.status='done';
					}
				});
			}else{
				$scope.ct.status = 'done';
			}
		}
	});

	$scope.syncRO = function(){
		$scope.syncROStatus = 'Loading...';
		sync_service.sync_ro($scope.subject).then(function(data){
			$scope.syncROStatus = data.message;
		});
	}

	$scope.get_global_stat = function(){
		$scope.loading_global_stat = true;
		sync_service.global_stat().then(function(data){
			$scope.global_stat = data;
			$scope.loading_global_stat = false;
		});
	}

	$scope.get_detailed_stat = function(){
		$scope.loading_detailed_stat = true;
		sync_service.detailed_stat().then(function(data){
			$scope.datasources = data;
			$scope.loading_detailed_stat = false;
			$scope.detailed_stat = true;
		});
	}
}

function interrogateDS($scope, $routeParams, sync_service){
	sync_service.get_ds($routeParams.data_source_id).then(function(data){
		$scope.ds = data;
	});
}