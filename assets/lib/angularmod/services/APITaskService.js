/**
 * File:  APITaskService
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
(function () {
    'use strict';

    angular
        .module('APITask', ['APIService'])
        .service('APITaskService', APITaskService);

    function APITaskService(APIService) {
        return {
            getTasksReport: function() {
                return APIService.get(
                    'task', {}
                );
            }
        }

    }

})();