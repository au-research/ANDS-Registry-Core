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
			},
			import_works: function(ro_ids) {
				return $http.post(base_url+'/orcid/import_to_orcid', {ro_ids:ro_ids}).then(function(response) {return response.data});
			}
		}
	})
;

function IndexCtrl($scope, works) {
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

	$('.import').click(function(e){
		e.preventDefault();
		if(!$(this).hasClass('disabled')){
			$('#myModal').modal();
		}
		return false;
	});

	$scope.refresh = function(){
		works.getWorks($scope.orcid).then(function(data){
			$scope.works = data.works;
			$.each($scope.works, function(){
				if(this.type=='imported'){
					$scope.imported_ids.push(this.id);
				}
			})
		});
	}
	$scope.refresh();

	$scope.$watch('works', function(){
		$scope.review();
	}, true);

	$scope.$watch('search_results', function() {
		$scope.review();
	}, true);

	$scope.review = function(){
		$scope.import_available = false;
		$scope.to_import = [];
		$.each($scope.works, function(){
			if(this.to_import) {
				$scope.to_import.push(this);
				$scope.import_available = true;
			}
		});
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

	$scope.search = function() {
		if($scope.filters.q!=''){
			$scope.filters.rows = 100;
			$scope.filters.class = 'collection';
			works.search($scope.filters).then(function(data){
				$scope.search_results = data.result;
			});
		}
	}

	$scope.already_imported = function(item) {
		$.each($scope.works, function(){
			if(this.type=="imported" && this.id===item.id){
				return true;
			}
		});
		return false;
	}

	$scope.import = function() {
		$scope.import_stg = 'importing';
		var ids = [];
		$.each($scope.to_import, function(){
			ids.push(this.id);
		});
		works.import_works(ids).then(function(data){
			if(data!=1){
				console.err(data);
				$scope.import_stg = 'error';
			} else {
				$scope.import_stg = 'complete';
				$scope.refresh();
			}
		});
	}
}