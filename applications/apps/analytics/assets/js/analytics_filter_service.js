(function(){
    'use strict';
    angular.module('analytic_app')
        .service('filterService', filterService)

    function filterService ($http, $log) {
		
		
		var date = new Date(), y = date.getFullYear(), m = date.getMonth();
		var firstDay = new Date(y, m-1, 1);
		var lastDay = new Date(y, m , 0);

        var filters = {
            'log': 'portal',
            'period': {'startDate': firstDay, 'endDate': lastDay},
            'dimensions': [
                'portal_view', 'portal_search', 'accessed'
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