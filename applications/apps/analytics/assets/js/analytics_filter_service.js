(function(){
    'use strict';
    angular.module('analytic_app')
        .service('filterService', filterService)

    function filterService ($http, $log) {
        var filters = {
            'log': 'portal',
            'period': {'startDate': '2015-06-01', 'endDate': '2015-06-06'},
            'dimensions': [
                'portal_view', 'portal_search'
            ]
        }

        var getFilters = function() {
            return filters;
        }

        return {
            getFilters: getFilters
        }
    }
})();