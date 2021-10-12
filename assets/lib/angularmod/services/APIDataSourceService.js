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

        var resource = 'registry/datasources/';

        return {
            getDataSources: function() {
                return APIService.get(resource, {
                    with: 'counts'
                });
            },
            syncDataSource: function(id) {
                return APIService.get(resource + id +'/sync', {})
            },
            refreshDataSourcesCount: function () {
                return APIService.get(resource, {'action': 'recount'})
            },
            getDataSource: function(id) {
                return APIService.get(resource + id, {})
            }
        }

    }
})();