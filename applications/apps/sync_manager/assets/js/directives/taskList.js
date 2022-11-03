/**
 * File: taskList AngularJS directive
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
(function () {
    'use strict';

    angular
        .module('sync_app')
        .directive('taskList', taskList);

    function taskList(APITaskService) {
        return {
            link: link,
            templateUrl: apps_url + 'assets/sync_manager/js/directives/taskList.html',
            restrict: 'EA',
            scope: {
                tasks: '=',
                status: '='
            }
        };

        function link(scope, element, attrs) {
            scope.taskOperation = taskOperation;
            scope.showTask = function(task){
                scope.$emit('showTask', {id:task.id});
            };
        }

        function taskOperation(op, task) {
            task.running = true;
            if (op=='run') {
                APITaskService.runTask(task.id).then(function(data){
                    refreshTask(task, data);
                });
            } else if (op=='delete') {
                APITaskService.deleteTask(task.id).then(function(data){
                    refreshTask(task, data);
                });
            } else if (op=='clearMessage') {
                APITaskService.clearTaskMessage(task.id).then(function(data){
                    refreshTask(task, data);
                });
            } else if (op=='reschedule') {
                APITaskService.rescheduleTask(task.id).then(function(data){
                    refreshTask(task, data);
                });
            }
        }

        function refreshTask(task, data) {
            task.running = false;
            if (data.code=="200") {
                task.status = data.data.status;
                task.last_run = data.data.last_run;
                task.message = data.data.message;
            }
        }

    }

})();