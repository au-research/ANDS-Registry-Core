(function () {
    'use strict';
    angular.module('analytic_app')
        .service('filterService', filterService)

    function filterService($http, $log) {

        var date = new Date(), y = date.getFullYear(), m = date.getMonth();
        var firstDay = new Date(y, m - 1, 1);
        var lastDay = new Date(y, m, 0);

        var filters = {
            'log': 'portal',
            'period': {'startDate': firstDay, 'endDate': lastDay},
            'dimensions': [
                'portal_view', 'portal_search', 'accessed'
            ],
            'class': ["collection", "party", "service", "activity"]
        }

        var availableFilters = {}

        return {
            getFilters: function () {
                return filters;
            },
            registerAvailableFilters: function(value, index) {
                availableFilters[index] = [];
                angular.copy(value, availableFilters[index]);
                //$log.debug(availableFilters)
            },
            getAvailableFilters: function() {
                return availableFilters;
            },
            toggleSelection: function (value, index) {
                var idx = filters[index].indexOf(value);
                //$log.debug(value, index, filters[index], filters[index].indexOf(value));
                if (idx > -1) {
                    filters[index].splice(idx, 1);
                } else {
                    filters[index].push(value);
                }
                //$log.debug('result', filters[index]);
            },
            setFilter: function (value, index) {
                filters[index] = value;
            }
        }
    }
})();