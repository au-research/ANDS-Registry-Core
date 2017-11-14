(function () {
    'use strict';
    angular
        .module('sync_app')
        .controller('indexCtrl', indexCtrl);


    function indexCtrl(
        APITaskService, APIDataSourceService, APIRegistryObjectService,
        $interval, $scope, $modal
    ) {

        $scope.base_url = base_url;

        // default options
        $scope.options = {
            'autorefresh': false
        };

        // function declaration
        $scope.refreshTasks = refreshTasks;
        $scope.refreshDataSources = refreshDataSources;
        $scope.addTask = addTask;
        $scope.showTaskStatus = showTaskStatus;
        $scope.syncRo = syncRo;
        $scope.showTask = showTask;

        //clearing the index of a DS
        $scope.clearIndex = clearIndex;

        // function declaration for options
        $scope.toggleOption = toggleOption;
        $scope.getOption = getOption;

        //allow child scope to ask this controller to show a task in a modal
        $scope.$on('showTask', function (event, data) {
            $scope.showTask(data.id);
        });

        //init intervals
        $scope.refreshTasks();
        $scope.refreshDataSources();

        var refreshTasksInterval, refreshDataSourcesInterval;
        $scope.$watch('options', function () {
            if ($scope.getOption('autorefresh') === false) {
                $interval.cancel(refreshTasksInterval);
                $interval.cancel(refreshDataSourcesInterval);
            } else {
                refreshTasksInterval = $interval(refreshTasks, 5000);
                refreshDataSourcesInterval = $interval(refreshDataSources, 60000);
            }
        }, true);

        //destroy the interval upon unloading of this controller
        $scope.$on('destroy', function () {
            $interval.cancel(refreshTasksInterval);
            $interval.cancel(refreshDataSourcesInterval);
        });

        /**
         * Sync a Registry Object based on ID
         * @param subject
         */
        function syncRo(subject) {
            if (!subject || $scope.syncing) {
                return;
            }

            $scope.syncing = true;

            APIRegistryObjectService.syncRecord(subject)
                .then(function(data){
                    $scope.syncing = false;
                    if (data.data.status === "ERROR") {
                        alert(data.data.data); // wow?
                        return;
                    }
                    $scope.showTask(data.id);
                });
        }

        $scope.syncDS = function(id) {
            if (!id) {
                return;
            }

            if (!confirm("Are you sure you want to sync this data source?")) {
                return;
            }

            APIDataSourceService.syncDataSource(id)
                .then(function(data) {
                    if (data.data.status === "ERROR") {
                        alert(data.data.data); // wow?
                        return;
                    }
                    $scope.showTask(data.id);
                });
        }

        function clearIndex(id) {
            if (id) {
                addTask('clear_index', 'ds', id, true).then(function (data) {
                    var task = data;
                    if (task.id) {
                        APITaskService.runTask(task.id).then(function (data) {
                            var task = data.data;
                            $scope.refreshTasks();
                            $scope.refreshDataSources();
                            $scope.showTask(task.id);
                        });
                    }
                });
            }
        }

        /**
         * Show a Task based on ID in a modal
         * @param id
         */
        function showTask(id) {
            return $modal.open({
                templateUrl: apps_url + 'assets/sync_manager/templates/taskDetail.html',
                controller: 'taskDetailController',
                resolve: {
                    id: function () {
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
                params: {}
            };
            switch (name) {
                case 'sync':
                    params.name = "Sync ";
                    break;
                case 'index':
                    params.name = "Index ";
                    params.params.includes = "indexPortal,indexRelations"
                    break;
                case 'indexPortal':
                    params.name = "Index Portal ";
                    params.params.includes = "indexPortal"
                    break;
                case 'indexRelations':
                    params.name = "Index Relations ";
                    params.params.includes = "indexRelations"
                    break;
                case 'addRelationships':
                    params.name = "Fix Relationships and Index ";
                    params.params.includes = "addRelationships,indexPortal,indexRelations,fixRelationship"
                    break;
                case 'index_missing':
                    params.name = "Index Missing ";
                    params.params.missingOnly = true;
                    params.params.indexOnly =  true;
                    break;
                case 'sync_missing':
                    params.name = "Sync Missing ";
                    params.params.missingOnly = true;
                    break;
                case 'clear_index':
                    params.name = "Clear Index ";
                    params.params.clearIndex = true;
                    break;
            }
            if (params.type == 'ds') {
                params.name += "Data Source " + params.id;
            } else if(params.type == 'all') {
                params.name += "Everything ";
            }
            params.params['class'] = 'sync' ;

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
                $scope.datasources = data;
                angular.forEach($scope.datasources, function (ds) {
                    ds.count_PUBLISHED = parseInt(ds.counts.count_PUBLISHED);
                    ds.count_INDEXED = parseInt(ds.counts.count_INDEXED);
                    ds.count_MISSING = parseInt(ds.counts.count_MISSING);
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

        /**
         * Show a list of tasks grouped by a status in a modal
         * @param status
         */
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

        /**
         * Returns an option
         * @param option
         * @returns {*}
         */
        function getOption(option) {
            return $scope.options[option];
        }

        /**
         * Toggle an option, true|false value
         * @param option
         */
        function toggleOption(option) {
            $scope.setOption(option, !$scope.getOption(option));
        }

        /**
         * Sets an option
         * @param option
         * @param value
         */
        function setOption(option, value) {
            $scope.options[option] = value
        }
    }

})();