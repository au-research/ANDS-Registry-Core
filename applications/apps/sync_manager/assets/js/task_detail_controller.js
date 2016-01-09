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

        function refresh() {
            APITaskService.getTask(id).then(function(data){
               $scope.task = data.data;
            });
        }

        $scope.dismiss = function () {
            $modalInstance.dismiss();
        }
    }

})();