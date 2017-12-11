/**
 * ORCID APP angularJS module
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
angular.module('orcid_app', ['portal-filters'])

	// Router
	.config(function($routeProvider){
		$routeProvider
			.when('/', {
				controller:IndexCtrl,
				template: $('#index').html()
			})
	})

	// Factory
	.factory('works', function($http){
		return {
			getWorks: function (orcid_id) {
                return $http.get(api_url + 'registry/orcids/' + orcid_id + '/works')
                    .then(function (response) {
                        return response.data
                    })
            },
			importWorks: function(orcid_id, ids) {
                return $http.post(api_url + 'registry/orcids/' + orcid_id + '/works', {ids: ids})
                    .then(function (response) {
                        return response.data
                    })
            },
			search: function (filters) {
                return $http.post(base_url + '/services/registry/post_solr_search', {filters: filters})
                    .then(function (response) {
                        return response.data
                    });
            },
			remove: function (orcid_id, work_id) {
                return $http.delete(api_url + 'registry/orcids/' + orcid_id + '/works/' + work_id)
                    .then(function(response) {
                        return response.data;
                    });
            },
            sync: function (orcid_id) {
                return $http.get(api_url + 'registry/orcids/' + orcid_id + '/sync/')
                    .then(function(response) {
                        return response.data;
                    });
            }
		}
	})
;

/**
 * Primary Controller
 * @param $scope
 * @param works
 */
function IndexCtrl($scope, works) {

	//Default Values
	$scope.works = false;
	$scope.to_import = [];
	$scope.import_available = false;
	$scope.filters = {};
	$scope.search_results = {};
	$scope.import_stg = 'ready';

	// obtain the orcid id from the DOM
	$scope.orcid = {
		id:$('#orcid_id').text()
	};

	//Overwrite the import button to only open the modal if it's not disabled
	$('.import').click(function(e){
		e.preventDefault();
		if(!$(this).hasClass('disabled')){
			$('#myModal').modal();
		}
		return false;
	});

	// Refresh functions refreshes the works, populates the imported_ids
	$scope.refresh = function (clear){
		if (clear) {
			$scope.works = false;
		}
		works.getWorks($scope.orcid.id).then(function(data){
			$scope.works = data;
		});
	};

	$scope.syncRefresh = function() {
		works.sync($scope.orcid.id).then(function(data) {
			$scope.refresh(true);
		})
	};

	//run once
	$scope.refresh();

	/**
	 * Watch Expression on works and search result to update the import tag
	 */
	$scope.$watch('works', function(oldv, newv){
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
		//$scope.import_stg = 'ready';
	};

    /**
	 * If an id is already imported
	 *
     * @param id
     * @returns {boolean}
     */
	$scope.alreadyImported = function(id) {
		if (!$scope.works) {
			return false;
		}
		var importedIDs = $scope.works.filter(function(item) {
			return item.in_orcid;
		}).map(function(item) {
			return item.registry_object_id
		});
		return importedIDs.indexOf(id) >= 0;
	};

	/**
	 * Generic SOLR search for collections
	 */
	$scope.search = function() {
		if ($scope.filters.q === '') {
			return;
		}

		$scope.filters.rows = 100;
		$scope.filters.class = 'collection';
		works.search($scope.filters).then(function(data){
			$scope.search_results = data.result;
		});
	};

	/**
	 * Import the set of to_import works to ORCID, calling the import_works factory method
	 * Increment import stages from idle->importing->complete, error is a stage
	 */
    $scope.importResult = null;
    $scope.importedResultCount = 0;
    $scope.failedResultCount = 0;
	$scope.import = function() {
		$scope.import_stg = 'importing';
		$scope.importResult = null;
		var ids = $scope.to_import.map(function(item) {
			return item.id;
		});
		works.importWorks($scope.orcid.id, ids).then(function(data){
			$scope.import_stg = 'complete';
			$scope.importResult = data;

            $scope.importedResultCount = $scope.importResult.filter(function(item) {
                return item.in_orcid;
            }).length;

            $scope.failedResultCount = $scope.importResult.filter(function(item) {
                return !item.in_orcid;
            }).length;

            $scope.refresh();
		});
	};

	$scope.remove = function (item) {

		if (!confirm("Are you sure you want to unlink " + item.title + "?")) {
			return;
		}

		works.remove($scope.orcid.id, item.id).then( function() {
            $scope.works.splice($scope.works.indexOf(item), 1);
		});
	};

	$scope.resetImported = function () {
		$scope.importResult = null;
		$scope.import_stg = "ready";
	}



}