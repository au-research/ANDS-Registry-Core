(function () {
    'use strict';
    angular.module('analytic_app')
        .service('filterService', filterService)

    function filterService($http, $log) {

        var firstDay = new Date();
        firstDay.setDate(firstDay.getDate() + 1);
        var lastDay = new Date();
        lastDay.setMonth(firstDay.getMonth() - 1);

        var filters = {
            'open' : false,
            'log': 'portal',
            'period': {'startDate': lastDay.toISOString().slice(0, 10), 'endDate': firstDay.toISOString().slice(0, 10)},
            'dimensions': [
                'portal_view', 'portal_search', 'accessed'
            ],
            'class': ["collection", "party", "service", "activity"]
        };

        var availableFilters = {};

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