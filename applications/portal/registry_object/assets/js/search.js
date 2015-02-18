var app = angular.module('app', ['ngRoute', 'portal-filters', 'ui.bootstrap', 'ui.utils', 'profile_components', 'record_components']);

app.config(function($interpolateProvider, $locationProvider, $logProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');

	$locationProvider.hashPrefix('!');

	$logProvider.debugEnabled(true);
});

app.controller('searchCtrl', function($scope, $log, $modal, search_factory, vocab_factory){
	
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
		} else {
			$scope.selectAdvancedField('terms')
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

	//VOCAB TREE
	vocab_factory.get().then(function(data){
		$scope.vocab_tree = data;
	});
	vocab_factory.getSubjects().then(function(data){
		// $log.debug(data);
		vocab_factory.subjects = data;
	});
	$scope.getSubTree = function(item) {
		if(!item['subtree']) {
			vocab_factory.get(item.uri).then(function(data){
				item['subtree'] = data;
			});
		}
	}
	$scope.isVocabSelected = function(item) {
		return vocab_factory.isSelected(item, $scope.filters);
	}
	$scope.isVocabParentSelected = function(item) {
		var found = false;
		var subjects = vocab_factory.subjects;
		angular.forEach(subjects[$scope.filters['subject']], function(uri){
			if(uri.indexOf(item.uri) != -1 && !found && uri!=item.uri) {
				found = true;
			}
		});
		return found;
	}
	// $scope.advanced('subject');

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
			{'name':'subject', 'display':'Subjects', 'active':true},
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
			var facets = [];

			//subjects
			facets['subject'] = [];
			angular.forEach(result.facet_counts.facet_queries, function(item, index) {
				var fa = {
					name: index,
					value: parseInt(item)
				}
				facets['subject'].push(fa)
			});

			//other facet fields
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

			var order = ['subject', 'group', 'access_rights', 'license_class'];
			var orderedfacets = [];
			angular.forEach(order, function(item){
				// orderedfacets[item] = facets[item]
				orderedfacets.push({
					name: item,
					value: facets[item]
				});
			});
			// $log.debug('orderedfacet', orderedfacets);
			// $log.debug('facets', facets);
			return orderedfacets;
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

app.factory('vocab_factory', function($http, $log){
	return {
		tree : {},
		subjects: {},
		get: function (term) {
			var url = '';
			if (term) {
				url = '?uri='+term;
			}
			return $http.get(base_url+'registry_object/vocab/'+url).then(function(response){
				return response.data
			});
		},
		isSelected: function(item, filters) {
			if (filters['subject_vocab_uri']) {
				// $log.debug(decodeURIComponent(filters['subject_vocab_uri']), item.uri);
				if(decodeURIComponent(filters['subject_vocab_uri'])==item.uri) {
					return true;
				} else if(angular.isArray(filters['subject_vocab_uri'])) {
					angular.forEach(filters['subject_vocab_uri'], function(content, index) {
						if(content==item.uri) {
							return true;
						}
					});
				}
			} else if(filters['subject']){
				var found = false;
				angular.forEach(this.subjects[filters['subject']], function(uri){
					if(uri==item.uri && !found) {
						found = true;
					}
				});
				return found;
			} else {
				return false;
			}
		},
		getSubjects: function(){
			return $http.get(base_url+'registry_object/getSubjects').then(function(response){
				return response.data
			});
		}
	}
});