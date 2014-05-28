angular.module('ds_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize', 'ui.bootstrap', 'ui.utils', 'portal-filters']).
	factory('ds_factory', function($http){
		return {
			get: function(id) {
				if(!id) id='';
				return $http.get(base_url+'data_source/get/'+id).then(function(response){return response.data});
			},
			get_log: function(id, offset) {
				return $http.get(base_url+'data_source/get_log/'+id+'/'+offset).then(function(response){return response.data});
			},
			get_harvester_status: function(id) {
				return $http.get(base_url+'data_source/harvester_status/'+id).then(function(response){return response.data});
			},
			contributor: function(id) {
				return $http.get(base_url+'data_source/get_contributor/'+id).then(function(response){return response.data});
			},
			save: function(data) {
				return $http.post(base_url+'data_source/save/', {data:data}).then(function(response){return response.data});
			}
		}
	}).
	directive('checkbox', function() {
		return {
			template: 	'<span class="label label-info">'+
							'<i class="icon icon-white" ng-class="{\'t\':\'icon-ok\', \'f\':\'icon-remove\', \'1\':\'icon-ok\', \'0\':\'icon-remove\'}[checkbox]"></i>'+
					 	'</span>',
			scope: {
				checkbox: '=checkbox'
			}
		}
	}).
	config(function($routeProvider, $locationProvider){
		$routeProvider
			.when('/',{
				controller:ListCtrl,
				template:$('#list_template').html()
			})
			.when('/view/:id', {
				controller: ViewCtrl,
				template:$('#view_template').html()
			})
			.when('/settings/:id',{
				controller: SettingsCtrl,
				template:$('#settings_template').html()
			})
			.when('/edit/:id/', {
				controller: EditCtrl,
				template:$('#edit_template').html()
			})
			.when('/edit/:id/:tab', {
				controller: EditCtrl,
				template:$('#edit_template').html()
			})
			;
		$locationProvider
		  .html5Mode(false)
		  .hashPrefix('!');
	})
;

function ListCtrl($scope, ds_factory) {
	$scope.stage = 'loading';
	$scope.datasources = [];
	ds_factory.get().then(function(data){
		$scope.stage = 'complete';
		$scope.datasources = data.items;
	});
}

function SettingsCtrl($scope, $routeParams, ds_factory) {
	$scope.ds = {};
	ds_factory.get($routeParams.id).then(function(data){
		$scope.ds = data.items[0];
		$scope.load_contributor();
		document.title = $scope.ds.title + ' - Settings';
	});

	$scope.load_contributor = function() {
		ds_factory.contributor($scope.ds.id).then(function(data){
			$scope.ds.contributor = data;
		});
	}
}

function EditCtrl($scope, $routeParams, ds_factory) {
	$scope.ds = {};

	if($routeParams.tab) {
		$scope.tab = $routeParams.tab;
	} else $scope.tab = 'admin';

	ds_factory.get($routeParams.id).then(function(data){
		$scope.ds = data.items[0];
		$scope.load_contributor();
		$scope.process_values();
		$scope.bind_plugins();
		document.title = $scope.ds.title + ' - Edit Settings';
	});

	$scope.load_contributor = function() {
		ds_factory.contributor($scope.ds.id).then(function(data){
			$scope.ds.contributor = data;
		});
	}

	$scope.process_values = function() {
		$.each($scope.ds, function(i){
			if((this=='t' || this=='1') && i!='id') {
				$scope.ds[i] = true;
			}
		});
	}

	$scope.save = function() {
		$scope.msg = {
			'type':'info',
			'msg':'Saving...'
		};
		ds_factory.save($scope.ds).then(function(data){
			if (data.status=='OK') {
				$scope.msg = {
					'type':'success',
					'msg': 'Datasource saved successfully'
				}
			} else {
				$scope.msg = {
					'type':'error',
					'msg':data.message
				}
			}
			console.log(data);
		});
	}

	$scope.bind_plugins = function() {
		$('.datepicker').ands_datetimepicker();

		$(".ro_search").each(function(){
			if ($(this).attr('name') && $(this).attr('name').match(/contributor/)) {
				$(this).ro_search_widget({ endpoint: apps_url + "registry_object_search/", 'class': "party", ds: $scope.ds.id });
			} else {
				$(this).ro_search_widget({ endpoint: apps_url + "registry_object_search/", datasource: $scope.ds.id, lock_presets: true });
			}
		});

		function _getVocab(vocab){
			vocab = vocab.replace("collection", "Collection");
			vocab = vocab.replace("party", "Party");
			vocab = vocab.replace("service", "Service");
			vocab = vocab.replace("activity", "Activity");
			return vocab;
		}

		$(".rifcs-type").each(function(){
			var elem = $(this);
			var widget = elem.vocab_widget({mode:'advanced'});
			var vocab = _getVocab(elem.attr('vocab'));
			var dataArray = Array();
			if(vocab == 'RIFCSClass') {
				dataArray.push({value:'Party', subtext:'Party'});
				dataArray.push({value:'Activity', subtext:'Activity'});			
				dataArray.push({value:'Collection', subtext:'Collection'});
				dataArray.push({value:'Service', subtext:'Service'});
				elem.typeahead({source:dataArray,items:16});
		} else {
				elem.on('narrow.vocab.ands', function(event, data) {	
					$.each(data.items, function(idx, e) {
						dataArray.push({value:e.label, subtext:e.definition});
					});
					elem.typeahead({source:dataArray,items:16});
				});
				elem.on('error.vocab.ands', function(event, xhr) {
					//error handling
				});
				widget.vocab_widget('repository', 'rifcs');
				widget.vocab_widget('narrow', "http://purl.org/au-research/vocabulary/RIFCS/1.4/" + vocab);	
			}	 
		});
	}

}

function ViewCtrl($scope, $routeParams, ds_factory) {
	$scope.stage = 'loading';
	$scope.ds = {};
	$scope.offset = 0;
	$scope.harvester = {};

	ds_factory.get($routeParams.id).then(function(data){
		$scope.ds = data.items[0];
		$scope.refresh_harvest_status();
		document.title = $scope.ds.title + ' - Dashboard';
	});

	$scope.more_logs = function() {
		$scope.offset = $scope.offset + 10;
		ds_factory.get_log($scope.ds.id, $scope.offset).then(function(data){
			$.each(data.items, function(){
				$scope.ds.logs.push(this);
			});
		});
	}

	$scope.refresh_harvest_status = function() {
		ds_factory.get_harvester_status($scope.ds.id).then(function(data){
			$scope.harvester = data.items[0];
		});
	}
}