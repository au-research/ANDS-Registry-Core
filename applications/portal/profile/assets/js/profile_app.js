

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

app.controller('dashboardCtrl', function($scope, $rootScope, $log, profile_factory, search_factory){
	$scope.hello = 'Hello World';
	$scope.base_url = base_url;


    $scope.saved_search_count = 0;

    $scope.fetch = function(){
        profile_factory.get_user().then(function(data){
            $scope.user = data;

            $scope.folders = {};
            $scope.folders['all'] = 0;
            if($scope.user.user_data.saved_search){
                $scope.saved_search_count = Object.keys($scope.user.user_data.saved_search).length;
            }
            if($scope.user.user_data && $scope.user.user_data.saved_record)
            {
                $scope.folders['all'] = Object.keys($scope.user.user_data.saved_record).length;

                angular.forEach($scope.user.user_data.saved_record, function(record){
                    if (record.folder){
                        if($scope.folders[record.folder] == undefined) {
                            $scope.folders[record.folder] = 1;
                        }
                        else{
                            $scope.folders[record.folder] = $scope.folders[record.folder] + 1;
                        }
                    }
                });


            }
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

    $scope.updateSorted = function(item){
        $scope.folderf = item;
    }

    $scope.refreshQueries = function(id){
        angular.forEach($scope.user.user_data.saved_search, function(record){
            if(id == 'group' || record.query_string == id)
            {
                var last_ran = record.refresh_time;
                var saved_time = record.saved_time;
                var query_string = record.query_string;
                var num_found_since_last_check = 0;
                var num_fund_since_saved = 0;
                var query_num_found_since_last_check = query_string + 'after_date=' + last_ran + '/';
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

    

    $scope.modify_user_data = function(type, action, id) {
        var records = [];
        if(action=='refresh') {
            $scope.refreshQueries(id);
        }
        else{
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