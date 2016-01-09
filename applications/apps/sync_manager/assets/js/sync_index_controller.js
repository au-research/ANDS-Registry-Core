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
        $scope.syncRo = syncRo;
        $scope.showTask = showTask;

        $scope.$on('showTask', function(event, data){
            $scope.showTask(data.id);
        });

        //init
        //$scope.refreshTasks();
        //$scope.refreshDataSources();

        //$interval(refreshTasks, 5000);
        //$interval(refreshDataSources, 60000);

        function syncRo(subject) {
            if (subject && !$scope.syncing) {
                $scope.syncing = true;
                addTask('sync', 'ro', subject, true).then(function (data) {
                    var task = data;
                    if (task.id) {
                        APITaskService.runTask(task.id).then(function (data) {
                            $scope.syncing = false;
                            var task = data.data;
                            console.log(task);
                            $scope.refreshTasks();
                            $scope.showTask(task.id);
                        });
                    }
                });
            }
        }

        function showTask(id) {
            return $modal.open({
                templateUrl: apps_url + 'assets/sync_manager/templates/taskDetail.html',
                controller: 'taskDetailController',
                resolve: {
                    id: function() {
                        return id;
                    }
                }
            });
        }


        /**
         * Adding a task
         * @param name
         * @param type
         * @param id
         * @param showTask
         */
        function addTask(name, type, id, showTask) {
            var params = {
                name: name,
                type: type,
                id: id,
                params: []
            };
            switch (name) {
                case 'index':
                    params.name = 'sync';
                    params.params.push({indexOnly: true});
                    break;
            }
            return APITaskService.addTask(params).then(function (data) {
                $scope.refreshTasks();
                if (showTask) {
                    return data.data;
                }
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
                $scope.tasksStatus = data.data;
            });
            APITaskService.getTasksByStatus('all').then(function (data) {
                $scope.tasks = data.data;
            })
        }

        function showTaskStatus(status) {
            return $modal.open({
                templateUrl: apps_url + 'assets/sync_manager/templates/task_status.html',
                controller: 'taskStatusController',
                resolve: {
                    status: function () {
                        return status;
                    }
                }
            });
        }
    }

})();