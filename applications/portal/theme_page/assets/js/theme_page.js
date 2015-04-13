rda_service_url = registry_url+'services/rda/';
angular.module('portal_theme',[]).
	factory('pages', function($http){
		return {
			getPage: function(slug){
				var promise = $http.get(base_url+'theme_page/get/'+slug).then(function(response){
					return response.data;
				});
				return promise;
			}
		}
	}).
	factory('searches', function($http){
		return{
			search: function(filters){
				var promise = $http.post(base_url+'registry_object/filter', {'filters':filters}).then(function(response){
					return response.data;
				});
				return promise;
			},
			constructFilterArray: function(search){
				var filters = {};
				filters['q'] = search.query ? decodeURIComponent(search.query) : false;
				filters['limit'] = search.limit ? search.limit : false;
				filters['random'] = search.random ? search.random : false;
				if(filters['random']=='') delete filters['random'];
				if(filters['limit']=='') delete filters['limit'];
				angular.forEach(search.fq, function(f){
					if(filters[f.name]) {
						if(angular.isArray(filters[f.name])) {
							filters[f.name].push(f.value);
						} else {
							var prev = filters[f.name];
							filters[f.name] = [];
							filters[f.name].push(prev);
							filters[f.name].push(f.value);
						}
					} else filters[f.name] = f.value;
				});
				return filters;
			},
			contructFilterQuery: function(filters) {
				var filter_query = '';
				angular.forEach(filters, function(f,key){
					if(f){
						if(key!='limit' && key!='random'){
							filter_query += key+'='+encodeURIComponent(f)+'/';
						}
					}
				});
				return filter_query;
			},
			getByList: function(list_ro){
				return $http.post(rda_service_url+'getByList/', {'list_ro': list_ro}).then(function(response){
					return response.data;
				});
			},
			getConnections: function(key){
				return $http.get(rda_service_url+'getConnections/?registry_object_key='+encodeURIComponent(key)).then(function(response){
					return response.data;
				});
			}
		}
	}).
	directive('colorbox', function(){
		return {
			restrict: 'AC',
			link: function(scope, element, attrs){
				$(element).colorbox({
					maxWidth:'100%',
					maxHeight:'100%'
				});
			}
		}
	}).
	directive('carousel', function(){
		return {
			restrict: 'A',
			link: function(scope, element, attrs){
				$(element).flexslider({
				    animation: "slide",
				    animationLoop:true,
				    slideshowSpeed: 2500,
				    pauseOnHover:true,
				    directionNav:false,
				    itemWidth: 260,
				    itemMargin: 40,
				  });
			}
		}
	}).
	directive('filmstrip', function(){
		return {
			restrict : 'A',
			link: function(scope, element, attrs){
				$(element).flexslider({
					animation:'slide',
					controlNav: false,
					directionNav: true,
					animationLoop: false,
					itemWidth: 260,
					itemMargin: 2,
					move:1,
					prevText:'',
					nextText:''
				});
			}
		}
	}).
	filter('class_name', function(){
		return function(text){
			switch(text){
				case 'collection': return 'Collections';break;
				case 'activity': return 'Activities';break;
				case 'party': return 'Parties';break;
				case 'party_one': return 'People';break;
				case 'party_multi': return 'Organisations & Groups';break;
				case 'service': return 'Services';break;
				default: return text;break;
			}
		}
	}).
	directive('themeSearch', function($log, searches){
		return {
			restrict: 'ACME',
			templateUrl:base_url+'assets/theme_page/js/templates/search.html',
			scope: {
				id:'='
			},
			controller: function($scope, $log, searches) {
				$scope.base_url = base_url;
				$scope.$parent.$watch('page', function(newv){
					if(newv) {
						$scope.el = $scope.$parent.getSearch($scope.id);
						if ( typeof $scope.el.search!=='undefined' ) {
							if (typeof $scope.el.search.limit!=='undefined' && !$scope.el.search.limit && $scope.el.search.limit!=0) $scope.el.search.limit = 15;
							// $log.debug('found search', $scope.el);
							var search = $scope.el.search;
							var filters = searches.constructFilterArray(search);
							$scope.filter_query = searches.contructFilterQuery(filters);
							
							searches.search(filters).then(function(data){
								$scope.result = data;
								// $log.debug('results for ', $scope.id, data);
							});
						}
					}
				});
			}
		}
	}).
	directive('themeFacet', function($log, searches){
		return {
			restrict: 'ACME',
			templateUrl:base_url+'assets/theme_page/js/templates/facet.html',
			scope: {
				id:'='
			},
			controller: function($scope, $log, searches) {
				$scope.limit = 5;
				$scope.base_url = base_url;
				$scope.$parent.$watch('page', function(newv){
					if(newv) {
						$scope.el = $scope.$parent.getSearch($scope.id);
						$scope.facetel = $scope.$parent.getFacet($scope.id);
						// $log.debug('found facet el',$scope.facetel);
						if($scope.el){
							// $log.debug('found', $scope.el);
							var search = $scope.el.search;
							var filters = searches.constructFilterArray(search);
							$scope.filter_query = searches.contructFilterQuery(filters);
							
							searches.search(filters).then(function(data){
								$scope.result = data;
								// $log.debug('results for ', $scope.id, data);
								$scope.facet_result = data.facet_counts.facet_fields[$scope.facetel.facet.type];
								// $log.debug('facet found ', $scope.facet_result);

								$scope.facet = [];
								for(var i=0;i<$scope.facet_result.length -1 ;i+=2) {
									$scope.facet.push({name: $scope.facet_result[i], value:$scope.facet_result[i+1]});
								}
								// $log.debug('facet extracted ', $scope.facet);
							});
						} else {
							// $log.debug('not found', $scope.id);
						}
						
					}
				});
					
				$scope.viewAll = function() {
					$scope.limit = 300;
				}
			}
		}
	}).
	directive('listRo', function($log,searches){
		return {
			restrict: 'ACME',
			templateUrl:base_url+'assets/theme_page/js/templates/list-ro.html',
			transclude: true,
			scope: {},
			compile: function(element, attrs, transclude){
				
				// $log.debug('every instance ', element);
				return function($scope,iElement,iAttrs){
					$scope.base_url = base_url;
					// $log.debug('this instance', element);
					transclude($scope,function(clone){
						// $log.debug('clone: ', clone);
						$scope.list = [];
						$.each($('li.ro',clone), function(){
							$scope.list.push($(this).text());
						});
						// $log.debug('list unresolved:',$scope.list);
						searches.getByList($scope.list).then(function(data){
							$scope.resolved = data.ros;
						});
					});
				}
			}
		}
	}).
	directive('themeRelation', function($log, searches){
		return {
			restrict: 'ACME',
			templateUrl:base_url+'assets/theme_page/js/templates/relation.html',
			transclude: false,
			scope: {

			},
			compile: function(element, attrs, transclude){

				// $log.debug('every instance ', element);
				return function(scope,iElement,iAttrs){
					scope.base_url = base_url;
					// $log.debug('this instance', iAttrs);
					scope.key = iAttrs.key;
					// $log.debug(scope.type);
					scope.conn = [];
					searches.getConnections(scope.key).then(function(data){
						// $log.debug('connections for ', scope.key, data);
						// $log.debug(data.connections);
						angular.forEach(data.connections, function(i,k){
							
							if(i[iAttrs.type]){
								// $log.debug('found ', i[iAttrs.type]);
								angular.forEach(i[iAttrs.type], function(obj){
									scope.conn.push(obj);
								});
							}
						});
						// $log.debug(scope.conn);
					});
				}
			}
		}
	})
	.filter('orderObjectBy', function($log) {
	  return function(items, field, reverse) {
	    var filtered = [];
	    angular.forEach(items, function(item) {
	      filtered.push(item);
	    });
	    filtered.sort(function (a, b) {
	    	var asort = (typeof(a[field])=='string' ? a[field].toLowerCase() : a[field]);
	    	var bsort = (typeof(b[field])=='string' ? b[field].toLowerCase() : b[field]);
	    	return (asort > bsort ? 1 : -1);
	    });
	    if(reverse) filtered.reverse();
	    return filtered;
	  };
}).
	controller('init', function($scope, pages, searches, $filter, $log){
		$scope.search_results = {}; 
		$scope.slug = $('#slug').val();
		pages.getPage($scope.slug).then(function(data){
			$scope.page = data;
			// $log.debug($scope.page);
		});

		$scope.getSearch = function(id) {
			var found = false;
			if($scope.page){
				angular.forEach($scope.page.left, function(i,k){
					if(i.type=='search' && !found) {
						if(i.search.id==id) {
							found = i;
						}
					}
				});
				angular.forEach($scope.page.right, function(i){
					if(i.type=='search' && !found) {
						if(i.search.id==id) {
							found = i;
						}
					}
				}); 
			}
			return found;
		}

		$scope.getFacet = function(id) {
			var found = false;
			if($scope.page){
				angular.forEach($scope.page.left, function(i,k){
					if(i.type=='facet' && !found) {
						if(i.facet.search_id==id) {
							found = i;
						}
					}
				});
				angular.forEach($scope.page.right, function(i){
					if(i.type=='facet' && !found) {
						if(i.facet.search_id==id) {
							found = i;
						}
					}
				}); 
			}
			return found;
		}

		
	});

