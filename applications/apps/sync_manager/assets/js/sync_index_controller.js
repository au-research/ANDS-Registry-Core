(function () {
    'use strict';
    angular
        .module('sync_app')
        .controller('indexCtrl', indexCtrl);


    function indexCtrl(APITaskService, APIDataSourceService, $interval, $scope, $modal) {

        $scope.base_url = base_url;

        // function declaration
        $scope.refreshTasks = refreshTasks;
        $scope.refreshDataSources = refreshDataSources;
        $scope.addTask = addTask;
        $scope.showTaskStatus = showTaskStatus;

        //init
        $scope.refreshTasks();
        $scope.refreshDataSources();

        //$interval(refreshTasks, 5000);
        //$interval(refreshDataSources, 60000);

        /**
         * Adding a task
         * @param name
         * @param ds
         */
        function addTask(name, ds) {
            var params = {
                name: name,
                type: 'ds',
                id: ds.id,
                params: []
            };
            switch (name) {
                case 'index':
                    params.name = 'sync';
                    params.params.push({indexOnly: true});
                    break;
            }
            APITaskService.addTask(params).then(function (data) {
                console.log(data);
            });
        }

        /**
         * Refreshing the data sources report
         */
        function refreshDataSources() {
            APIDataSourceService.getDataSources().then(function (data) {
                $scope.datasources = data.data;
                angular.forEach($scope.datasources, function (ds) {
                    ds.count_PUBLISHED = parseInt(ds.count_PUBLISHED);
                    ds.count_INDEXED = parseInt(ds.count_INDEXED);
                    ds.count_MISSING = ds.count_PUBLISHED - ds.count_INDEXED;
                });
            });
        }

        /**
         * Refreshing the tasks report
         */
        function refreshTasks() {
            APITaskService.getTasksReport().then(function (data) {
                $scope.tasks = data.data;
            });
        }

        function showTaskStatus(status) {
            return $modal.open({
                templateUrl:apps_url+'assets/sync_manager/templates/task_status.html',
                controller: 'taskStatusController',
                resolve : {
                    status: function() {
                        return status;
                    }
                }
            });
        }
    }

})();