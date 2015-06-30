(function () {
    'use strict';

    angular.module("analytic_app", ['chart.js']);

    angular.module('analytic_app')
        .controller('mainCtrl', mainCtrl)
        .factory('analyticFactory', analyticFactory);

    function mainCtrl($log, analyticFactory) {
        var vm = this;
        vm.types = ['Line', 'Bar'];
        vm.chartType = vm.types[0];

        vm.onClick = function (points, evt) {
            $log.debug(points, evt);
        };

        getData();
        function getData() {
            return analyticFactory.testdata().then(function(data){
                vm.chartData = data;
            });
        }

    }

    function analyticFactory($http, $log) {
        return {
            testdata: getTestData
        };

        function getTestData() {
            return $http.get(apps_url+'analytics/summary2')
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