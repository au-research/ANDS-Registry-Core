/**
 * File: taskList AngularJS directive
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
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
            scope.runTask = runTask;

            scope.showTask = function(task){
                scope.$emit('showTask', {id:task.id});
            };
        }

        function runTask(task) {
            task.running = true;
            APITaskService.runTask(task.id).then(function (data) {
                task.running = false;
                if (data.code=="200") {
                    tasks.status = data.data.status;
                    tasks.last_run = data.data.last_run;
                    task.message = data.data.message;
                } else {
                    $scope.refresh();
                }
            })
        }

    }

})();