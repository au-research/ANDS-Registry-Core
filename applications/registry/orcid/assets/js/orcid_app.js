angular.module('orcid_app', ['ngSanitize'])
	.config(function($routeProvider){
		$routeProvider
			.when('/', {
				controller:IndexCtrl,
				template: $('#index').html()
			})
	})
	.factory('works', function($http){
		return {
			getWorks: function(data) {
				return $http.post(base_url+'/orcid/orcid_works', {data:data}).then(function(response) {return response.data});
			},
			search: function(filters) {
				return $http.post(base_url+'/services/registry/post_solr_search', {filters:filters}).then(function(response) {return response.data});
			}
		}
	})
;

function IndexCtrl($scope, works) {
	$scope.works = {};
	$scope.to_import = [];
	$scope.import_suggested = false;
	$scope.import_searched = false;
	$scope.filters = {};
	$scope.search_results = {};

	$scope.orcid = {
		orcid_id:$('#orcid_id').text(),
		first_name:$('#first_name').text(),
		last_name:$('#last_name').text()
	};

	works.getWorks($scope.orcid).then(function(data){
		$scope.works = data.works;
	});

	$scope.$watch('works', function(){
		$scope.import_suggested = false;
		$.each($scope.works, function(){
			if(this.to_import) {
				console.log(this);
				$scope.import_suggested = true;
			}
		});
	}, true);

	$scope.search = function() {
		if($scope.filters.q!=''){
			console.log($scope.filters);
			works.search($scope.filters).then(function(data){
				$scope.search_results = data.result;
			});
		}
	}
}