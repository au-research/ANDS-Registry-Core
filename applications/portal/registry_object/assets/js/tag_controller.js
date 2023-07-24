app.controller('tagController', function($scope, $log, $http){

	$scope.key = $('#ro_key').val();
    $scope.ro_id = $('#ro_id').val();
	$scope.values = ['Value 1', 'value 2'];
	$scope.addTag = function(){
		var data = {
			'key':$scope.key,
            'id':$scope.ro_id,
			'tag':$scope.newTag
		}
		$http.post(base_url+'registry_object/addTag/', {'data':data}).then(function(response){

			if(response.data.status=='OK') {
				location.reload();
			}
            if(response.data.status=='ERROR' && response.data.message!='') {
                $('#tag_error').html(response.data.message);
            }
		});
	}

	$scope.getSuggestTag = function(val) {
		return $http.get(registry_url+'services/rda/getTagSuggestion/false/?q='+val).then(function(response){
			return response.data;
		});
	}

});