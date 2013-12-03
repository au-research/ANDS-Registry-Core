angular.module('sync_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize', 'ui.bootstrap', 'ui.utils', 'datatablesDirectives']).
	config(function($routeProvider){
		$routeProvider
			.when('/',{
				controller:indexCtrl,
				template:$('#index_template').html()
			})
	}).
	service('sync_service', function($http){
		return{
			list_ds: function(){
				return $http.post(base_url+'/maintenance/getDataSourceList').then(function(response){return response.data;});
			},
			analyze: function(ds_id){
				return $http.post(base_url+'/maintenance/smartAnalyze/'+ds_id).then(function(response){return response.data;});
			},
			sync_ds: function(ds_id, chunk_pos){
				return $http.get(base_url+'/maintenance/smartSyncDS/'+ds_id+'/'+chunk_pos).then(function(response){return response.data;});
			},
			test: function(){
				return $http.get('http://filltext.com/?rows=10&fname={firstName}&lname={lastName}&delay=2').then(function(response){return response.data;});
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
			if(task=='sync'){
				$scope.ct = {
					task:task,
					ds_id:ds_id
				};
				$scope.doTask();
			}
		}else{
			alert("There's already a task running!");
		}
	};

	$scope.doTask = function(){
		if($scope.ct.task=='sync'){
			sync_service.analyze($scope.ct.ds_id).then(function(data){
				if(data){
					$scope.ct.total = data.total;
					$scope.ct.numChunk = data.numChunk;
					$scope.ct.status = 'running';
					$scope.percent = 1;
					$scope.ct.totalTime = 0;
				}
			});
		}
	}

	$scope.$watch('ct.status', function(){
		if($scope.ct.status=='running'){
			$scope.currentChunk = 1;
		}else if($scope.ct.status=='idle'){
			$scope.currentChunk = 0;
		}
	});

	$scope.$watch('currentChunk', function(){
		if($scope.currentChunk > 0){
			if($scope.currentChunk <= $scope.ct.numChunk){
				sync_service.sync_ds($scope.ct.ds_id, $scope.currentChunk).then(function(data){
					console.log(data);
					$scope.ct.totalTime += parseFloat(data.benchMark.totalTime);
					var num = parseFloat($scope.ct.totalTime);
					$scope.ct.totalTime = num.toFixed(2);
					$scope.currentChunk++;
					$scope.percent = (($scope.currentChunk-1) * 100 / $scope.ct.numChunk);
				});
			}else{
				$scope.ct.status = 'done';
				$scope.percent = 100;
				$scope.currentChunk = 0;
			}
		}
	});

}