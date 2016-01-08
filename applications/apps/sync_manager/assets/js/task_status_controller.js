/**
 * File: taskStatusController
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
(function () {
    'use strict';

    angular
        .module('sync_app')
        .controller('taskStatusController', taskStatusController);

    function taskStatusController(status, APITaskService, $scope, $modalInstance) {
        $scope.header = status + " Tasks";

        $scope.runTask = runTask;
        $scope.refresh = refresh;
        $scope.refresh();

        $scope.tasks = [];

        function refresh() {
            APITaskService.getTasksByStatus(status).then(function (data) {
                if (data && data.data && angular.isArray(data.data)) {
                    $scope.tasks = data.data;
                }
            });
        }

        function runTask(task) {
            task.running = true;
            APITaskService.runTask(task.id).then(function (data) {
                task.running = false;
                if (data.code=="200") {
                    task.message = data.data.message;
                } else {
                    $scope.refresh();
                }
            })
        }

        $scope.dismiss = function () {
            $modalInstance.dismiss();
        }
    }

})();