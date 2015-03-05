angular.module('record_components',['profile_components'])

.factory('record_factory', function($http){
	return{
        get_record: function(id) {
            var promise = $http.get(base_url+'registry_object/get/'+id+'/core').then(function(response){
                return response.data;
            });
            return promise;
        },
		stat: function(id) {
			var promise = $http.get(base_url+'registry_object/stat/'+id).then(function(response){
				return response.data;
			});
			return promise;
		},
        add_stat: function(id, type, value) {
            var data = {
                type:type,
                value:value
            };
            var promise = $http.post(base_url+'registry_object/add_stat/'+id, {data:data}).then(function(response){
                return response.data;
            });
            return promise;
        }
	}
})

.controller('moveCtrl', function($scope, $log, $modalInstance, id, profile_factory, record_factory){
    $scope.message = 'test';
    $scope.id = id;

    if (angular.isArray($scope.id)) {
        $scope.records = $scope.id;
        // $log.debug($scope.records);
    } else {
        record_factory.get_record($scope.id).then(function(data){
            $scope.record = data;
        });
    }

    profile_factory.check_is_bookmarked($scope.id).then(function(data){
       if (data.status=='OK') {
          $scope.bookmarked = true;
       } else $scope.bookmarked = false;
    });

    $scope.fetch = function(){
        $scope.folders = {};
        profile_factory.get_user().then(function(data){
            $scope.user = data;
            $scope.folders = profile_factory.get_user_folders($scope.user);
        });
    }

    $scope.moveToFolder = function(folder) {
        // $log.debug(folder);
        if ($scope.record) {
            var records = [];
            records.push({
                id:$scope.record.core.id,
                slug:$scope.record.core.slug,
                group:$scope.record.core.group,
                title:$scope.record.core.title,
                folder:folder,
                last_viewed:parseInt(new Date().getTime() / 1000)
            });
        } else if($scope.records) {
            var records = $scope.records;
            angular.forEach($scope.records, function(record){
                record.selected = false;
                record.folder = folder;
                record.last_viewed = parseInt(new Date().getTime() / 1000);
            });
        }
        // $log.debug(folder);
        if(records) {
            var action = 'modify';
            if (!$scope.bookmarked) action = 'add';
            profile_factory.modify_user_data('saved_record', action, records).then(function(data){
                if(data.status=='OK') {
                    $scope.fetch();
                } else {
                    $log.debug(data);
                }
            });
        }
    }

    $scope.moveRecordsToFolder = function(records, folder) {
        angular.forEach(records, function(record){
            record.selected = false;
            record.folder = folder;
            record.last_viewed = parseInt(new Date().getTime() / 1000);
        });
        profile_factory.modify_user_data('saved_record', 'modify', records).then(function(data){
            if(data.status=='OK') {
                $scope.fetch();
            } else {
                $log.debug(data);
            }
        });
    }

    $scope.unBookmark = function(id) {
        var records = [];
        records.push({id:id});
        profile_factory.modify_user_data('saved_record', 'delete', records).then(function(data){
            if(data.status=='OK') {
                $modalInstance.close();
            } else {
                // $log.debug(data);
            }
        });
    }

    $scope.inFolder = function(id, folder) {
        var ret = false;
        if($scope.user) {
            angular.forEach($scope.user.user_data.saved_record, function(rec) {
                if (rec.id==id && folder==rec.folder) {
                    ret = true;
                }
            });
        }
        return ret;
    }

    $scope.inRecordsFolder = function(records, folder) {
        var ret = true;
        angular.forEach(records, function(r){
            if(r.folder!=folder) ret = false;
        });
        return ret;
    }

    $scope.dismiss = function(){
        $modalInstance.dismiss();
    }

    $scope.fetch();

})


.controller('exportCtrl', function($scope, $log, $modalInstance, id, record_factory){
    $scope.id = id;

    if (angular.isArray($scope.id)) {
        $scope.records = $scope.id;
        // $log.debug($scope.records);
    } else {
        record_factory.get_record($scope.id).then(function(data){
            $scope.record = data;
        });
    }

    $scope.export = function(type) {

        var id = 0;
        var link = '';

        if ($scope.record) {
            id = $scope.record.id;
        } else if($scope.records) {
            var ids = [];
            angular.forEach($scope.records, function(record){
                ids.push(record.id);
            });
            id = ids.join('-');
        }

        if (type=='endnote') {
            link = registry_url+'registry_object/exportToEndnote/'+id+'.ris?foo='+Math.floor(Date.now() / 1000);
        } else if(type=='endnote_web') {
            link = 'http://www.myendnoteweb.com/?func=directExport&partnerName=ResearchDataAustralia&dataIdentifier=1&dataRequestUrl='+registry_url+'registry_object/exportToEndnote/'+id+'.ris?foo='+Math.floor(Date.now() / 1000);
        }

        return link;
        
    }

    $scope.dismiss = function(){
        $modalInstance.dismiss();
    }

})

.controller('saveSearchCtrl', function($scope, $log, $modalInstance, saved_search_data, profile_factory){
    $scope.data = saved_search_data;
    $scope.saveSearch = function(){
        ngdata = [];
        ngdata.push($scope.data);
        profile_factory.add_user_data('saved_search', ngdata).then(function(data){
            if(data.status=='OK') {
                $modalInstance.close();
            } else {
                $log.debug(data);
            }
        });
    }

    $scope.dismiss = function(){
        $modalInstance.dismiss();
    }
})

;