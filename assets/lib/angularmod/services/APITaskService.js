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
            },
            getTasksByStatus: function(status) {
                return APIService.get(
                    'task/'+status, {}
                );
            },
            addTask: function(params) {
              return APIService.post(
                  'task', params
              );
            },
            runTask: function(id) {
                return APIService.get(
                    'task/exe/'+id, {}
                );
            },
            getTask: function(id) {
                return APIService.get(
                    'task/'+id, {}
                )
            },
            rescheduleTask: function(id) {
                return APIService.get(
                    'task/'+id+'/reschedule', {}
                )
            },
            deleteTask: function(id) {
                return APIService.get(
                    'task/'+id+'/clear', {}
                )
            },
            clearTaskMessage: function(id) {
                return APIService.get(
                    'task/'+id+'/message/clear', {}
                )
            }
        }

    }

})();