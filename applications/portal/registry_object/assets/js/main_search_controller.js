function mainSearchController($scope, search_factory, profile_factory, $location, $sce, uiGmapGoogleMapApi, $timeout) {
	$scope.search_type = 'all';
	$scope.filters = {};
	$scope.prefilters = {}; //prefilters used to store temporary values like temporal and spatial
	$scope.result = {};
	$scope.fields = ['title', 'description', 'subject'];
	$scope.allfilters = [];
	$scope.loading = false;
	$scope.selectState = 'selectAll';

	$scope.selected = [];

	$scope.pp = [
		{value:15,label:'Show 15'},
		{value:30,label:'Show 30'},
		{value:60,label:'Show 60'},
		{value:100,label:'Show 100'}
	];

	$scope.sort = [
		{value:'score desc',label:'Relevance'},
		{value:'title asc',label:'Title A-Z'},
		{value:'title desc',label:'Title Z-A'},
		{value:'title desc',label:'Popular'},
		{value:'record_created_timestamp asc',label:'Date Added'},
	];

	$scope.$on('$locationChangeSuccess', function() {
		$scope.filters = search_factory.filters_from_hash($location.path());
		$scope.populateFilters();
		$scope.search();
		$scope.$broadcast('filters', {'filters':$scope.filters, 'query':$scope.query});
	});

	$scope.$watch('filters', function(newv, oldv){
		if(newv) {
			$scope.allfilters = [];
			$.each($scope.filters, function(i,k){
				if(i!='p' && k && i!='rows' && i!='sort' && i!='class') {
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
		$scope.filters.q = $scope.query;
		if ($scope.search_type!='all') {
			$scope.cleanfilters();
			$scope.filters[$scope.search_type] = $scope.q;
		}
		var hash = $scope.getHash();
		$location.path(hash);
	}

	$scope.clearSearch = function(){
		$scope.filters = {};
		$scope.query = '';
		$scope.hashChange();
	}

	$scope.$on('inisearch', function(e, s){
		console.log('received', s);
		if(s.url) {
			window.location = s.url;
		}
	});

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

	$scope.populateFilters = function() {
		$scope.query = ($scope.filters.q ? $scope.filters.q : '');
		$.each($scope.fields, function(){
			if($scope.filters[this]) {
				$scope.search_type = this.toString();
				$scope.query = $scope.filters[this];
			}
		});

		//temporal
		if ($scope.filters['temporal'] && $scope.filters['temporal'].indexOf('-')) {
			var split = $scope.filters['temporal'].split('-');
			$scope.prefilters.dateFrom = parseInt(split[0]);
			$scope.prefilters.dateTo = parseInt(split[1]);
		}
	}

	$scope.search = function() {

		if ($scope.loading) return false;

		$scope.loading = true;

		// $scope.filters.q = $scope.q;
		if ($scope.search_type!='all') {
			$scope.cleanfilters();
			$scope.filters[$scope.search_type] = $scope.q;
		}
		if(!$scope.filters['rows']) $scope.filters['rows'] = 15;
		if(!$scope.filters['sort']) $scope.filters['sort'] = 'score desc';
		if(!$scope.filters['class']) $scope.filters['class'] = 'collection';
		$scope.populateFilters();

		$('.sresult').addClass('fadeOutRight');

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

			$scope.$broadcast('search_complete');
			$scope.loading = false;
			
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

	$scope.addKeyWord = function(key) {
		if (key) {
			$scope.toggleFilter('refine', key);
			$scope.extra_keywords = '';
		}
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

	$scope.cleanfilters = function() {
		$.each($scope.fields, function(){
			delete $scope.filters[this];
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
}