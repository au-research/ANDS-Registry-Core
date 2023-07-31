app.controller('viewController', function($scope, $log, $modal, profile_factory, record_factory){

	$scope.ro = {
		'id': $('#ro_id').val(),
		'slug': $('#ro_slug').val(),
		'title': $('#ro_title').val(),
        'group': $('#ro_group').val()
	};

	// Add a record_view event to Google Analytics GA4, with more details than previous page_view could capture
    if (typeof gtag === 'function') {
        gtag('event', 'record_view', {'page_location':location.href,
            'record_id':$scope.ro.id,
            'record_title':$scope.ro.title,
            'slug':$scope.ro.slug,
            'group':$scope.ro.group,
        });
    }

	//get stat
	record_factory.stat($scope.ro.id).then(function(data){
	    if(data[0].id); $scope.ro.stat = data[0];
	});

    $scope.access = function(event) {
        if (typeof gtag === 'function') {
            gtag('event', 'data_access', {'link_url':event.target.href, 'link_text':'Go to Data Provider'});
        }
        record_factory.add_stat($scope.ro.id, 'accessed', 1, event.target.href).then(function(data){
            location.href = event.target.href;
        });
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


    /**
     * Opening the citation modal
     * Bootstrap v3 modal
     * @todo move to own controller
     */
	$scope.openCitationModal = function(){
		$('#citationModal').modal();
	}

    $scope.openAccessPolicyModal = function() {
        $('#accessPolicyModal').modal();
    }

});