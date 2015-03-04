app.controller('viewController', function($scope, $log, $modal, profile_factory, record_factory){

	$scope.ro = {
		'id': $('#ro_id').val(),
		'slug': $('#ro_slug').val(),
		'title': $('#ro_title').val(),
        'group': $('#ro_group').val()
	};

	//get stat
	record_factory.stat($scope.ro.id).then(function(data){
		if (data.id) $scope.ro.stat = data;
	});

    profile_factory.check_is_bookmarked($scope.ro.id).then(function(data){
       if (data.status=='OK') {
          $scope.ro.bookmarked = true;
       } else $scope.ro.bookmarked = false;
    });

	$scope.bookmark = function() {
        var records = [];
        records.push({
                id:$scope.ro.id,
                slug:$scope.ro.slug,
                group:$scope.ro.group,
                title:$scope.ro.title,
                folder:'unsorted',
                last_viewed:parseInt(new Date().getTime() / 1000)
            });
        profile_factory.modify_user_data('saved_record', 'add', records).then(function(data){
            if(data.status=='OK') {
                $scope.ro.bookmarked = true;
            } else {
                $log.debug(data);
            }
        });
	};




    $scope.removeBookmark = function(){
        var records = [];
        records.push({id:$scope.ro.id});
        profile_factory.modify_user_data('saved_record', 'delete', records).then(function(data){
            if(data.status=='OK') {
                $scope.ro.bookmarked = false;
            } else {
                $log.debug(data);
            }
        });
    }

	$scope.openCitationModal = function(){
		$log.debug('open');
		$log.debug($('#citationModal'));
		$('#citationModal').modal();
	}

});