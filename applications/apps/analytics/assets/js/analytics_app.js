(function () {
    'use strict';

    angular.module("analytic_app", ['chart.js', 'daterangepicker']);

    angular.module('analytic_app')
        .controller('mainCtrl', mainCtrl)
        .factory('analyticFactory', analyticFactory);

    function mainCtrl($log, analyticFactory) {
        var vm = this;
        vm.types = ['Line', 'Bar'];
        vm.chartType = vm.types[0];

        vm.filters = {
            'log': 'portal',
            'period': {startDate: '2015-06-01', endDate: '2015-06-02'},
            'group': {
                'type':'group', 'value':'State Records Authority of New South Wales'
            },
            'dimensions': [
                'portal_view', 'portal_search'
            ]
        };

        vm.onClick = function (points, evt) {
            $log.debug(points, evt);
        };

        vm.getData = function() {
            getData(vm.filters);
        }
        getData(vm.filters);

        function getData(filters) {
            vm.loading = true;
            return analyticFactory.summary(filters).then(function(data){
                vm.loading = false;
                vm.chartData = data;
            });
        }

    }

    function analyticFactory($http, $log) {
        return {
            summary: getSummaryData,
            testdata: getTestData
        };

        function getSummaryData(filters) {
            return $http.post(apps_url+'analytics/summary', {filters:filters})
                    .then(returnData)
                    .catch(handleError);
        }

        function getTestData() {
            return $http.get(apps_url+'analytics/summary')
                        .then(returnData)
                        .catch(handleError);
        }

        function returnData(response) {
            var result = {
                labels: [],
                series: ['View', 'Search'],
                data: [[],[]]
            };
            angular.forEach(response.data.result, function (obj, index) {
                result.labels.push(index);
                result.data[0].push(obj['portal_view']);
                if (obj['portal_search']) {
                    result.data[1].push(obj['portal_search'])
                } else {
                    result.data[1].push(0);
                }
            });
            return result;
        }

        function handleError(error) {
            $log.error(error);
        }
    }

})();