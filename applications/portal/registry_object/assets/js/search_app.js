var app = angular.module('app', ['ngRoute', 'ngSanitize', 'search_components', 'profile_components'], function($interpolateProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');
});

app.config(function($routeProvider, $locationProvider) {
    $locationProvider.hashPrefix('!');
});

app.filter('trustAsHtml', ['$sce', function($sce){
	return function(text){
		var decoded = $('<div/>').html(text).text();
		return $sce.trustAsHtml(decoded);
	}
}]);


app.directive('tooltip', function(){
    return {
        restrict: 'A',
        link: function(scope, element, attrs){
            $(element).hover(function(){
                // on mouseenter
                $(element).tooltip('show');
            }, function(){
                // on mouseleave
                $(element).tooltip('hide');
            });
        }
    };
});

app.controller('mainController', function($scope, search_factory, profile_factory, $location, $sce) {
	$scope.q = '';
	$scope.search_type = 'all';
	$scope.filters = {};
	$scope.result = {};
	$scope.fields = ['title', 'description', 'subject'];
	$scope.allfilters = [];
	$scope.allfacets = [];
	$scope.loading = false;

	$scope.advanced_search = {};
	$scope.advanced_search.fields = search_factory.advanced_fields();

	$scope.selected = [];

	$scope.pp = [
		{value:15,label:'Show 15'},
		{value:50,label:'Show 50'},
		{value:100,label:'Show 100'}
	];

	$scope.sort = [
		{value:'score desc',label:'Relevence'},
		{value:'title asc',label:'Title A-Z'},
	];

	$scope.selectAdvancedField = function(field) {
		$.each($scope.advanced_search.fields, function(){
			this.active = false;
		});
		field.active = true;
	}

	$scope.advanced = function(select) {
		if (select) {
			$.each($scope.advanced_search.fields, function(){
				if(this.name==select){
					$scope.selectAdvancedField(this);
				}
			});		
		}
		$('#advanced_search').modal();
	}

	$scope.closeAdvanced = function() {
		$('#advanced_search').modal('hide');	
	}
	// $scope.advanced();

	$scope.$on('$locationChangeSuccess', function() {
		$scope.filters = search_factory.filters_from_hash($location.path());
		$scope.populateFilters();
		$scope.search();
	});

	$scope.$watch('filters', function(newv, oldv){
		if(newv) {
			$scope.allfilters = [];
			$.each($scope.filters, function(i,k){
				if(i!='p' && k && i!='rows' && i!='sort') {
					if(typeof k!='object') {
						$scope.allfilters.push({'name':i,'value':k.toString()});
					} else if(typeof k=='object') {
						$.each(k,function(){
							$scope.allfilters.push({'name':i,'value':this.toString()});
						});
					}
				}
			});
		}
	});

	$scope.hashChange = function(){
		$scope.filters.q = $scope.q;
		if ($scope.search_type!='all') {
			$scope.cleanfilters();
			$scope.filters[$scope.search_type] = $scope.q;
		}
		var hash = $scope.getHash();
		$location.path(hash);
	}

	$scope.getHash = function() {
		var hash = '';
		$.each($scope.filters, function(i,k){
			if(typeof k!='object'){
				hash+=i+'='+k+'/';
			} else if (typeof k=='object'){
				$.each(k, function(){
					hash+=i+'='+this+'/';
				});
			}
		});
		return hash;
	}

	$scope.search = function() {

		if ($scope.loading) return false;

		$scope.loading = true;

		$scope.filters.q = $scope.q;
		if ($scope.search_type!='all') {
			$scope.cleanfilters();
			$scope.filters[$scope.search_type] = $scope.q;
		}
		if(!$scope.filters['rows']) $scope.filters['rows'] = 15;
		if(!$scope.filters['sort']) $scope.filters['sort'] = 'score desc';
		$scope.populateFilters();

		//regular search
		search_factory.search($scope.filters).then(function(data){
			$scope.result = data;
			$scope.result.facets = {};

			//construct the facets array
			$.each($scope.result.facet_counts.facet_fields, function(j,k){
				$scope.result.facets[j] = [];
				for (var i = 0; i < $scope.result.facet_counts.facet_fields[j].length;i+=2){
					var fa = {
						name: $scope.result.facet_counts.facet_fields[j][i],
						value: $scope.result.facet_counts.facet_fields[j][i+1]
					}
					$scope.result.facets[j].push(fa);
				}
			});

			//construct the highlighting array
			$.each($scope.result.highlighting, function(i,k){
				$.each($scope.result.response.docs, function(){
					if(this.id==i && !$.isEmptyObject(k)) {
						this.hl = k;
					}
				});
			});

			//construct the pagination
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

			$scope.loading = false;
			
		});

		//search without filters
		var dumb_filters = {
			'q':$scope.filters['q']
		};
		search_factory.search(dumb_filters).then(function(data){
			$.each(data.facet_counts.facet_fields, function(j,k){
				$scope.allfacets[j]=[];
				for(var i=0;i<data.facet_counts.facet_fields[j].length-1;i+=2){
					var fa = {
						name: data.facet_counts.facet_fields[j][i],
						value:data.facet_counts.facet_fields[j][i+1]
					}
					$scope.allfacets[j].push(fa);
				}
			});
		});
	}

	$scope.toggleResult = function(ro) {
		var exist = false;
		$.each($scope.selected, function(i,k){
			if(k && ro.id == k.id) {
				$scope.selected.splice(i, 1);
				exist = true;
			}
		});
		if(!exist) $scope.selected.push(ro);
	}

	$scope.addKeyWord = function(key) {
		if (key) {
			$scope.toggleFilter('refine', key);
			$scope.extra_keywords = '';
		}
	}

	$scope.isAdvancedSearchActive = function(type) {
		if($scope.advanced_search.fields.length){
			for (var i=0;i<$scope.advanced_search.fields.length;i++){
				if($scope.advanced_search.fields[i].name==type && $scope.advanced_search.fields[i].active) {
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

	/**
	 * Go to a page
	 * @param  {int} x 
	 * @return {search}
	 */
	$scope.goto = function(x) {
		$scope.filters['p'] = ''+x;
		$scope.hashChange();
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
		if(!execute) $scope.hashChange();
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

	$scope.cleanfilters = function() {
		$.each($scope.fields, function(){
			delete $scope.filters[this];
		});
	}

	$scope.populateFilters = function() {
		$scope.q = ($scope.filters.q ? $scope.filters.q : '');
		$.each($scope.fields, function(){
			if($scope.filters[this]) {
				$scope.search_type = this.toString();
				$scope.q = $scope.filters[this];
			}
		});
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
});