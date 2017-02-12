/**
 * ORCID APP angularJS module
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
angular.module('orcid_app', ['portal-filters'])

	//Router
	.config(function($routeProvider){
		$routeProvider
			.when('/', {
				controller:IndexCtrl,
				template: $('#index').html()
			})
	})

	//Factory
	.factory('works', function($http){
		return {
			getWorks: function(data) {
				return $http.post(base_url+'/orcid/orcid_works', {data:data}).then(function(response) {return response.data});
			},
			search: function(filters) {
				return $http.post(base_url+'/services/registry/post_solr_search', {filters:filters}).then(function(response) {return response.data});
			},
			import_works: function(ro_ids) {
				return $http.post(base_url+'/orcid/import_to_orcid', {ro_ids:ro_ids}).then(function(response) {return response.data});
			}
		}
	})
;

/**
 * Primary Controller
 * @param  $scope
 * @param factory works
 */
function IndexCtrl($scope, works) {

	//Default Values
	$scope.works = {};
	$scope.to_import = [];
	$scope.import_available = false;
	$scope.filters = {};
	$scope.search_results = {};
	$scope.imported_ids= [];
	$scope.import_stg = 'ready';

	$scope.orcid = {
		orcid_id:$('#orcid_id').text(),
		first_name:$('#first_name').text(),
		last_name:$('#last_name').text()
	};

	//Overwrite the import button to only open the modal if it's not disabled
	$('.import').click(function(e){
		e.preventDefault();
		if(!$(this).hasClass('disabled')){
			$('#myModal').modal();
		}
		return false;
	});

	//Refresh functions refreshes the works, populates the imported_ids 
	$scope.refresh = function(){
		$scope.imported_ids = [];
		works.getWorks($scope.orcid).then(function(data){
			$scope.works = data.works;
			if($scope.works){
				$.each($scope.works, function(){
					if(this.type=='imported' && this.in_orcid){
						$scope.imported_ids.push(this.id);
					}
				});
			}
		});
	}
	//run once
	$scope.refresh();

	/**
	 * Watch Expression on works and search result to updat the import tag
	 * @return {[type]} [description]
	 */
	$scope.$watch('works', function(){
		$scope.review();
	}, true);

	$scope.$watch('search_results', function() {
		$scope.review();
	}, true);

	$scope.review = function(){
		$scope.import_available = false;
		$scope.to_import = [];
		if($scope.works){
			$.each($scope.works, function(){
				if(this.to_import) {
					$scope.to_import.push(this);
					$scope.import_available = true;
				}
			});
		}
		if($scope.search_results && $scope.search_results.docs){
			$.each($scope.search_results.docs, function(){
				if(this.to_import) {
					$scope.to_import.push(this);
					$scope.import_available = true;
				}
			});
		}
		$scope.import_stg = 'ready';
	}

	/**
	 * Generic SOLR search for collections
	 * @return search_result
	 */
	$scope.search = function() {
		if($scope.filters.q!=''){
			$scope.filters.rows = 100;
			$scope.filters.class = 'collection';
			works.search($scope.filters).then(function(data){
				$scope.search_results = data.result;
			});
		}
	}

	/**
	 * Import the set of to_import works to ORCID, calling the import_works factory method
	 * Increment import stages from idle->importing->complete, error is a stage
	 */
	$scope.import = function() {
		$scope.import_stg = 'importing';
		var ids = [];
		$.each($scope.to_import, function(){
			ids.push(this.id);
		});
		works.import_works(ids).then(function(data){
			if(data!=1){
				window.location = base_url+'/orcid/login';
			} else {
				$scope.import_stg = 'complete';
			}
		});
	}
}