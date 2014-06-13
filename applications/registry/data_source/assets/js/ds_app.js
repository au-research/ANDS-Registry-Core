angular.module('ds_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize', 'ui.bootstrap', 'ui.utils', 'portal-filters']).
	factory('ds_factory', function($http){
		return {
			get: function(id) {
				if(!id) id='';
				return $http.get(base_url+'data_source/get/'+id).then(function(response){return response.data});
			},
			get_log: function(id, offset, limit, logid) {
				return $http.get(base_url+'data_source/get_log/'+id+'/'+offset+'/'+limit+'/'+logid).then(function(response){return response.data});
			},
			get_harvester_status: function(id) {
				return $http.get(base_url+'data_source/harvester_status/'+id).then(function(response){return response.data});
			},
			contributor: function(id) {
				return $http.get(base_url+'data_source/get_contributor/'+id).then(function(response){return response.data});
			},
			save: function(data) {
				return $http.post(base_url+'data_source/save/', {data:data}).then(function(response){return response.data});
			},
			add: function(data) {
				return $http.post(base_url+'data_source/add/', {data:data}).then(function(response){return response.data});
			},
			remove: function(id) {
				return $http.post(base_url+'data_source/delete/', {id:id}).then(function(response){return response.data});
			},
			import: function(id, type, data) {
				if(type=='path'){
					console.log(data);
					return $http.get(base_url+'import/put/'+id+'?batch='+data.path).then(function(response){return response.data});
				}else return $http.post(base_url+'import/put/'+id+'/'+type, {data:data}).then(function(response){return response.data});
			},
			start_harvest: function(id) {
				return $http.get(base_url+'data_source/trigger_harvest/'+id).then(function(response){return response.data});
			},
			stop_harvest: function(id) {
				return $http.get(base_url+'/data_source/stop_harvest/'+id).then(function(response){return response.data;});
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

function ListCtrl($scope, ds_factory, $location) {
	$scope.stage = 'loading';
	$scope.datasources = [];
	ds_factory.get().then(function(data){
		$scope.stage = 'complete';
		$scope.datasources = data.items;
	});

	$scope.add_new = function() {
		var new_ds = {
			key: $('#new_ds input[name=data_source_key]').val(),
			title: $('#new_ds input[name=title]').val(),
			record_owner: $('#new_ds select[name=record_owner]').val()
		}
		ds_factory.add(new_ds).then(function(data) {
			if (data.status=='OK' && data.data_source_id) {
				$('.modal-backdrop').hide();
				$location.path('/view/'+data.data_source_id);
			} else {
				$scope.error = data.message;
			}
		});
	}
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
		bind_plugins($scope);
		document.title = $scope.ds.title + ' - Edit Settings';
	});

	$scope.load_contributor = function() {
		ds_factory.contributor($scope.ds.id).then(function(data){
			$scope.ds.contributor = data;
		});
	}

	$scope.process_values = function() {
		var flags = ['manual_publish', 'allow_reverse_internal_links', 'allow_reverse_external_links', 'create_primary_relationship', 'qa_flag', 'export_dci'];
		$.each($scope.ds, function(i){
			if($.inArray(i, flags) > -1){
				if((this=='t' || this=='1') && i!='id') {
					$scope.ds[i] = true;
				}
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
		});
	}

	$scope.$watch('ds.harvest_method', function(newv, oldv){
		if($scope.ds.harvester_methods && newv!=oldv){
			$.each($scope.ds.harvester_methods.harvester_config.harvester_methods, function(){
				if(newv==this.id) {
					$scope.harvest_params = {};
					$scope.harvest_method_desc = this.description;
					$.each(this.params, function(){
						$scope.harvest_params[this.name] = true;
					});
					return false;
				}
			});
		}
	});

	$scope.$watch('ds.manual_publish', function(newv, oldv){
		if(oldv!=undefined && newv!=undefined) {
			console.log(oldv, newv);
			if((!oldv || oldv=='f' || oldv=='0') && newv) {
				$scope.modal = {
					'title':'Alert',
					'body':'Enabling the ‘Manually Publish Records’ option will require you to <br />manually publish your approved records via the Manage My Records screen.'
				}
				$('#modal').modal();
			} else if((!newv || newv=='f' || newv=='0') && oldv) {
				$scope.modal = {
					'title':'Alert',
					'body':'Disabling the ‘Manually Publish Records’ option <br />will cause your approved records to be published automatically. <br/>This means your records will be publically visible in <br />Research Data Australia immediately after being approved.'
				}
				$('#modal').modal();
			}
		}
	});

	$scope.$watch('ds.qa_flag', function(newv, oldv){
		if(oldv!=undefined && newv!=undefined) {
			console.log(oldv, newv);
			if((!oldv || oldv=='f' || oldv=='0') && newv) {
				$scope.modal = {
					'title':'Alert',
					'body':'Enabling the ‘Quality Assessment Required’ option will <br/>send any records entered into the ANDS registry from this data source <br />through the Quality Assessment workflow.'
				}
				$('#modal').modal();
			} else if((!newv || newv=='f' || newv=='0') && oldv) {
				var publishStr = '';
				var pubStat = 'published';

				if($scope.ds.manual_publish) {
					pubStat = 'approved'
				}

				if(parseInt($scope.ds.count_SUBMITTED_FOR_ASSESSMENT) > 0) {
					publishStr += $scope.ds.count_SUBMITTED_FOR_ASSESSMENT + '`Submitted for Assessment`';
				}
				if(parseInt($scope.ds.count_ASSESSMENT_IN_PROGRESS) > 0) {
					publishStr += ' and ' + $scope.ds.count_ASSESSMENT_IN_PROGRESS + ' `Assessment in Progress`'
				}
				$scope.modal = {
					'title':'Alert',
					'body':'Disabling the ‘Quality Assessment Required’ option will cause <br />'+publishStr+' records to be automatically '+pubStat+'. <br />It will also prevent any future records from being sent through <br />the Quality Assessment workflow.'
				}
				$('#modal').modal();
			}
		}
	});

}

function ViewCtrl($scope, $routeParams, ds_factory, $location, $timeout) {
	$scope.stage = 'loading';
	$scope.ds = {};
	$scope.offset = 0;
	$scope.harvester = {};
	$scope.importer = {};

	if(!$scope.timers) $scope.timers = [];

	$scope.get = function(id) {
		ds_factory.get(id).then(function(data){
			$scope.ds = data.items[0];
			$scope.refresh_harvest_status();
			if($scope.ds.logs) $scope.ds.latest_log = $scope.ds.logs[0].id;
			if($scope.ds.logs.length < 10) $scope.nomore = true;
			document.title = $scope.ds.title + ' - Dashboard';
		});
	}
	$scope.get($routeParams.id);

	$scope.get_latest_log = function(click) {
		$scope.ds.refreshing = true;
		ds_factory.get_log($scope.ds.id, 0, 10, $scope.ds.latest_log).then(function(data){
			$scope.ds.refreshing = false;
			var timeout = 3000;
			if (data.status=='OK' && data.items.length > 0) {
				var logs = data.items.reverse();
				$.each(logs, function(){
					$scope.ds.logs.unshift(this);
				});
				if($scope.ds.logs) $scope.ds.latest_log = $scope.ds.logs[0].id;
			} else if(data.items.length == 0) {
				timeout = 10000;
			}
			if(!click) $timeout($scope.get_latest_log, timeout);
		});
	}
	$timeout($scope.get_latest_log, 1000);

	$scope.more_logs = function() {
		$scope.offset = $scope.offset + 10;
		ds_factory.get_log($scope.ds.id, $scope.offset, 10, false).then(function(data){
			if(data.items.length < 10) $scope.nomore = true;
			$.each(data.items, function(){
				$scope.ds.logs.push(this);
			});
			if($scope.ds.logs) $scope.ds.latest_log = $scope.ds.logs[0].id;
		});
	}

	$scope.refresh_harvest_status = function() {
		ds_factory.get_harvester_status($scope.ds.id).then(function(data){
			$scope.harvester = data.items[0];

			//can_start, can_stop
			switch($scope.harvester.status) {
				case 'IDLE': $scope.harvester.can_start = true; $scope.harvester.can_stop = true; break;
				case 'HARVESTING': $scope.harvester.can_start = false; $scope.harvester.can_stop = true; break;
				case 'IMPORTING': $scope.harvester.can_start = false; $scope.harvester.can_stop = false; break;
				case 'STOPPED': $scope.harvester.can_start = true; $scope.harvester.can_stop = false; break;
				case 'COMPLETED': $scope.harvester.can_start = true; $scope.harvester.can_stop = false; break;
				case 'SCHEDULED': $scope.harvester.can_start = true; $scope.harvester.can_stop = true; break;
				case 'WAITING': $scope.harvester.can_start = false; $scope.harvester.can_stop = true; break;
			}

			//parse message
			try {
				$scope.harvester.message = JSON.parse($scope.harvester.message);
				if($scope.harvester.message.progress.total!='unknown' && $scope.harvester.message.progress.current) {
					$scope.harvester.percent =  ($scope.harvester.message.progress.current * 100) / $scope.harvester.message.progress.total;
					$scope.harvester.percent = $scope.harvester.percent.toFixed(2);
				}

			} catch (err) {
				console.error($scope.harvester.message);
			}

			$timeout($scope.refresh_harvest_status, 10000);
		});
	}
	$timeout($scope.refresh_harvest_status, 10000);

	$scope.start_harvest = function() {
		ds_factory.start_harvest($scope.ds.id).then(function(data) {
			$scope.refresh_harvest_status();
		});
	}

	$scope.stop_harvest = function() {
		ds_factory.stop_harvest($scope.ds.id).then(function(data) {
			$scope.refresh_harvest_status();
		});
	}

	$scope.open_import_modal = function(method) {
		$scope.importer.type = method;
		switch (method) {
			case 'url' :
				$scope.importer.title = 'Import From URL';
				break;
			case 'xml':
				$scope.importer.title = 'Import From Pasted XML';
				break;
			case 'upload':
				$scope.importer.title = 'Import From Uploading File';
				break;
			case 'path':
				$scope.importer.title = 'Import From Harvested Path';
				break;
		}
		$('#import_modal').modal('show');
	}

	$scope.import = function() {
		$scope.importer.result = {};
		if($scope.importer.type && !$scope.importer.running) {
			$scope.importer.running = true;
			switch ($scope.importer.type) {
				case 'url' :
					data = {
						'url': $('#importer_url').val()
					}
					break;
				case 'xml':
					data = {
						'xml': $('#importer_xml').val()
					}
					break;
				case 'upload':
					data = {
						'file':$('#importer_file').val()
					}
					break;
				case 'path':
					data = {
						'path':$('#importer_batch').val()
					}
					break;
			}
			ds_factory.import($scope.ds.id, $scope.importer.type, data).then(function(data){
				$scope.importer.running = false;
				$scope.importer.result = {};
				$scope.importer.result.message = data.message;
				if(data.status=='OK') {
					$scope.importer.result.type = 'success'
					$scope.get($scope.ds.id);
				} else {
					$scope.importer.result.type = 'error';
				}
			});
		}
	}

	$scope.mmr_link = function(type, name) {
		window.location = base_url+'data_source/manage_records/'+$scope.ds.id+'/#!/{"sort":{"updated":"desc"},"filter":{"'+type+'":"'+name+'"}}';
	}

	$scope.remove = function() {
		if (window.confirm("You are about to delete data source '"+ $scope.ds.title +"'.\n Deleting this Data Source will remove it from the registry and delete all of its records.\n Do you want to continue?") === true){
			ds_factory.remove($scope.ds.id).then(function(data){
				$location.path('/');
			});
		}
	}
}

function bind_plugins($scope) {
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