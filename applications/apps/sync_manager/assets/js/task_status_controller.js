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

        $scope.refresh = refresh;
        $scope.refresh();

        $scope.tasks = [];
        function refresh() {
            APITaskService.getTasksByStatus(status).then(function (data) {
                $scope.tasks.push(data.data);
            });
        }

        $scope.dismiss = function() {
            $modalInstance.dismiss();
        }
    }

})();