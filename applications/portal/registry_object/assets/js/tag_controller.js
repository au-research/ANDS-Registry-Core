app.controller('tagController', function($scope, $log, $http){

	$scope.key = $('#ro_key').val();
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

});