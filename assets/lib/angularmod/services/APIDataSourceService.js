/**
 * File:  APIDataSourceService
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
(function () {
    'use strict';

    angular
        .module('APIDataSource', ['APIService'])
        .service('APIDataSourceService', APIDataSourceService);

    function APIDataSourceService(APIService) {
        return {
            getDataSources: function() {
                return APIService.get('registry/ds/', {
                    with: 'counts'
                });
            },
            syncDataSource: function(id) {
                return APIService.get('registry/ds/'+id+'/sync', {})
            },
            getDataSource: function(id) {
                return APIService.get('registry/ds/'+id, {})
            }
        }

    }
})();