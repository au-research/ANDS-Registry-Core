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

    $scope.access = function(event) {
        record_factory.add_stat($scope.ro.id, 'accessed', 1).then(function(data){
            location.href = event.target.href;
        });
        if (typeof urchin_id !== 'undefined' && typeof ga !== 'undefined' && urchin_id!='') {
            ga('send', 'event', 'Access', 'Go to Data Provider', 'GoToData', 1);
        }
    };

    
    $scope.check = function() {
        profile_factory.check_is_bookmarked($scope.ro.id).then(function(data){
           if (data.status=='OK') {
              $scope.ro.bookmarked = true;
           } else $scope.ro.bookmarked = false;
        });
    }
    $scope.check();


	$scope.bookmark = function() {
        var modalInstance = $modal.open({
            templateUrl: base_url+'assets/registry_object/templates/moveModal.html',
            controller: 'moveCtrl',
            windowClass: 'modal-center',
            resolve: {
                id: function () {
                    return $scope.ro.id;
                }
            }
        });
        modalInstance.result.then(function(){
            //close
            $scope.check();
        }, function(){
            //dismiss
            $scope.check();
        });
	};


	$scope.openCitationModal = function(){
		$log.debug('open');
		$log.debug($('#citationModal'));
		$('#citationModal').modal();
	}

});