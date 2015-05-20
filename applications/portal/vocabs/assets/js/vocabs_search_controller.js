app.controller('searchCtrl', function($scope, $log, vocabs_factory){
	
	$scope.vocabs = [];
	$scope.filters = {};

	$scope.search = function() {
		vocabs_factory.search($scope.filters).then(function(data){
			$log.debug(data);
			$scope.result = data;
			facets = [];
			angular.forEach(data.facet_counts.facet_fields, function(item, index) {
				facets[index] = [];
				for (var i = 0; i < data.facet_counts.facet_fields[index].length ; i+=2) {
					var fa = {
						name: data.facet_counts.facet_fields[index][i],
						value: data.facet_counts.facet_fields[index][i+1]
					}
					facets[index].push(fa);
				}
			});
			$scope.facets = facets;
		});
	}
	$scope.search();

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
		if(execute) $scope.search();
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
		if(execute) $scope.search();
	}

});