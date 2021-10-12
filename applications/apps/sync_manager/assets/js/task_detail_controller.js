/**
 * File: taskDetailController
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
(function () {
    'use strict';
    angular
        .module('sync_app')
        .controller('taskDetailController', taskDetailController);

    function taskDetailController(id, APITaskService, $scope, $modalInstance) {

        $scope.header = 'Viewing Task: ' + id;

        $scope.refresh = refresh;
        $scope.refresh();

        $scope.taskOperation = taskOperation;

        function refresh() {
            APITaskService.getTask(id).then(function(data){
               $scope.task = data.data;
            });
        }

        function taskOperation(op, task) {
            task.running = true;
            if (op=='run') {
                APITaskService.runTask(task.id).then(function(data){
                    $scope.task = data.data;
                });
            } else if (op=='delete') {
                APITaskService.deleteTask(task.id).then(function(data){
                    $scope.task = data.data;
                });
            } else if (op=='clearMessage') {
                APITaskService.clearTaskMessage(task.id).then(function(data){
                    $scope.task = data.data;
                });
            } else if (op=='reschedule') {
                APITaskService.rescheduleTask(task.id).then(function(data){
                    $scope.task = data.data;
                });
            }
        }

        $scope.dismiss = function () {
            $modalInstance.dismiss();
        }
    }

})();