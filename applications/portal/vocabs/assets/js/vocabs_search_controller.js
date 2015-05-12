app.controller('searchCtrl', function($scope, $log, vocabs_factory){
	
	$scope.vocabs = [];
	vocabs_factory.getAll().then(function(response){
		// $scope.vocabs = response.message;
		angular.forEach(response.message, function(vocab){
			$scope.vocabs.push(vocab);
		})
	});

});