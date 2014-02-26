angular.module('connections',[]).
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
// directive('connectiontip', function($compile, $templateCache, $rootScope){
// 	return {
// 		restrict: 'A',
// 		link: function(scope, el, attr){
// 			var clone = $compile($templateCache.get('connections_layout_template'))(scope);
// 			$(el).qtip({
// 				content: {
// 					text: function() {
// 						return scope.$apply(function () {
// 							return clone;
// 						});
// 					}
// 				},
// 				position: {viewport: $(window),my: 'right center',at: 'left center'},
// 				show: {
// 					event: 'click',
// 					ready: false,
// 					solo: true
// 				},
// 				hide: {
// 					fixed:true,
// 					event:'unfocus',
// 				},
// 				events: {
// 					render : function(){
// 						console.log(scope);
// 						$rootScope.$apply();
// 					}
// 				},
// 				style: {classes: 'ui-tooltip-light ui-tooltip-shadow previewPopup', width: 850} ,
// 				overwrite: true
// 			});
// 		}
// 	}
// }).
controller('openConnections', function($scope, searches){

	$scope.results = {};
	$scope.filters = {};
	$scope.facet = {};
	$scope.query = '';

	$scope.$watch('query', function(){
		$scope.filters['q'] = $scope.query;
		$scope.search();
	});

	$scope.open = function($event){

		var c = $('#connections_layout_container');

		$('.ui-widget-overlay').live('click', function() {
			c.dialog( "close" );
		});

		c.dialog({
			width:900,
			modal:true
		});

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
		$scope.search();
	}

	$scope.select = function(type, value){
		$scope.filters[type] = value;
		$scope.search();
	}

	$scope.search = function(){
		var ro_id = $('#registry_object_id').text();
		$scope.filters['related_object_id'] = ro_id;
		searches.search($scope.filters).then(function(data){
			$scope.results = data.result;
			$scope.facet = data.facet_result;
		});
	}

});