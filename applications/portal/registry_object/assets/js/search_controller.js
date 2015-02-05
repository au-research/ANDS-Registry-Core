function searchController($scope, search_factory, uiGmapGoogleMapApi, $log){
	$scope.filters = {};
	$scope.query = '';
	$scope.search_type = 'q';
	$scope.allfacets = [];
	
	$scope.class_choices = [
		{'name':'collection', 'val':'Collection', 'selected':true},
		{'name':'activity', 'val':'Activity', 'selected':false},
		{'name':'party', 'val':'Party', 'selected':false},
		{'name':'service', 'val':'Services', 'selected':false}
	];

	$scope.hashChange = function(){
		var search_url = base_url+'search/#!/';
		$scope.filters[$scope.search_type] = $scope.query;
		var hash = search_factory.filters_to_hash($scope.filters);
		var url = search_url + hash;
		window.location = url;
	}

	$scope.getHash = function(){
		var hash = search_factory.filters_to_hash($scope.filters);
		return hash;
	}

	$scope.$watch('search_type', function(newv, oldv){
		var search_types = ['q', 'title', 'identifier', 'related_people', 'related_organisation', 'description'];
		$.each(search_types, function(){
			delete $scope.filters[this];
		});
		$scope.filters[newv] = $scope.query;
	});

	$scope.$on('filters', function(e, s){
		$scope.filters = s.filters;
		$scope.query = s.query;
	});

	$scope.advanced = function(select) {
		if (select) {
			$.each($scope.advanced_search.fields, function(){
				if(this.name==select){
					$scope.selectAdvancedField(this);
				}
			});
		}
		$('#advanced_search').modal();
		// if ($scope.mapInstance) google.maps.event.trigger($scope.mapInstance, "resize");
	}

	$scope.advanced = function(){
		$('#advanced_search').modal();
	}

	$scope.advanced_search = {};
	$scope.advanced_search.fields = search_factory.advanced_fields();
	search_factory.search($scope.filters).then(function(data){
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

	$scope.selectAdvancedField = function(field) {
		$.each($scope.advanced_search.fields, function(){
			this.active = false;
		});
		field.active = true;
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
		console.log($scope.filters);
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

	$scope.populateFilters = function() {
		$scope.q = ($scope.filters.q ? $scope.filters.q : '');
		$.each($scope.fields, function(){
			if($scope.filters[this]) {
				$scope.search_type = this.toString();
				$scope.q = $scope.filters[this];
			}
		});

		//temporal
		if ($scope.filters['temporal'] && $scope.filters['temporal'].indexOf('-')) {
			var split = $scope.filters['temporal'].split('-');
			$scope.prefilters.dateFrom = parseInt(split[0]);
			$scope.prefilters.dateTo = parseInt(split[1]);
		}
	}

	uiGmapGoogleMapApi.then(function(maps) {
		$scope.map = {
			// new google.maps.LatLng(-25.397, 133.644)
			center: {
				latitude: -25.397, longitude: 133.644
			}, 
			zoom: 4,
			events: {
				tilesloaded: function(map){
					$scope.$apply(function(){
						$scope.$parent.mapInstance = map;
						google.maps.event.trigger($scope.$parent.mapInstance, "resize");
					});
				},
				mouseover: function(map) {
					$scope.$apply(function(){
						google.maps.event.trigger($scope.$parent.mapInstance, "resize");
					});
				}
			}
		};
		$scope.options = {
			disableDefaultUI: true,
		  	panControl: false,
		  	navigationControl: false,
		  	scrollwheel: true,
		  	scaleControl: true
		};
		$scope.showWeather = false;
    });

	$scope.centres = [];

    $scope.$on('search_complete', function(){
    	//construct the array of centres
		$.each($scope.result.response.docs, function(){
			if(this.spatial_coverage_centres){
				//1st one
				var pair = this.spatial_coverage_centres[0];
				var split = pair.split(' ');
				var lon = split[0];
				var lat = split[1];
				// console.log(this.spatial_coverage_centres,pair,split,lon,lat)
				$scope.centres.push(
					{
						id: this.id,
						title: this.title,
						longitude: lon,
						latitude: lat,
						showw:true,
						onClick: function() {
							this.showw=!this.showw;
						}
					}
				);
			}
		});
    });

    $scope.$on('overwrite_map', function(){
    	$log.debug('overwritten');
    });
}