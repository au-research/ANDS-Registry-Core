(function () {
    'use strict';
    angular
        .module('sync_app')
        .controller('indexCtrl', indexCtrl);

    function indexCtrl($scope, APITaskService, APIDataSourceService) {
        $scope.base_url = base_url;
        APITaskService.getTasksReport().then(function(data){
            console.log(data);
        });
        APIDataSourceService.getDataSources().then(function(data){
            console.log(data);
        });
    }

})();