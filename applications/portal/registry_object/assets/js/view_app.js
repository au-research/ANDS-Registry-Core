app.controller('viewController', function($scope, profile_factory, record_factory, $log){

	$scope.ro = {
		'id': $('#ro_id').val(),
		'slug': $('#ro_slug').val(),
		'title': $('#ro_title').val(),
	};

	//get stat
	record_factory.stat($scope.ro.id).then(function(data){
		if (data.id) $scope.ro.stat = data;
	});

	$scope.bookmark = function() {
		var records = [];
		records.push($scope.ro);
		$log.debug('bookmarking',records);
		profile_factory.add_user_data('saved_record', records).then(function(data){
			alert('This record is bookmarked');
		});
	};

});