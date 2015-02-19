app.controller('tagController', function($scope, $log, $http){

	$scope.key = $('#ro_key').val();
	$scope.values = ['Value 1', 'value 2'];
	$scope.addTag = function(){
		var data = {
			'key':$scope.key,
			'tag':$scope.newTag
		}
		$http.post(base_url+'registry_object/addTag/', {'data':data}).then(function(response){
			if(response.data.status=='OK') {
				location.reload();
			}
		});
	}

	$scope.getSuggestTag = function(val) {
		return $http.get(registry_url+'services/rda/getTagSuggestion/false/?q='+val).then(function(response){
			return response.data;
		});
	}

});