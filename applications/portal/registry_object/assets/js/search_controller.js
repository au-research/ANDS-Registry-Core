app.controller('searchCtrl', 
function($scope, $log, $modal, search_factory, vocab_factory, profile_factory, uiGmapGoogleMapApi){

	$scope.sf = search_factory;

	//setting default search class
	if ($('#ro_id').length) {
		var search_class = $('#ro_class').val();
		search_factory.update_class(search_class);
	}

    $scope.query_title = 'Untitled Query';
    $scope.saved_records_folder = 'Untitled';
    $scope.base_url = base_url;

	$scope.class_choices = [
		{value:'collection', label:'Data'},
		{value:'party', label:'People and Organisations'},
		{value:'service', label:'Services and Tools'},
		{value:'activity', label:'Grants and Projects'}
	];

	$scope.vocab = 'anzsrc-for';
	$scope.vocab_choices = [
		{value:'anzsrc-for', label: 'ANZSRC FOR'},
		{value:'anzsrc-seo', label: 'ANZSRC SEO'},
		{value:'anzsrc', label: 'ANZSRC'},
		{value:'keywords', label: 'Keywords'},
		{value:'scot', label: 'School of Online Thesaurus'},
		{value:'pont', label: 'Powerhouse Museum Object Name Thesaurus'},
		{value:'psychit', label: 'Thesaurus of Psychological Index Terms'},
		{value:'apt', label: 'Australian Pictorial Thesaurus'},
		{value:'lcsh', label: 'LCSH'},
        {value:'gcmd', label: 'Global Change Master Directory Keywords'}
	];

	$scope.$watch(function(){
		return location.hash;
	},function(){
		var hash = location.hash ? location.href.split("#")[1] : '';
		if ( $('#refer_q').length ) {
			hash = $('#refer_q').val();
		}
		$scope.filters = search_factory.ingest(hash);
		angular.copy($scope.filters, $scope.prefilters);
		$scope.sync();
		if($scope.filters.cq) {
			$scope.$broadcast('cq', $scope.filters.cq);
		}
		// $log.debug('after sync', $scope.filters, search_factory.filters, $scope.query, search_factory.query, $scope.search_type);
		$scope.search();
	});

	$scope.$on("$locationChangeSuccess", function () {
		// $log.debug(location.hash);
	})

	$scope.getHash = function(){
		var hash = '';
		$.each($scope.filters, function(i,k){
			if(typeof k!='object'){
				hash+=i+'='+k+'/';
			} else if (typeof k=='object'){
				$.each(k, function(){
					hash+=i+'='+encodeURIComponent(this)+'/';
				});
			}
		});
		return hash;
	}

	$scope.isArray = angular.isArray;

	$scope.$on('toggleFilter', function(e, data){
		$scope.toggleFilter(data.type, data.value, data.execute);
	});
	
	$scope.$on('togglePreFilter', function(e, data){
		$scope.togglePreFilter(data.type, data.value, data.execute);
	});

	$scope.$on('advanced', function(e, data){
		$scope.advanced(data);
	});

	$scope.$on('changeFilter', function(e, data){
		$scope.changeFilter(data.type, data.value, data.execute);
	});

	$scope.$on('changePreFilter', function(e, data){
		$scope.prefilters[data.type] = data.value;
	});

	$scope.$on('changeQuery', function(e, data){
		$scope.query = data;
		$scope.filters['q'] = data;
		search_factory.update('query', data);
		search_factory.update('filters', $scope.filters);
	});

	$scope.$on('changePreQuery', function(e, data){
		$scope.prefilters['q'] = data;
	});

	$scope.$watch('query', function(newv,oldv){
		if(newv!=oldv) {
			if ($scope.search_type=='q') {
				$scope.filters['q'] = newv;
			}
			else if($scope.search_type) {
				$scope.filters[$scope.search_type] = newv;
			}
		}
	});

	$scope.setSearchType = function(value) {
		$scope.search_type = value;
	}

	$scope.$watch('search_type', function(newv,oldv){
		if (newv) {
			delete $scope.filters['q'];
			delete $scope.filters[oldv];
			$scope.filters[newv] = $scope.query;
		}
	});

	$scope.getLabelFor = function(filter, value) {
		if ($scope[filter]) {
			angular.forEach($scope[filter], function(f) {
				if (f.value==value) {
					return f.label;
				}
			});
		}
	}

	$scope.hasFilter = function(){

		var has_filter = false;
		angular.forEach($scope.filters, function(val, index){
			if(index!='class' && index!='rows' && index!='sort') {
				if(val!='') {
					has_filter = true;
				}
			}
		});

		if ($scope.query!='') has_filter = true;

		return has_filter;
	}

	$scope.filterExists = function(filter) {
		var ret = false;
		if ($scope.filters[filter]) {
			ret = true;
		}
		return ret;
	}

	$scope.clearSearch = function(){
		$scope.query = '';
		search_factory.reset();
		$scope.$broadcast('clearSearch');
		$scope.sync();
		$scope.hashChange();
		$('input[name=q]').focus();
	}

	$scope.isLoading = function(){
		if(location.href.indexOf('search')>-1 && $scope.loading) {
			return true;
		} else return false;
	}
	
	$scope.newSearch = function(query) {
		if(query!='' && query!=undefined) {
			$scope.query = query;
			$scope.filters['sort'] = 'score desc';
		}
		$scope.filters['p'] = 1;
		$scope.hashChange();
	}

	$scope.hashChange = function(){
		// $log.debug('query', $scope.query, search_factory.query);
		// $scope.filters.q = $scope.query;
		if ($scope.search_type=='q') {
			$scope.filters.q = $scope.query;
		} else {
			$scope.filters[$scope.search_type] = $scope.query;
		}
		search_factory.update('filters', $scope.filters);
		// $log.debug(search_factory.filters, search_factory.filters_to_hash(search_factory.filters));
		var hash = search_factory.filters_to_hash(search_factory.filters)
		// $log.debug('changing hash to ', hash);
		// return false;
		

		//only change the hash at search page, other page will navigate to the search page
		if ($scope.onSearchPage()) {
			location.hash = '!/'+hash;
            $(window).scrollTop(0);
		} else {
			location.href = base_url+'search/#' + '!/' + hash;
            $(window).scrollTop(0);
		}
	}

	$scope.onSearchPage = function() {
		var ret = false;
		if (location.href.indexOf(base_url+'search')==0) {
			ret = true;
		}
		return ret;
	}

	$scope.filters_to_hash = function() {
		return search_factory.filters_to_hash($scope.filters);
	}

	$scope.search = function(){
		$scope.loading = true;

		if (typeof urchin_id !== 'undefined' && typeof ga !== 'undefined' && urchin_id!='' && $scope.filters['q'] && $scope.filters['q']!='' && $scope.filters['q']!==undefined) {
			ga('send', 'pageview', '/search_results.php?q='+$scope.filters['q']);
		}
	
		if (location.href.indexOf('search')>-1) {
			search_factory.search($scope.filters).then(function(data){
				$scope.loading = false;
				$scope.fuzzy = data.fuzzy_result;
				search_factory.update('result', data);
				search_factory.update('facets', search_factory.construct_facets(data));

				$scope.sync();
				$scope.$broadcast('search_complete');
				$scope.populateCenters($scope.result.response.docs);
			});
		}
		
	}

	$scope.addKeyWord = function(extra_keywords) {
		$scope.toggleFilter('refine', extra_keywords, true);
		$scope.extra_keywords = '';
	}

	$scope.presearch = function(){
		search_factory.search_no_record($scope.prefilters).then(function(data){
			$scope.preresult = data;
			$scope.prefacets = search_factory.construct_facets($scope.preresult, $scope.prefilters['class']);
			$scope.populateCenters($scope.preresult.response.docs);
			vocab_factory.get(false, $scope.prefilters, $scope.vocab).then(function(data){
				$scope.vocab_tree_tmp = data;
			});
		});
	}

	$scope.sync = function(){
		$scope.filters = search_factory.filters;

		$scope.query = search_factory.query;
		$scope.search_type = search_factory.search_type;

		// $scope.$broadcast('query', {query:$scope.query, search_type:$scope.search_type});

		$scope.result = search_factory.result;
		$scope.facets = search_factory.facets;
		$scope.pp = search_factory.pp;
		$scope.sort = search_factory.sort;
		$scope.advanced_fields = search_factory.advanced_fields;

		if($scope.filters['class']=='activity') {
			$scope.advanced_fields = search_factory.advanced_fields_activity;
			$scope.sort = search_factory.activity_sort;
		}

		if($scope.filters['class']=='activity') {
			$scope.advanced_fields = search_factory.advanced_fields_activity;
		} else if($scope.filters['class']=='collection') {
			$scope.advanced_fields = search_factory.advanced_fields;
		} else if($scope.filters['class']=='party') {
			$scope.advanced_fields = search_factory.advanced_fields_party;
		} else if($scope.filters['class']=='service') {
			$scope.advanced_fields = search_factory.advanced_fields_service;
		}

		//construct the pagination
		if ($scope.result) {
			// $log.debug($scope.result);
			$scope.page = {
				cur: ($scope.filters['p'] ? parseInt($scope.filters['p']) : 1),
				rows: ($scope.filters['rows'] ? parseInt($scope.filters['rows']) : 15),
				range: 3,
				pages: []
			}
			$scope.page.end = Math.ceil($scope.result.response.numFound / $scope.page.rows);
			for (var x = ($scope.page.cur - $scope.page.range); x < (($scope.page.cur + $scope.page.range)+1);x++ ) {
				if (x > 0 && x <= $scope.page.end) {
					$scope.page.pages.push(x);
				}
			}

			// $scope.temp

			$scope.temporal_range = search_factory.temporal_range($scope.result);
			$scope.earliest_year = $scope.temporal_range[0];
			$scope.latest_year = $scope.temporal_range[$scope.temporal_range.length - 1];

			//get temporal range
			// search_factory.search_no_record($scope.filters).then(function(data){
			// 	$scope.temporal_range = search_factory.temporal_range(data);
			// 	$log.debug(data);
			// 	$scope.earliest_year = $scope.temporal_range[0];
			// 	$scope.latest_year = $scope.temporal_range[$scope.temporal_range.length - 1];
			// });
		}

		//duplicate record matching
		if ($scope.result) {
			var matchingdoc = [];
			angular.forEach($scope.result.response.docs, function(doc){
				if (doc.matching_identifier_count) {
					matchingdoc.push(doc);
				}
			});
			// $log.debug(matchingdoc);
			angular.forEach(matchingdoc, function(doc) {
				if(!doc.hide) {
					search_factory.get_matching_records(doc.id).then(function(data){
						if (doc && !doc.hide) {
							doc.identifiermatch = data.message.identifiermatch;
							if(doc && !doc.hide) {
								angular.forEach(doc.identifiermatch, function(idd){
									$scope.hidedoc(idd.registry_object_id);
								});
							}
						}
					});
				}
			});
		}
		
		$scope.hidedoc = function(id) {
			if ($scope.result) {
				angular.forEach($scope.result.response.docs, function(doc){
					if (doc.id==id && !doc.hide) {
						doc.hide = true;
					}
				});
			}
		}

		//init vocabulary
		$scope.vocabInit();

		// $log.debug('sync result', $scope.result);
	}

	/**
	 * Getting the highlighting for a result
	 * @param  {int} id [result ID for matching]
	 * @return {hl}|false    [false if there's no highlight, highlight object if there's any]
	 */
	$scope.getHighlight = function(id){
		if ($scope.result.highlighting && !$.isEmptyObject($scope.result.highlighting[id])) {
			return $scope.result.highlighting[id];
		} else return false;
	}

	$scope.showFilter = function(filter_name, mode){
		if (!mode || mode=='undefined') mode = 'normal';
		var show = true;
		if (filter_name=='cq' || filter_name=='rows' || filter_name=='sort' || filter_name=='p' || filter_name=='class') {
			show = false;
		}
		if ($scope.filters[filter_name]=="" && mode == 'normal')  show = false;
		if ($scope.prefilters[filter_name]=="" && mode == 'advanced')  show = false;
		return show;
	}


	/**
	 * Filter manipulation
	 */
	$scope.toggleFilter = function(type, value, execute) {
		if($scope.filters[type]) {
			if($scope.filters[type]==value) {
				$scope.clearFilter(type,value);
			} else {
				if($scope.filters[type].indexOf(value)==-1) {
					$scope.addFilter(type, value);
				} else {
					$scope.clearFilter(type,value);
				}
			}
		} else {
			$scope.addFilter(type, value);
		}
		$scope.filters['p'] = 1;
		if(execute) $scope.hashChange();
	}

	$scope.toggleAccessRights = function() {
		if ($scope.filters['access_rights']) {
			delete $scope.filters['access_rights'];
		} else {
			$scope.filters['access_rights'] = 'open';
		}
		if ( $scope.onSearchPage() ) {
			$scope.hashChange();
		}
	}

	$scope.addFilter = function(type, value) {
		if($scope.filters[type]){
			if(typeof $scope.filters[type]=='string') {
				var old = $scope.filters[type];
				$scope.filters[type] = [];
				$scope.filters[type].push(old);
				$scope.filters[type].push(value);
			} else if(typeof $scope.filters[type]=='object') {
				$scope.filters[type].push(value);
			}
		} else $scope.filters[type] = value;
	}

	$scope.clearFilter = function(type, value, execute) {
		if(typeof $scope.filters[type]!='object') {
			if(type=='q') {
				$scope.query = '';
				search_factory.update('query', '');
				$scope.filters['q'] = '';
			} else if(type=='description' || type=='title' || type=='identifier' || type == 'related_people' || type == 'related_organisations' || type == 'institution' || type == 'researcher') {
				$scope.query = '';
				search_factory.update('query', '');
				delete $scope.filters[type];
				delete $scope.filters['q'];
			}
			delete $scope.filters[type];
		} else if(typeof $scope.filters[type]=='object') {
			var index = $scope.filters[type].indexOf(value);
			$scope.filters[type].splice(index, 1);
		}
		if(execute) $scope.hashChange();
	}

	$scope.isFacet = function(type, value) {
		if($scope.filters[type]) {
			if(typeof $scope.filters[type]=='string' && $scope.filters[type]==value) {
				return true;
			} else if(typeof $scope.filters[type]=='object') {
				if($scope.filters[type].indexOf(value)!=-1) {
					return true;
				} else return false;
			}
			return false;
		}
		return false;
	}

	$scope.showFacet = function(facet) {
		if ($scope.filters['class']=='collection') {
			var allowed = ['subjects', 'group', 'access_rights', 'license_class', 'temporal', 'spatial'];
		} else if($scope.filters['class']=='activity') {
			var allowed = ['type', 'activity_status', 'subjects', 'administering_institution', 'funders', 'funding_scheme', 'commencement_to', 'commencement_from', 'completion_to', 'completion_from', 'funding_amount'];
		} else {
			var allowed = ['type' ,'subjects', 'group'];
		}
		if(allowed.indexOf(facet) > -1) {
			return true;
		} else {
			return false;
		}
	}

	$scope.isPrefilterFacet = function(type, value) {
		if($scope.prefilters[type]) {
			if(typeof $scope.prefilters[type]=='string' && $scope.prefilters[type]==value) {
				return true;
			} else if(typeof $scope.prefilters[type]=='object') {
				if($scope.prefilters[type].indexOf(value)!=-1) {
					return true;
				} else return false;
			}
			return false;
		}
		return false;
	}

	$scope.changeFilter = function(type, value, execute) {
		$scope.filters[type] = value;
		if (execute===true) {
			$scope.hashChange();
		}
	}

	$scope.goto = function(x) {
		$scope.filters['p'] = ''+x;
		$scope.hashChange();
		$scope.selected = [];
		$scope.selectState = 'selectAll';
		$("html, body").animate({ scrollTop: 0 }, 500);
	}


	/**
	 * Record Selection Section
	 */
	$scope.selected = [];
	$scope.selectState = 'selectAll';
	$scope.toggleResult = function(ro) {
		var exist = false;
		$.each($scope.selected, function(i,k){
			if(k && ro.id == k.id) {
				$scope.selected.splice(i, 1);
				exist = true;
			}
		});
		if(!exist) $scope.selected.push(ro);
		if($scope.selected.length != $scope.result.response.docs.length) {
			$scope.selectState = 'deselectSelected';
		}
		if($scope.selected.length == 0) {
			$scope.selectState = 'selectAll';
		}
	}

	$scope.isSelected = function(ro) {
		var ret = false;
		angular.forEach($scope.selected, function(x, index){
			if (ro.id==x.id) {
				ret = true;
			}
		});
		return ret;
	}

	$scope.toggleResults = function() {
		if ($scope.selectState == 'selectAll') {
			$.each($scope.result.response.docs, function(){
				this.select = true;
				$scope.selected.push(this);
			});
			$scope.selectState = 'deselectAll';
		} else if ($scope.selectState=='deselectAll' || $scope.selectState=='deselectSelected') {
			$scope.selected = [];
			$.each($scope.result.response.docs, function(){
				this.select = false;
			});
			$scope.selectState = 'selectAll';
		}
	}

	$scope.add_user_data = function(type) {
		if (type=='saved_record') {
			var modalInstance = $modal.open({
			    templateUrl: base_url+'assets/registry_object/templates/moveModal.html',
			    controller: 'moveCtrl',
			    windowClass: 'modal-center',
			    resolve: {
			        id: function () {
			        	var selected = [];
			        	angular.forEach($scope.selected, function(i, index) {
			        		selected.push({
			        			id:i.id,
			        			title:i.title,
			        			slug:i.slug,
			        			group:i.group,
                                class: $scope.filters.class,
                                type: i.type,
                                saved_time:parseInt(new Date().getTime() / 1000)
			        		});
			        	});
			           	return selected;
			        }
			    }
			});
		} else if(type=='saved_search') {
			var modalInstance = $modal.open({
			    templateUrl: base_url+'assets/registry_object/templates/saveSearchModal.html',
			    controller: 'saveSearchCtrl',
			    windowClass: 'modal-center',
			    resolve: {
			        saved_search_data: function () {
			           	var data = {
			            	id:Math.random().toString(36).substring(7),
			            	query_title: '',
			                query_string: $scope.getHash(),
			                num_found: $scope.result.response.numFound,
			                num_found_since_last_check: 0,
			                num_found_since_saved:0,
			                saved_time:parseInt(new Date().getTime() / 1000),
			                refresh_time:parseInt(new Date().getTime() / 1000),
                            last_ran:parseInt(new Date().getTime() / 1000)
			            }
			            return data;
			        }
			    }
			});
		} else if(type=='export') {
			var modalInstance = $modal.open({
			    templateUrl: base_url+'assets/registry_object/templates/exportModal.html',
			    controller: 'exportCtrl',
			    windowClass: 'modal-center',
			    resolve: {
			        id: function () {
			           	return $scope.selected;
			        }
			    }
			});
		}
		modalInstance.result.then(function(){
		    //close
		}, function(){
		    //dismiss
		});
	}

	/**
	 * Advanced Search Section
	 */
	$scope.prefilters = {};
	$scope.advanced = function(active){
		// $scope.prefilters = {};
		// $scope.preresult = {};
		angular.copy($scope.filters, $scope.prefilters);
		
		//get all facets by deleting the existing facets restrain from the filters
		var filters_no_facet = {};
		angular.copy($scope.filters, filters_no_facet);
		angular.forEach($scope.facets, function(content, index){
			delete filters_no_facet[index];
		});
		search_factory.search_no_record(filters_no_facet).then(function(data){
			$scope.allfacets = search_factory.construct_facets(data);
		});

		if (active && active!='close') {
			$scope.selectAdvancedField(active);
			$('#advanced_search').modal('show');
		} else if(active=='close'){
			$('#advanced_search').modal('hide');
		}else {
			$scope.selectAdvancedField('terms')
			$('#advanced_search').modal('show');
		}

		// $log.debug($scope.prefilters);
		// $scope.selectAdvancedField(active);
		$scope.presearch();
	}

	$scope.selectAdvancedField = function(name) {
		// $log.debug('selecting', name);
		angular.forEach($scope.advanced_fields, function(f){
			if (f.name==name) {
				f.active = true;
			} else f.active = false;
		});

		$scope.prefilters2 = {};
		angular.copy($scope.prefilters, $scope.prefilters2);
		delete $scope.prefilters2[name];
		search_factory.search_no_record($scope.prefilters2).then(function(data){
			$scope.prefacets2 = search_factory.construct_facets(data, $scope.prefilters['class']);
		});

		$scope.presearch();
	}

	$scope.$watch('prefilters.class', function(newv){
		var tmp_filter = {};
		tmp_filter['class'] = newv;
		if(newv=='activity') {
			$scope.advanced_fields = search_factory.advanced_fields_activity;
			search_factory.search_no_record($scope.prefilters).then(function(data){
				$scope.temporal_range = search_factory.temporal_range(data);
			});
		} else if(newv=='collection') {
			$scope.advanced_fields = search_factory.advanced_fields;
			search_factory.search_no_record($scope.prefilters).then(function(data){
				$scope.temporal_range = search_factory.temporal_range(data);
			});
		} else if(newv=='party') {
			$scope.advanced_fields = search_factory.advanced_fields_party;
		} else if(newv=='service') {
			$scope.advanced_fields = search_factory.advanced_fields_service;
		}
		$scope.presearch();
		$scope.cleanPrefilters();
	});

	$scope.cleanPrefilters = function() {
		// var cleanout = [];
		// if ($scope.prefilters['class']=='activity') {
		// 	cleanout = ['year_from', 'year_to', 'group', 'subject', 'access_rights', 'license_class', 'temporal', 'spatial'];
		// } else {
		// 	cleanout = ['type', 'subject', 'group', 'activity_status', 'administering_institution', 'date_range', 'funders', 'funding_scheme', 'funding_amount'];
		// }
		var cleanout = ['year_from', 'year_to', 'group', 'subject', 'access_rights', 'license_class', 'temporal', 'spatial', 'type', 'subject', 'group', 'activity_status', 'administering_institution', 'date_range', 'funders', 'funding_scheme', 'funding_amount'];
		angular.forEach(cleanout, function(f) {
			delete $scope.prefilters[f];
		});
	}

	$scope.advancedSearch = function(){
		$scope.filters = {};
		angular.copy($scope.prefilters, $scope.filters);
		if($scope.prefilters['q']) {
			$scope.query = $scope.prefilters.q;
		} else {
			$scope.query = '';
			$scope.filters['q'] = '';
		}
		$log.debug($scope.filters);
		$scope.hashChange();
		$('#advanced_search').modal('hide');
	}

	$scope.togglePreFilter = function(type, value, execute) {
		// $log.debug('toggling', type,value);
		if($scope.prefilters[type]) {
			if($scope.prefilters[type]==value) {
				$scope.clearPreFilter(type,value);
			} else {
				if($scope.prefilters[type].indexOf(value)==-1) {
					$scope.addPreFilter(type, value);
				} else {
					$scope.clearPreFilter(type,value);
				}
			}
		} else {
			$scope.addPreFilter(type, value);
		}
		if(execute) $scope.presearch();
	}

	$scope.addPreFilter = function(type, value) {
		// $log.debug('adding', type,value);
		if($scope.prefilters[type]){
			if(typeof $scope.prefilters[type]=='string') {
				var old = $scope.prefilters[type];
				$scope.prefilters[type] = [];
				$scope.prefilters[type].push(old);
				$scope.prefilters[type].push(value);
			} else if(typeof $scope.prefilters[type]=='object') {
				$scope.prefilters[type].push(value);
			}
		} else $scope.prefilters[type] = value;
	}

	$scope.clearPreFilter = function(type, value, execute) {
		// $log.debug('clearing', type,value);
		if(typeof $scope.prefilters[type]!='object') {
			if(type=='q') $scope.q = '';
			delete $scope.prefilters[type];
			$scope.prefilters['cq'] = '';
			$scope.$broadcast('clearSearch');
		} else if(typeof $scope.prefilters[type]=='object') {
			var index = $scope.prefilters[type].indexOf(value);
			$scope.prefilters[type].splice(index, 1);
		}
		if(execute) $scope.presearch();
	}
	
	

	$scope.isAdvancedSearchActive = function(type) {
		if($scope.advanced_fields.length){
			for (var i=0;i<$scope.advanced_fields.length;i++){
				if($scope.advanced_fields[i].name==type && $scope.advanced_fields[i].active) {
					return true;
					break;
				}
			}
		}
		return false;
	}

	$scope.sizeofField = function(type) {
		
		var ret = 0;

		if(type=='subject') {
			var fields_array = ['anzsrc-for', 'anzsrc-seo', 'anzsrc', 'keywords', 'scot', 'pont', 'psychit', 'apt', 'gcmd', 'lcsh'];
			angular.forEach(fields_array, function(ss){
				if ($scope.prefilters[ss] ) {
					ret = 1;
				}
			});
		}

		if(type=='temporal') {
			var fields_array = ['year_from', 'year_to'];
			angular.forEach(fields_array, function(ss){
				if ($scope.prefilters[ss] ) {
					ret = 1;
				}
			});
		}

		if(type=='date_range') {
			var fields_array = ['commence_from', 'commence_to', 'completed_from', 'completed_to'];
			angular.forEach(fields_array, function(ss){
				if ($scope.prefilters[ss] ) {
					ret = 1;
				}
			});
		}

		if (type=='funding_amount') {
			var fields_array = ['funding_from', 'funding_to'];
			angular.forEach(fields_array, function(ss){
				if ($scope.prefilters[ss] ) {
					ret = 1;
				}
			});
		}

		if (type=='terms') {
			if ($scope.prefilters['q'] && $scope.prefilters['q']!='' ) {
				ret = 1;
			}
		}


		if($scope.prefilters[type]) {
			if(typeof $scope.prefilters[type]!='object') {
				ret = 1
			} else if(typeof $scope.prefilters[type]=='object') {
				return $scope.prefilters[type].length;
			}
		} else if(type=='review'){
			if($scope.preresult && $scope.preresult.response) {
				return $scope.preresult.response.numFound;
			} else {
				ret = 0;
			}
		}

		return ret;
	}

	//VOCAB TREE
	//
	//
	$scope.$watch('vocab', function(newv, oldv){
		if (newv!=oldv && $scope.isAdvancedSearchActive('subject')) {
			$scope.loading_subjects = true;
			vocab_factory.get(false, $scope.filters, $scope.vocab).then(function(data){
				$scope.vocab_tree_tmp = data;
				$scope.loading_subjects = false;
			});
		}
	});
	$scope.setVocab = function(v) {
		$scope.vocab = v;
	}

	$scope.vocabInit = function() {
		$scope.vocab = 'anzsrc-for';

		//only loads in search page, other page don't have subject facet (yet)
		if ( $scope.onSearchPage() ) {
			vocab_factory.get(false, $scope.filters, $scope.vocab).then(function(data){
				$scope.vocab_tree = data;
				$scope.vocab_tree_tmp = $scope.vocab_tree;
			});
		}
		

		// DEPRECATED. getting vocabulary in configuration, mainly for matching isSelected
		// if(!angular.equals(vocab_factory.subjects, {})) {
		// 	vocab_factory.getSubjects().then(function(data){
		// 		vocab_factory.subjects = data;
		// 	});
		// }
	}

	$scope.getSubTree = function(item) {
		item['showsubtree'] = !item['showsubtree'];
		if(!item['subtree'] && ($scope.vocab=='anzsrc-for' || $scope.vocab=='anzsrc-seo')) {
			vocab_factory.get(item.uri, $scope.filters, $scope.vocab).then(function(data){
				item['subtree'] = data;
			});
		}
	}
	
	$scope.isVocabSelected = function(item, filters) {
		if(!filters) filters = $scope.filters;
		var found = vocab_factory.isSelected(item, filters);
		if (found) {
			item.pos = 1;
		}
		return found;
	}

	$scope.isVocabParentSelected = function(item) {
		var found = false;
		
		if($scope.filters['subject']){
			var subjects = vocab_factory.subjects;
			angular.forEach(subjects[$scope.filters['subject']], function(uri){
				if(uri.indexOf(item.uri) != -1 && !found && uri!=item.uri) {
					found = true;
				}
			});
		} else if($scope.filters['anzsrc-for']) {
			if (angular.isArray($scope.filters['anzsrc-for'])) {
				angular.forEach($scope.filters['anzsrc-for'], function(code){
					if(code.indexOf(item.notation) == 0 && !found && code!=item.notation) {
						found =  true;
					}
				});
			} else if ($scope.filters['anzsrc-for'].indexOf(item.notation) ==0 && !found && $scope.filters['anzsrc-for']!=item.notation){
				found = true;
			}
		} else if($scope.filters['anzsrc-seo']) {
			if (angular.isArray($scope.filters['anzsrc-seo'])) {
				angular.forEach($scope.filters['anzsrc-seo'], function(code){
					if(code.indexOf(item.notation) == 0 && !found && code!=item.notation) {
						found =  true;
					}
				});
			} else if ($scope.filters['anzsrc-seo'].indexOf(item.notation) ==0 && !found && $scope.filters['anzsrc-seo']!=item.notation){
				found = true;
			}
		}
		if(found) {
			item.pos = 1;
		}
		return found;
	}
	// $scope.advanced('subject');
	// 
	// 
	
	//MAP
	$scope.clearMap = function() {
		$scope.searchBox.setMap(null);
		$scope.searchBox = null;
		delete $scope.filters['spatial'];
		$scope.centres = [];
	}
	uiGmapGoogleMapApi.then(function(maps) {
		$scope.map = {
			center:{
				latitude:-25.397, longitude:133.644
			},
			zoom:4,
			bounds:{},
			options: {
				disableDefaultUI: false,
				panControl: true,
				navigationControl: false,
				scrollwheel: true,
				scaleControl: true
			},
			events: {
				tilesloaded: function(map){
					$scope.$apply(function () {
				   		$scope.mapInstance = map;
				    });
				}
			}
		};

		$scope.$watch('mapInstance', function(newv, oldv){
			if(newv && !angular.equals(newv,oldv)){
				bindDrawingManager(newv);

				//Draw the searchbox
				if($scope.filters['spatial']) {
					var wsenArray = $scope.filters['spatial'].split(' ');
					var sw = new google.maps.LatLng(wsenArray[1],wsenArray[0]);
					var ne = new google.maps.LatLng(wsenArray[3],wsenArray[2]);
					//148.359375 -32.546813 152.578125 -28.998532
					//LatLngBounds(sw?:LatLng, ne?:LatLng)
					var rBounds = new google.maps.LatLngBounds(sw,ne);

					if($scope.searchBox) {
						$scope.searchBox.setMap(null);
						$scope.searchBox = null;
					}

				  	$scope.searchBox = new google.maps.Rectangle({
				  		fillColor:'#ffff00',
				  		fillOpacity: 0.4,
					    strokeWeight: 1,
					    clickable: false,
					    editable: false,
					    zIndex: 1,
				  		bounds:rBounds
				  	});
				  	// $log.debug($scope.geoCodeRectangle);
				  	$scope.searchBox.setMap($scope.mapInstance);
				}
				
			  	google.maps.event.trigger($scope.mapInstance, 'resize');
			}
		});

		function bindDrawingManager(map) {
			var polyOption = {
			    fillColor: '#ffff00',
			    fillOpacity: 0.4,
			    strokeWeight: 1,
			    clickable: false,
			    editable: false,
			    zIndex: 1
			};
			$scope.drawingManager = new google.maps.drawing.DrawingManager({
			    drawingControl: true,
			    drawingControlOptions: {
			        position: google.maps.ControlPosition.TOP_CENTER,
			            drawingModes: [
			              google.maps.drawing.OverlayType.RECTANGLE
			            ]
			     },
			     circleOptions: polyOption,
			     rectangleOptions: polyOption,
			     polygonOptions: polyOption,
			     polylineOptions: polyOption
			});
			$scope.drawingManager.setMap(map);

			google.maps.event.addListener($scope.drawingManager, 'overlaycomplete', function(e) {
				if(e.type == google.maps.drawing.OverlayType.RECTANGLE) {

					$scope.drawingManager.setDrawingMode(null);

					if($scope.searchBox){
						$scope.searchBox.setMap(null);
						$scope.searchBox = null;
					}

				   	$scope.searchBox = e.overlay;
				    var bnds = $scope.searchBox.getBounds();
				    var n = bnds.getNorthEast().lat().toFixed(6);
					var e = bnds.getNorthEast().lng().toFixed(6);
					var s = bnds.getSouthWest().lat().toFixed(6);
					var w = bnds.getSouthWest().lng().toFixed(6);

					// drawing.setMap(null);

					$scope.prefilters['spatial'] = w + ' ' + s + ' ' + e + ' ' + n;
					$scope.centres = [];
					$scope.presearch();
				}
			});
		}

	});
	
	$scope.centres = [];
	$scope.populateCenters = function(results){
		angular.forEach(results, function(doc){
			if(doc.spatial_coverage_centres){
				var pair = doc.spatial_coverage_centres[0];
				if (pair) {
					var split = pair.split(' ');
					if (split.length == 1) {
						split = pair.split(',');
					}
					
					if(split.length > 1 && split[0]!=0 && split[1]!=0){

						var lon = split[0];
						var lat = split[1];
						// console.log(doc.spatial_coverage_centres,pair,split,lon,lat)
						if(lon && lat){
							$scope.centres.push({
								id: doc.id,
								title: doc.title,
								longitude: lon,
								latitude: lat,
								showw:true,
								onClick: function() {
									doc.showw=!doc.showw;
								}
							});
						}
					}
				}
				
			}
		});
	}
});