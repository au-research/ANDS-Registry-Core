

app.config(['$routeProvider', '$locationProvider',
  function($routeProvider, $locationProvider) {
    $routeProvider.
      when('/dashboard', {
        templateUrl: base_url+'assets/profile/templates/dashboard.html',
        controller: 'dashboardCtrl'
      }).
      otherwise({
        redirectTo: '/dashboard'
    });
}]);

app.controller('dashboardCtrl', function($scope, $rootScope, $log, profile_factory, search_factory, $modal){
	$scope.hello = 'Hello World';
	$scope.base_url = base_url;
    $scope.sort_order = 'title';
    $scope.sort_title = 'Record Title';
    $scope.ascending = false;
    $scope.saved_search_count = 0;

    $scope.fetch = function(){
        profile_factory.get_user().then(function(data){
            $scope.user = data;
            $scope.folders = profile_factory.get_user_folders($scope.user);
        });
    }
    $scope.fetch();


	$scope.action = 'action';
	$scope.available_actions = profile_factory.get_user_available_actions();
	// $log.debug($scope.available_actions);

    $scope.folderf = 'all';
    $scope.folderfilter = function(item){
        if($scope.folderf==item.folder || $scope.folderf=='all') {
            return true;
        } else return false;
    }

    $scope.folderCount = function(f) {
        var ret = 0;
        if($scope.folders) {
            angular.forEach($scope.folders, function(count, folder){
                if(folder==f) ret = count;
            });
        }
        return ret;
    }

    $scope.updateSorted = function(item){
        $scope.folderf = item;
    }

    $scope.refreshQueries = function(id){
       $log.debug($scope.user.user_data.saved_search);
        angular.forEach($scope.user.user_data.saved_search, function(record){
            if(id == 'group' || record.query_string == id)
            {

                var last_ran = record.saved_time;
                if(record.last_ran)
                    last_ran = record.last_ran;
                var last_refresh = record.refresh_time;
                var saved_time = record.saved_time;
                var query_string = record.query_string;
                var num_found_since_last_check = 0;
                var num_fund_since_saved = 0;
                var query_num_found_since_last_check = query_string + 'after_date=' + last_refresh + '/';
                var query_num_found_since_saved = query_string + 'after_date=' + saved_time + '/';
                var filters = search_factory.filters_from_hash(query_num_found_since_last_check);
                var search_results = search_factory.search(filters).then(function(data){
                    num_found_since_last_check = data.response.numFound;
                    filters = search_factory.filters_from_hash(query_num_found_since_saved);
                    search_results = search_factory.search(filters).then(function(data){
                        num_fund_since_saved = data.response.numFound;
                        var records = [];
                        records.push({id:query_string,
                            num_found_since_last_check:num_found_since_last_check,
                            num_found_since_saved:num_fund_since_saved,
                            last_ran:last_refresh,
                            refresh_time:parseInt(new Date().getTime() / 1000)
                        });
                        profile_factory.modify_user_data('saved_search', 'refresh', records).then(function(data){
                            if(data.status=='OK') {
                                $scope.fetch();
                            } else {
                                $log.debug(data);
                            }
                        });
                    });
                });
           }
        });
    }

    $scope.selected = function(type) {
        var i = 0;
        angular.forEach($scope.user.user_data[type], function(t){
            if (t.selected) i++;
        });
        return i;
    }

    $scope.toggleSelection = function(type) {
        // $log.debug($scope.selected(type), $scope.folderf);
        if ($scope.selected(type) == 0) {
            //select all in folder
            angular.forEach($scope.user.user_data[type], function(f){
                if (type=='saved_record') {
                    if($scope.folderf!='all') {
                        if(f.folder == $scope.folderf) f.selected = true;
                    } else {
                        f.selected = true;
                    }
                } else if(type=='saved_search') {
                    f.selected = true;
                }
            });
        }  else {
            //deselect all
            angular.forEach($scope.user.user_data[type], function(f){
                f.selected = false;
            });
        }
    }

    $scope.isSocial = function(user) {
        var social_auth_methods = ['AUTHENTICATION_SOCIAL_FACEBOOK', 'AUTHENTICATION_SOCIAL_TWITTER', 'AUTHENTICATION_SOCIAL_GOOGLE', 'AUTHENTICATION_SOCIAL_LINKEDIN'];
        if (social_auth_methods.indexOf(user.authMethod) >-1) {
            return true;
        } else {
            return false;
        }
    }

    $scope.openMoveModal = function(id) {
        var modalInstance = $modal.open({
            templateUrl: base_url+'assets/registry_object/templates/moveModal.html',
            controller: 'moveCtrl',
            windowClass: 'modal-center',
            resolve: {
                id: function () {
                    return id;
                }
            }
        });
        modalInstance.result.then(function(){
            //close
            $scope.fetch();
        }, function(){
            //dismiss
            $scope.fetch();
        });
    }

    $scope.openExportModal = function(id) {
        var modalInstance = $modal.open({
            templateUrl: base_url+'assets/registry_object/templates/exportModal.html',
            controller: 'exportCtrl',
            windowClass: 'modal-center',
            resolve: {
                id: function () {
                    return id;
                }
            }
        });
        modalInstance.result.then(function(){
            //close
            $scope.fetch();
        }, function(){
            //dismiss
            $scope.fetch();
        });
    }


    $scope.sort_table = function(table, sort_order, sort_title){
        $scope.sort_order = sort_order;
        $scope.sort_title = sort_title;
        $scope.ascending = !$scope.ascending;
    }

    $scope.modify_user_data = function(type, action, id) {
        var records = [];
        if(action=='refresh') {
            $scope.refreshQueries(id);
        } else if (action=='move') {
            if(id!='group') {
                $scope.openMoveModal(id);
            } else {
                var ids = [];
                angular.forEach($scope.user.user_data[type], function(t){
                    if(t.selected) ids.push(t);
                });
                $scope.openMoveModal(ids);
            }
        } else if(action=='delete'){
            var records = [];
            if (id!='group') {
                records.push({id:id});
            } else {
                angular.forEach($scope.user.user_data[type], function(t){
                    if (t.selected) records.push({id:t.id});
                });
            }
            if (confirm('Are you sure you want to delete '+records.length+' records from MyRDA?')) {
                profile_factory.modify_user_data(type, action, records).then(function(data){
                    if(data.status=='OK') {
                        $scope.fetch();
                    } else {
                        $log.debug(data);
                    }
                });
            }
            
        } else if(action=='export'){
            if(id!='group') {
                $scope.openExportModal(id);
            } else {
                var ids = [];
                angular.forEach($scope.user.user_data[type], function(t){
                    if(t.selected) ids.push(t);
                });
                $scope.openExportModal(ids);
            }
        } else {
            records.push({id:id});
            profile_factory.modify_user_data(type, action, records).then(function(data){
                if(data.status=='OK') {
                    $scope.fetch();
                } else {
                    $log.debug(data);
                }
            });
        }
    }
});