/**
 * AngularJS module connections, used for displaying a modal with a mini-search interface
 * This module depends on the outter container has ng-app="connections" and ng-controller="openConnections"
 *
 * 
 * @requires portal-filters infinite-scroll modules
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
angular.module('connections',['portal-filters', 'infinite-scroll']).
/**
 * Search factory to use for solr searching, used default RDA search protocol
 * @param  module $http
 * @return promise
 */
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
/**
 * Default controller for connections module. This controller auto-fire on start
 * @param  $scope
 * @param  factory searches
 * @return void
 */
controller('openConnections', function($scope, searches){

	/**
	 * Default Scope assignment
	 * @type $rootScope
	 */
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


	/**
	 * Triggers when an action ng-click="open($event)" occur
	 * This will do a search then open the modal containing the search result
	 * @param event $event
	 * @return void
	 */
	$scope.open = function($event){

		/**
		 * The relation type is set within the target event
		 * This relation type will determine the class and type we will search for
		 */
		var relation_type = $($event.target).attr('relation_type');
		$scope.filters = {};
		switch(relation_type){
			case 'collection': 
				$scope.filters['class'] = 'collection';
				delete $scope.filters['type'];
				break;
			case 'activity': 
				$scope.filters['class'] = 'activity';
				delete $scope.filters['type'];
				break;
			case 'service': 
				$scope.filters['class'] = 'service';
				delete $scope.filters['type'];
				break;
			case 'party_multi': 
				$scope.filters['class'] = 'party';
				delete $scope.filters['group'];
				break;
			case 'party_one':
				$scope.filters['class'] = 'party';
				$scope.filters['type'] = 'person';
		}

		//search
		$scope.search();

		//dialog setup and open
		var c = $('#connections_layout_container');
		$('.ui-widget-overlay').live('click', function() {
			c.dialog( "close" );
		});

		c.dialog({
			width:900,
			modal:true
		});

		//Change the title of the dialog
		$('#ui-dialog-title-connections_layout_container').html($scope.getTitle());
	}

	/**
	 * Helper function to return the title for displaying on the dialog
	 * @return string
	 */
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

	/**
	 * Watch every instance changes of $scope.query
	 * and do a search accordingly
	 * @return void
	 */
	$scope.$watch('query', function(){
		$scope.filters['q'] = $scope.query;
		if($scope.query!=''){
			$scope.search();
		}
	});

	/**
	 * Watch every instance change of the result set
	 * If we have enough result then stop searching
	 * @return $scope.done
	 */
	$scope.$watch('results.docs', function(){
		var total_found = $('.ro_preview').length;
		if($scope.numFound <= $scope.results.docs.length){
			$scope.done = true;
		}
	}, true);

	/**
	 * Load the next page and append it to the result set
	 * This function requires the dialog to be open, there's more to search and not currently doing a search
	 * @return void
	 */
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

	/**
	 * Select a facet
	 * Change variables then redo the search
	 * @param  string type
	 * @param  string value
	 * @return $scope.search
	 */
	$scope.select = function(type, value){
		$scope.filters[type] = value;
		$scope.search();
	}

	/**
	 * Deselect a facet
	 * Delete the variable then redo the search
	 * @param  string type
	 * @return $scope.search
	 */
	$scope.deselect = function(type){
		delete $scope.filters[type];
		$scope.search();
	}

	/**
	 * Check if a value type pair is selected
	 * @param  facet_type type
	 * @param  value v
	 * @return boolean
	 */
	$scope.infacet = function(type, v){
		if($scope.filters[type]==v.title){
			return true;
		}else return false;
	}

	/**
	 * Actual Search function
	 * Used registry_object_id from the DOM as related_object_id restraint
	 * Include subject facets
	 * 
	 * @return result set
	 */
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

	/**
	 * Update the relation array, for use in displaying relations next to search result
	 * Works by matching the registry object id pair to the relation and relation class
	 * Filters are utilized in the front end
	 * @return void
	 */
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