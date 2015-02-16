var app = angular.module('app', ['ngRoute', 'portal-filters', 'ui.bootstrap', 'profile_components', 'record_components']);

app.config(function($interpolateProvider, $locationProvider, $logProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');

	$locationProvider.hashPrefix('!');

	$logProvider.debugEnabled(true);
});

app.controller('searchCtrl', function($scope, $log, $modal, search_factory){
	
	$scope.$watch(function(){
		return location.hash;
	},function(){
		$scope.filters = search_factory.ingest(location.hash);
		$scope.sync();
		// $log.debug('after sync', $scope.filters, search_factory.filters, $scope.query, search_factory.query, $scope.search_type);
		$scope.search();
	});

	$scope.hashChange = function(){
		// $log.debug($scope.query, search_factory.query);
		$scope.filters.q = $scope.query;
		search_factory.update('filters', $scope.filters);
		// $log.debug(search_factory.filters, search_factory.filters_to_hash(search_factory.filters));
		var hash = search_factory.filters_to_hash(search_factory.filters)
		// $log.debug('changing hash to ', hash);

		//only change the hash at search page, other page will navigate to the search page
		if (location.href.indexOf('search')==-1) {
			location.href = base_url+'search/#' + '!/' + hash;
		} else {
			location.hash = '!/'+hash;			
		}
	}

	$scope.search = function(){
		search_factory.search($scope.filters).then(function(data){
			// search_factory.updateResult(data);
			search_factory.update('result', data);
			search_factory.update('facets', search_factory.construct_facets(data));
			$scope.sync();
			// $log.debug('result', $scope.result);
			// $log.debug($scope.result, search_factory.result);
		});
	}

	$scope.sync = function(){
		$scope.filters = search_factory.filters;
		$scope.query = search_factory.query;
		$scope.search_type = search_factory.search_type;
		$scope.result = search_factory.result;
		$scope.facets = search_factory.facets;
		$scope.pp = search_factory.pp;
		$scope.sort = search_factory.sort;
		$scope.advanced_fields = search_factory.advanced_fields;

		//construct the pagination
		if ($scope.result) {
			// $log.debug($scope.result);
			$scope.page = {
				cur: ($scope.filters['p'] ? parseInt($scope.filters['p']) : 1),
				rows: ($scope.filters['rows'] ? parseInt($scope.filters['rows']) : 15),
				range: 3,
				pages: [],
			}
			$scope.page.end = Math.ceil($scope.result.response.numFound / $scope.page.rows);
			for (var x = ($scope.page.cur - $scope.page.range); x < (($scope.page.cur + $scope.page.range)+1);x++ ) {
				if (x > 0 && x <= $scope.page.end) {
					$scope.page.pages.push(x);
				}
			}
		}

		//get all facets by deleting the existing facets restrain from the filters
		var filters_no_facet = {};
		angular.copy($scope.filters, filters_no_facet);
		angular.forEach($scope.facets, function(content, index){
			delete filters_no_facet[index];
		});
		// $log.debug($scope.filters, filters_no_facet);
		// $log.debug(filters_no_facet);
		search_factory.search(filters_no_facet).then(function(data){
			$scope.allfacets = search_factory.construct_facets(data);
			// $log.debug($scope.allfacets);
		});

		// $log.debug($scope.result);
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
		if(execute) $scope.hashChange();
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

	$scope.clearFilter = function(type, value) {
		if(typeof $scope.filters[type]=='string') {
			if(type=='q') $scope.q = '';
			delete $scope.filters[type];
		} else if(typeof $scope.filters[type]=='object') {
			var index = $scope.filters[type].indexOf(value);
			$scope.filters[type].splice(index, 1);
		}
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

	$scope.changeFilter = function(type, value) {
		$scope.filters[type] = value;
		$scope.hashChange();
	}

	$scope.goto = function(x) {
		$scope.filters['p'] = ''+x;
		$scope.hashChange();
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


	/**
	 * Advanced Search Section
	 */
	$scope.advanced = function(active){
		if (active) {
			$scope.selectAdvancedField(active);
		}
		$('#advanced_search').modal('show');
	}
	
	$scope.selectAdvancedField = function(name) {
		// $log.debug('selecting', name);
		angular.forEach($scope.advanced_fields, function(f){
			if (f.name==name) {
				f.active = true;
			} else f.active = false;
		});
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
		if($scope.filters[type]) {
			if(typeof $scope.filters[type]!='object') {
				return 1;
			} else if(typeof $scope.filters[type]=='object') {
				return $scope.filters[type].length;
			}
		} else return 0;
	}

});

app.factory('search_factory', function($http, $log){
	return {
		status : 'idle',
		filters: [],
		query: '',
		search_type: 'q',
		result: null,
		facets: null,
		pp : [
			{value:15,label:'Show 15'},
			{value:30,label:'Show 30'},
			{value:60,label:'Show 60'},
			{value:100,label:'Show 100'}
		],

		sort : [
			{value:'score desc',label:'Relevance'},
			{value:'title asc',label:'Title A-Z'},
			{value:'title desc',label:'Title Z-A'},
			{value:'title desc',label:'Popular'},
			{value:'record_created_timestamp asc',label:'Date Added'},
		],

		advanced_fields: [
			{'name':'terms', 'display':'Search Terms', 'active':true},
			{'name':'group', 'display':'Contributors'},
			{'name':'access_rights', 'display':'Access Rights'},
			{'name':'license_class', 'display':'License'},
			{'name':'type', 'display':'Types'},
			{'name':'spatial', 'display':'Spatial'},
			{'name':'class', 'display':'Class'}
		],

		ingest: function(hash) {
			this.filters = this.filters_from_hash(hash);
			if (this.filters.q) this.query = this.filters.q;
			return this.filters;
		},

		update: function(which, what) {
			this[which] = what;
		},

		search: function(filters){
			this.status = 'loading';
			// $log.debug('search filters', filters);
			var promise = $http.post(base_url+'registry_object/filter', {'filters':filters}).then(function(response){
				this.status = 'idle';
				// $log.debug('response', response.data);
				return response.data;
			});
			return promise;
		},

		construct_facets: function(result) {
			var facets = {};
			angular.forEach(result.facet_counts.facet_fields, function(item, index) {
				facets[index] = [];
				for (var i = 0; i < result.facet_counts.facet_fields[index].length ; i+=2) {
					var fa = {
						name: result.facet_counts.facet_fields[index][i],
						value: result.facet_counts.facet_fields[index][i+1]
					}
					facets[index].push(fa);
				}
			});
			// $log.debug('facets', facets);
			return facets;
		},

		filters_from_hash:function(hash) {
			var xp = hash.split('/');
			var filters = {};
			$.each(xp, function(){
				var t = this.split('=');
				var term = t[0];
				var value = t[1];
				if(term=='rows') value = parseInt(value);
				if(term && value && term!=''){

					if(filters[term]) {
						if(typeof filters[term]=='string') {
							var old = filters[term];
							filters[term] = [];
							filters[term].push(old);
							filters[term].push(value);
						} else if(typeof filters[term]=='object') {
							filters[term].push(value);
						}
					} else {
						filters[term] = value;
					}
					
				}
			});
			return filters;
		},
		filters_to_hash: function(filters) {
			var hash = '';
			$.each(filters, function(i,k){
				if(typeof k!='object'){
					hash+=i+'='+k+'/';
				} else if (typeof k=='object'){
					$.each(k, function(){
						hash+=i+'='+decodeURIComponent(this)+'/';
					});
				}
			});
			return hash;
		}
	}
});