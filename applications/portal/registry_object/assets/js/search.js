var app = angular.module('app', ['ngRoute', 'ngSanitize', 'portal-filters', 'ui.bootstrap', 'ui.utils', 'profile_components', 'record_components', 'queryBuilder', 'lz-string', 'angular-loading-bar', 'ui.select', 'uiGmapgoogle-maps']);

app.config(function($interpolateProvider, $locationProvider, $logProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');

	$locationProvider.hashPrefix('!');

	$logProvider.debugEnabled(true);
});

app.config(function(uiGmapGoogleMapApiProvider) {
    uiGmapGoogleMapApiProvider.configure({
        //    key: 'your api key',
        v: '3.17',
        libraries: 'weather,drawing,geometry,visualization'
    });
});


app.controller('searchCtrl', 
function($scope, $log, $modal, search_factory, vocab_factory, profile_factory, uiGmapGoogleMapApi){
	

	$scope.class_choices = [
		{'name':'collection', 'val':'Collection', 'selected':true},
		{'name':'activity', 'val':'Activity', 'selected':false},
		{'name':'party', 'val':'Party', 'selected':false},
		{'name':'service', 'val':'Services', 'selected':false}
	];
	
	$scope.$watch(function(){
		return location.hash;
	},function(){
		$scope.filters = search_factory.ingest(location.hash);
		$scope.sync();
		if($scope.filters.cq) {
			$scope.$broadcast('cq', $scope.filters.cq);
		}
		// $log.debug('after sync', $scope.filters, search_factory.filters, $scope.query, search_factory.query, $scope.search_type);
		$scope.search();
	});

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

	$scope.$watch('search_type', function(newv,oldv){
		if (newv) {
			delete $scope.filters['q'];
			delete $scope.filters[oldv];
			$scope.filters[newv] = $scope.query;
		}
	});

	$scope.hasFilter = function(){
		var empty = {'q':''};
		if(!angular.equals($scope.filters, empty)) {
			return true;
		} else return false;
	}

	$scope.clearSearch = function(){
		search_factory.reset();
		$scope.$broadcast('clearSearch');
		$scope.sync();
		$scope.hashChange();
	}

	$scope.isLoading = function(){
		if(location.href.indexOf('search')>-1 && $scope.loading) {
			return true;
		} else return false;
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

		//only change the hash at search page, other page will navigate to the search page
		if (location.href.indexOf('search')==-1) {
			location.href = base_url+'search/#' + '!/' + hash;
		} else {
			location.hash = '!/'+hash;			
		}
	}

	$scope.filters_to_hash = function() {
		return search_factory.filters_to_hash($scope.filters);
	}

	$scope.search = function(){
		$scope.loading = true;
		search_factory.search($scope.filters).then(function(data){
			$scope.loading = false;
			// search_factory.updateResult(data);
			search_factory.update('result', data);
			search_factory.update('facets', search_factory.construct_facets(data));

			$scope.sync();
			$scope.$broadcast('search_complete');
			$scope.populateCenters($scope.result.response.docs);
			// $log.debug('result', $scope.result);
			// $log.debug($scope.result, search_factory.result);
		});
	}

	$scope.presearch = function(){
		search_factory.search($scope.prefilters).then(function(data){
			$scope.preresult = data;
			$scope.populateCenters($scope.preresult.response.docs);
		})
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
		}

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

			//get temporal range
			search_factory.search().then(function(data){
				// $log.debug(data);
				$scope.temporal_range = search_factory.temporal_range(data);
			});
			
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
			// 
		});

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

	$scope.showFilter = function(filter_name){
		var show = true;
		if (filter_name=='cq' || filter_name=='rows' || filter_name=='sort') {
			show = false;
		}
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

	$scope.clearFilter = function(type, value, execute) {
		if(typeof $scope.filters[type]!='object') {
			if(type=='q') {
				$scope.query = '';
				search_factory.update('query', '');
				$scope.filters['q'] = '';
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

	$scope.changeFilter = function(type, value, execute) {
		$scope.filters[type] = value;
		if (execute===true) {
			$scope.hashChange();
		}
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

	$scope.add_user_data = function(type) {
		if(type=='saved_record') {
			profile_factory.add_user_data('saved_record', $scope.selected).then(function(data){
				alert('done');
			});
		} else if(type=='saved_search') {
			profile_factory.add_user_data('saved_search', $scope.getHash()).then(function(data){
				alert('done');
			});
		}
	}


	/**
	 * Advanced Search Section
	 */
	$scope.prefilters = {};
	$scope.advanced = function(active){
		$scope.prefilters = {};
		$scope.preresult = {};
		angular.copy($scope.filters, $scope.prefilters);
		if (active && active!='close') {
			$scope.selectAdvancedField(active);
			$('#advanced_search').modal('show');
		} else if(active=='close'){
			$('#advanced_search').modal('hide');
		}else {
			$scope.selectAdvancedField('terms')
			$('#advanced_search').modal('show');
		}
		$scope.presearch();
	}

	$scope.advancedSearch = function(){
		$scope.filters = {};
		angular.copy($scope.prefilters, $scope.filters);
		$scope.query = $scope.prefilters.q;

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
		} else if(typeof $scope.prefilters[type]=='object') {
			var index = $scope.prefilters[type].indexOf(value);
			$scope.prefilters[type].splice(index, 1);
		}
		if(execute) $scope.presearch();
	}
	
	$scope.selectAdvancedField = function(name) {
		// $log.debug('selecting', name);
		angular.forEach($scope.advanced_fields, function(f){
			if (f.name==name) {
				f.active = true;
			} else f.active = false;
		});
		$scope.presearch();
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
		} else if(type=='review'){
			if($scope.preresult && $scope.preresult.response) {
				return $scope.preresult.response.numFound;
			} else return 0;
			
		} else return 0;
	}

	//VOCAB TREE
	//
	//
	
	$scope.vocabInit = function() {
		vocab_factory.get(false, $scope.filters).then(function(data){
			$scope.vocab_tree = data;
		});
		//getting vocabulary in configuration, mainly for matching isSelected
		vocab_factory.getSubjects().then(function(data){
			// $log.debug(data);
			vocab_factory.subjects = data;
		});
	}

	$scope.getSubTree = function(item) {
		if(!item['subtree']) {
			vocab_factory.get(item.uri, $scope.filters).then(function(data){
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
	uiGmapGoogleMapApi.then(function(maps) {
		$scope.map = {
			center:{
				latitude:-25.397, longitude:133.644
			},
			zoom:4,
			bounds:{},
			options: {
				disableDefaultUI: true,
				panControl: false,
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
			     polylineOptions: polyOption,
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

		available_search_type: [
			'q', 'title', 'identifier', 'related_people', 'related_organisations', 'description'
		],

		class_choices: [
			'collection', 'party' , 'service', 'activity'
		],

		default_filters: {
			'rows':15,
			'sort':'score desc',
			'class':'collection'
			// 'spatial_coverage_centres': '*'
		},

		sort : [
			{value:'score desc',label:'Relevance'},
			{value:'title asc',label:'Title A-Z'},
			{value:'title desc',label:'Title Z-A'},
			{value:'title desc',label:'Popular'},
			{value:'record_created_timestamp asc',label:'Date Added'},
		],

		advanced_fields: [
			{'name':'terms', 'display':'Search Terms', 'active':true},
			{'name':'subject', 'display':'Subjects'},
			{'name':'group', 'display':'Contributors'},
			{'name':'access_rights', 'display':'Access Rights'},
			{'name':'license_class', 'display':'License'},
			{'name':'temporal', 'display':'Temporal'},
			{'name':'spatial', 'display':'Spatial'},
			{'name':'class', 'display':'Class'},
			{'name':'review', 'display':'Review'}
		],

		advanced_fields_activity: [
			{'name':'terms', 'display':'Search Terms', 'active':true},
			{'name':'type', 'display':'Types'},
			{'name':'activity_status', 'display':'Status'},
			{'name':'subject', 'display':'Subjects'},
			{'name':'administering_institution', 'display':'Administering Institution'},
			{'name':'date_range', 'display':'Date Range'},
			{'name':'funders', 'display':'Funders'},
			{'name':'funding_scheme', 'display':'Funding Scheme'},
			{'name':'class', 'display':'Class'},
			{'name':'review', 'display':'Review'}
		],

		collection_facet_order: ['group', 'access_rights', 'license_class'],
		activity_facet_order: ['type', 'activity_status', 'funding_scheme', 'administering_institution', 'funders'],

		ingest: function(hash) {
			this.filters = this.filters_from_hash(hash);
			if (this.filters.q) this.query = this.filters.q;
			// $log.debug(this.available_search_type);
			var that = this;
			angular.forEach(this.available_search_type, function(x){
				if (that.filters.hasOwnProperty(x)) {
					that.query = that.filters[x];
					that.search_type = x;
				}
			});
			return this.filters;
		},

		reset: function(){
			this.filters = {q:''};
			this.search_type = 'q';
			this.query = '';
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

			//subjects DEPRECATED in favor of ANZSRC codes directly from the home page
			// facets['subject'] = [];
			// angular.forEach(result.facet_counts.facet_queries, function(item, index) {
			// 	var fa = {
			// 		name: index,
			// 		value: parseInt(item)
			// 	}
			// 	facets['subject'].push(fa)
			// });

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

			var order = this.collection_facet_order;

			if(this.filters['class']=='activity'){
				var order = this.activity_facet_order;
			}

			var orderedfacets = [];
			angular.forEach(order, function(item){
				// orderedfacets[item] = facets[item]
				orderedfacets.push({
					name: item,
					value: facets[item]
				});
			});

			// $log.debug(result.facet_counts.facet_fields.earliest_year);
			

			$log.debug('orderedfacet', orderedfacets);
			// $log.debug('facets', facets);
			return orderedfacets;
		},

		temporal_range: function(result) {
			var range = [];
			var earliest_year = false;
			var latest_year = false;

			if(result.facet_counts.facet_fields.earliest_year) {
				earliest_year = result.facet_counts.facet_fields.earliest_year[0];
			}
			if(result.facet_counts.facet_fields.latest_year) {
				latest_year = result.facet_counts.facet_fields.latest_year[0];
			}

			if(earliest_year && latest_year) {
				// $log.debug(earliest_year, latest_year);
				for(i = parseInt(earliest_year); i < parseInt(latest_year);i++){
					range.push(i);
				}
			}

			return range;
		},

		filters_from_hash:function(hash) {
			var xp = hash.split('/');
			var filters = {};
			$.each(xp, function(){
				var t = this.split('=');
				var term = t[0];
				var value = t[1];
				if(term=='rows'||term=='year_from'||term=='year_to') value = parseInt(value);
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

			angular.forEach(this.default_filters, function(content,type){
				if(!filters[type]) filters[type] = content;
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
		get: function (term, filters) {
			var url = '';
			if (term) {
				url = '?uri='+term;
			}
			return $http.post(base_url+'registry_object/vocab/'+url, {'filters':filters}).then(function(response){
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
			} else if(filters['anzsrc-for']){
				var found = false;
				if(filters['anzsrc-for']==item.notation){
					found = true;
				} else if (angular.isArray(filters['anzsrc-for'])) {
					angular.forEach(filters['anzsrc-for'], function(code){
						if(code==item.notation && !found) {
							found =  true;
						}
					});
				}
				return found;
			} else {
				return false;
			}
		},
		getSubjects: function(){
			return $http.get(base_url+'registry_object/getSubjects').then(function(response){
				return response.data
			});
		},
		resolveSubjects: function(subjects){
			return $http.post(base_url+'registry_object/resolveSubjects', {data:subjects}).then(function(response){
				return response.data
			});
		}
	}
});

app.directive('resolve', function($http, $log, vocab_factory){
	return {
		template: '<ul class="listy"><li ng-repeat="item in result"><a href="" ng-click="toggleFilter(\'anzsrc-for\', item.notation, true)">{{item.label}} <small><i class="fa fa-remove"></i></small></a></li></ul>',
		scope: {
			subjects: '=subjects',
			vocab: '='
		},
		transclude: true,
		link: function(scope) {
			scope.result = [];
			scope.$watch('subjects', function(newv){
				if(newv) {
					scope.result = [];
					vocab_factory.resolveSubjects(scope.subjects).then(function(data){
						// $log.debug(data);
						angular.forEach(data, function(label, notation){
							scope.result.push({notation:notation,label:label});
						});
						// $log.debug(scope.result);
					});
				}
			});

			scope.toggleFilter = function(type, value, execute) {
				scope.$emit('toggleFilter', {type:type,value:value,execute:execute});
			}
		}
	}
});

app.directive('mappreview', function($log, uiGmapGoogleMapApi){
	return {
		template: '<a href="" ng-click="advanced(\'spatial\')"><img src="{{static_img_src}}"/></a><div></div>',
		scope: {
			sbox: '=',
			centres: '=',
			polygons: '=',
			draw:'='
		},
		transclude:true,
		link: function(scope, element) {
			uiGmapGoogleMapApi.then(function(){
				if(element && scope.draw=='map'){
					//the map
					var myOptions = {
					  zoom: 4,
					  center: new google.maps.LatLng(-25.397, 133.644),
					  disableDefaultUI: true,
					  panControl: true,
					  zoomControl: true,
					  mapTypeControl: true,
					  scaleControl: true,
					  streetViewControl: false,
					  overviewMapControl: false,
					  scrollwheel:false,
					  mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					$(element).height('200px');
					map = new google.maps.Map(element[0],myOptions);

					var bounds = new google.maps.LatLngBounds();

					//centres
					angular.forEach(scope.centres, function(centre){
						$log.debug('centre', stringToLatLng(centre).toString());
						var marker = new google.maps.Marker({
							map:map,
							position: stringToLatLng(centre),
		        		    draggable: false,
		        		    raiseOnDrag:false,
		        		    visible:true
						});
					});

					//polygons
					angular.forEach(scope.polygons, function(polygon){
						$log.debug('polygon', polygon);
						split = polygon.split(' ');
						if(split.length>1) {
						    mapContainsOnlyMarkers = false;
						    coords = [];
						    $.each(split, function(){
						        coord = stringToLatLng(this);
						        coords.push(coord);
						        bounds.extend(coord);
						    });
						    poly = new google.maps.Polygon({
						        paths: coords,
						        strokeColor: "#FF0000",
						        strokeOpacity: 0.8,
						        strokeWeight: 2,
						        fillColor: "#FF0000",
						        fillOpacity: 0.35
						    });
						    poly.setMap(map);
						}else{
						    var marker = new google.maps.Marker({
						        map: map,
						        position: stringToLatLng(polygon),
						        draggable: false,
						        raiseOnDrag:false,
						        visible:true
						    });
						    bounds.extend(stringToLatLng(polygon));
						}
					});
					// $log.debug(bounds);
					map.fitBounds(bounds);
					// $log.debug(map.getZoom());

					google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
					    $log.debug('bound change zoom', map.getZoom());
					});

				} else if(element && scope.draw=='static') {
					scope.static_img_src = 'https://maps.googleapis.com/maps/api/staticmap?center=-32.75,144.75&zoom=8&size=328x200&maptype=roadmap&markers=color:red%7C|-32.75,144.75&path=color:0xFFFF0033|fillcolor:0xFFFF0033|weight:5|-32.5,145|-33,145|-33,144.5|-32.5,144.5|-32.5,145';

					var src = 'https://maps.googleapis.com/maps/api/staticmap?maptype=roadmap&size=328x200';

					//center
					if(scope.centres && scope.centres.length > 0){
						var center = scope.centres[0];
						var lat,lon;
						angular.forEach(scope.centres, function(centre){
							var coord = stringToLatLng(centre);
							lat = coord.lat();
							lon = coord.lng();
						});
						src +='&center='+lat+','+lon;
					}
					

					//markers
					var markers = [];
					// if(scope.centres && scope.centres.length > 0) {
					// 	markers.push(lat+','+lon);
					// }

					//bounds
					var bounds = new google.maps.LatLngBounds();

					//polygon
					var polys = [];
					angular.forEach(scope.polygons, function(polygon){
						split = polygon.split(' ');
						if(split.length>1) {
						    mapContainsOnlyMarkers = false;
						    coords = [];
						    $.each(split, function(){
						        coord = stringToLatLng(this);
						        coords.push(coord);
						        bounds.extend(coord);
						    });
						    poly = new google.maps.Polygon({
						        paths: coords
						    });
						    // $log.debug(poly.getPath());
						    // $log.debug('encoded', google.maps.geometry.encoding.encodePath(poly.getPath()));
						    polys.push(google.maps.geometry.encoding.encodePath(poly.getPath()));
						}else{
							var coord = stringToLatLng(polygon);
							markers.push(coord.lat()+','+coord.lng());
						    bounds.extend(coord);
						}
					});

					if (markers.length > 0){
						// $log.debug(markers);
						angular.forEach(markers, function(marker){
							src+='&markers=color:red%7C|'+marker;
						});
					}

					if (polys.length > 0) {
						// $log.debug(polys);
						angular.forEach(polys, function(poly){
							src+='&path=color:0xFF0000|fillcolor:0xFF000045|weight:2|enc:'+poly;
						});
					}

					var mapDim = {height:200,width:328};
					src +='&zoom='+getBoundsZoomLevel(bounds, mapDim);

					scope.static_img_src = src;
					// $log.debug(src);
				}

				function stringToLatLng(str){
				    var word = str.split(',');
				    if(word[0] && word[1]) {
				    	var lat = word[1];
				    	var lon = word[0];
				    } else {
				    	var word = str.split(' ');
				    	var lat = word[1];
				    	var lon = word[0];
				    }
				    var coord = new google.maps.LatLng(parseFloat(lat), parseFloat(lon));
				    return coord;
				}

				function extendBounds(bounds, coordinates) {
				    for (b in coordinates) {
				        bounds.extend(coordinates[b]);            
				    };
				    console.log(bounds.toString());
				};

				function getBoundsZoomLevel(bounds, mapDim) {
				    var WORLD_DIM = { height: 256, width: 256 };
				    var ZOOM_MAX = 21;

				    function latRad(lat) {
				        var sin = Math.sin(lat * Math.PI / 180);
				        var radX2 = Math.log((1 + sin) / (1 - sin)) / 2;
				        return Math.max(Math.min(radX2, Math.PI), -Math.PI) / 2;
				    }

				    function zoom(mapPx, worldPx, fraction) {
				        return Math.floor(Math.log(mapPx / worldPx / fraction) / Math.LN2);
				    }

				    var ne = bounds.getNorthEast();
				    var sw = bounds.getSouthWest();

				    var latFraction = (latRad(ne.lat()) - latRad(sw.lat())) / Math.PI;

				    var lngDiff = ne.lng() - sw.lng();
				    var lngFraction = ((lngDiff < 0) ? (lngDiff + 360) : lngDiff) / 360;

				    var latZoom = zoom(mapDim.height, WORLD_DIM.height, latFraction);
				    var lngZoom = zoom(mapDim.width, WORLD_DIM.width, lngFraction);

				    return Math.min(latZoom, lngZoom, ZOOM_MAX);
				}
			});
			// $log.debug(scope.centres);
			
			scope.advanced = function(active) {
				scope.$emit('advanced', active);
			}
		}
	}
});