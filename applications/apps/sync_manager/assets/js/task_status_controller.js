/**
 * File: taskStatusController
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
(function () {
    'use strict';

    angular
        .module('sync_app')
        .controller('taskStatusController', taskStatusController);

    function taskStatusController(status, APITaskService, $scope, $modalInstance, $modal) {
        $scope.header = status + " Tasks";

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

        $scope.$on('showTask', function(event, data){
            return $modal.open({
                templateUrl: apps_url + 'assets/sync_manager/templates/taskDetail.html',
                controller: 'taskDetailController',
                resolve: {
                    id: function() {
                        return data.id;
                    }
                }
            });
        });

        $scope.dismiss = function () {
            $modalInstance.dismiss();
        }
    }

})();