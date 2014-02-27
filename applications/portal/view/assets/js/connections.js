angular.module('connections',['portal-filters', 'infinite-scroll']).
factory('searches', function($http){
	return{
		search: function(filters){
			var promise = $http.post(base_url+'search/filter/', {'filters':filters}).then(function(response){
				return response.data;
			});
			return promise;
		}
	}
}).
controller('openConnections', function($scope, searches){

	$scope.results = {};
	$scope.filters = {};
	$scope.facet = {};
	$scope.query = '';
	$scope.facet_limit = 5;
	$scope.relations = [];
	$scope.page = 1;
	$scope.numFound = 0;
	$scope.loading = false;
	$scope.done = false;

	$scope.class_name = $('#class').text();

	$scope.$watch('query', function(){
		$scope.filters['q'] = $scope.query;
		if($scope.query!=''){
			$scope.search();
		}
	});

	$scope.open = function($event){

		var c = $('#connections_layout_container');

		$('.ui-widget-overlay').live('click', function() {
			c.dialog( "close" );
		});

		$scope.current_relation = '';

		var relation_type = $($event.target).attr('relation_type');
		$scope.filters = {};
		switch(relation_type){
			case 'collection': 
				$scope.filters['class'] = 'collection';
				delete $scope.filters['type'];
				break;
			case 'party_one':
				$scope.filters['class'] = 'party';
				$scope.filters['type'] = 'person';
		}

		$scope.current_relation = $scope.filters['class'];

		// c.attr('title', $scope.getTitle());

		c.dialog({
			width:900,
			modal:true
		});

		$('#ui-dialog-title-connections_layout_container').html($scope.getTitle());

		$scope.search();
	}

	$scope.getTitle = function(){
		if($scope.filters['class']=='collection'){
			return 'Related Collections';
		}else if($scope.filters['class']=='activity'){
			return 'Related Activities';
		}else if($scope.filters['class']=='service'){
			return 'Related Services';
		}else if($scope.filters['class']=='party'){
			if($scope.filters['type']=='person'){
				return 'Related Researchers';
			}else if($scope.filters['type']=='group'){
				return 'Related Organisations & Groups';
			}
		}else{
			return 'Related Objects';
		}
	}

	$scope.$watch('results.docs', function(){
		var total_found = $('.ro_preview').length;
		if($scope.numFound <= $scope.results.docs.length){
			$scope.done = true;
		}
	}, true);

	$scope.load_more = function(){
		if(($('#connections_layout_container').dialog('isOpen')===true) && !$scope.done && !$scope.loading){
			$scope.loading = true;
			var ro_id = $('#registry_object_id').text();
			$scope.page++;
			$scope.filters['related_object_id'] = ro_id;
			$scope.filters['include_facet_subjects'] = 1;
			$scope.filters['p'] = $scope.page;
			searches.search($scope.filters).then(function(data){
				$scope.loading = false;
				$.each(data.result.docs, function(){
					$scope.results.docs.push(this);
				});
				$scope.getRelations();
			});
		}
	}

	$scope.select = function(type, value){
		$scope.filters[type] = value;
		$scope.search();
	}

	$scope.deselect = function(type){
		delete $scope.filters[type];
		$scope.search();
	}

	$scope.infacet = function(type, v){
		if($scope.filters[type]==v.title){
			return true;
		}else return false;
	}

	$scope.search = function(){
		var ro_id = $('#registry_object_id').text();
		$scope.page = 1;
		$scope.done = false;
		$scope.filters['related_object_id'] = ro_id;
		$scope.filters['include_facet_subjects'] = 1;
		$scope.filters['p'] = $scope.page;
		searches.search($scope.filters).then(function(data){
			$scope.numFound = data.numFound;
			$scope.relations = [];
			$scope.results = data.result;
			$scope.facet = data.facet_result;
			$scope.getRelations();
		});
	}

	$scope.getRelations = function(){
		var ro_id = $('#registry_object_id').text();
		$.each($scope.results.docs, function(){
			var ind = this.related_object_id.indexOf(ro_id);
			var relation = this.related_object_relation[ind];
			var related_class = this.related_object_class[ind];
			$scope.relations[this.id] = {related_relation:relation, related_class:related_class};
		});
	}
});