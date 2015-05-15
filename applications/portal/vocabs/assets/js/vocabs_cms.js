app.controller('addVocabsCtrl', function($scope){
	
	$scope.vocab = {};

	$scope.vocab = {
		title: 'test',
		description:'some description',
		versions: [
			{title:'version 1', status:'current'}
		]
	}

});